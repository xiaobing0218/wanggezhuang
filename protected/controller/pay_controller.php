<?php
class pay_controller extends general_controller
{
    public function action_index()
    {
        $user_id = $this->is_logined();
        $order_model = new order_model();
        $conditions=explode(",",request('order_id'));
        $order=array();
        if ($conditions){
            foreach ($conditions as $k=>$v){

                $order[$k] = $order_model->find(array('order_id' => $v, 'user_id' => $user_id));
            }
        }
        if($order)
        {
            $cid=array();
            foreach ($order as $k=>$v){
                $cid[$k]=$v['order_id'];
            }
            $id=implode(',',$cid);
            $sum = 0;
            foreach($order as $item){
                $sum +=$item['order_amount'];
            }
            foreach ($order as $k=>$v){
                $payment_map = vcache::instance()->payment_method_model('indexed_list');

                if($change_payment = (int)request('change_payment', 0))
                {
                    if($change_payment == 2)
                    {
                        $order_shipping_model = new order_shipping_model();
                        if($order_shipping_model->find(array('order_id' => $v['order_id']))) $this->prompt('error', '您的订单已发货，无法更改为其他付款方式');
                    }

                    $payment_model = new payment_method_model();
                    if($change_payment != $v['payment_method'] &&
                        isset($payment_map[$change_payment]) && $change_payment != $v['payment_method'])
                    {

                        $order_model->update(array('order_id' => $v['order_id']), array('payment_method' => $change_payment));
                        $v['payment_method'] = $change_payment;
                    }
                }
                if($v['order_status'] == 1)
                {
                    if(!isset($payment_map[$v['payment_method']])) $this->prompt('error', '付款方式不存在');

                    $payment = $payment_map[$v['payment_method']];

                    $pay_plugin = plugin::instance('payment', $payment['pcode'], array($payment['params']));

                    if ($payment['id'] == 4) {
                        $this->payment = array('name' => $payment['name'], 'url' => '/pay/wxpay.html?order_id=' . $id);
                        $this->payment_list = $payment_map;
                        $this->payment_method=$v['payment_method'];
                    }else{
                        $this->payment = array('name' => $payment['name'], 'url' => $pay_plugin->create_pay_url($id));
                        $this->payment_list = $payment_map;
                        $this->payment_method=$v['payment_method'];
                    }
                } else {
                    $this->prompt('error', '您无法进行此操作');
                }
            }

            $this->id=$id;
            $this->order=$order;
            $this->order_amount=$sum;

            $this->compiler('pay.html');

        } else {
            jump(url('main', '404'));
        }
    }

    public function action_pay_status_check()
    {
        $user_id = $this->is_logined();
        $order_model = new order_model();
        if ($order = $order_model->find(array('order_id' => bigintstr(request('order_id')), 'user_id' => $user_id))) {
            if ($order['order_status'] == 2) {
                die(json_encode([
                    'errcode' => 0
                ]));
            } else {
                die(json_encode([
                    'errcode' => -1
                ]));
            }
        } else {
            die(json_encode([
                'errcode' => -1
            ]));
        }
    }

    public function action_wxpay()
    {
        $user_id = $this->is_logined();
        $order_model = new order_model();
        $order_id= explode(',',request('order_id')) ;
        $order=array();
        if ($order_id){
            foreach ($order_id as $k=>$v){
                $order[$k] = $order_model->find(array('order_id' => $v, 'user_id' => $user_id));
            }
        }

        if ($order) {
            $payment_map = vcache::instance()->payment_method_model('indexed_list');

            $cid=array();
            foreach ($order as $k=>$v){
                $cid[$k]=$v['order_id'];
                if ($v['order_status'] == 1) {
                    if (!isset($payment_map[$v['payment_method']])) $this->prompt('error', '付款方式不存在');
                    $payment = $payment_map[$v['payment_method']];
                    $pay_plugin = plugin::instance('payment', $payment['pcode'], array($payment['params']));
                } else {
                    $this->prompt('error', '您无法进行此操作');
                }
            }
            $id=implode(',',$cid);
            $sum = 0;
            foreach($order as $item){
                $sum += $item['order_amount'];
            }
            $wxorder=rand(10000,99999).rand(10000,99999).rand(10000,99999);
            $_SESSION['wxorder']=$wxorder;
            if ($payment['id'] == 4) {
                $body = "微信支付";//商品描述
                $out_trade_no = $wxorder;//订单
                $total_fee = $sum;//价格
                $notifyUrl = "https://{$_SERVER['SERVER_NAME']}/pay/notify.html";
                //var_dump($notifyUrl);
                $payTime = time();//付款时间
                $arr = $pay_plugin->createJsBizPackage($total_fee, $out_trade_no, $body, $notifyUrl, $payTime,$id);
                $url = '/qrcode/index.html?url=' . $arr['code_url'];
                $this->url = urldecode($url);
                $this->order = $order;
                $this->order_amount = $sum;
                $this->id=$id;
                $this->order_id=$cid[0];
            } else {
                jump(url('main', '404'));
            }
            $this->compiler('pay_wx.html');

        } else {
            jump(url('main', '404'));
        }
    }

    public function action_return()
    {
        $pcode = sql_escape(request('pcode', ''));
        $payment_model = new payment_method_model();
        if($payment = $payment_model->find(array('pcode' => $pcode, 'enable' => 1), null, 'params'))
        {
            $plugin = plugin::instance('payment', $pcode, array($payment['params']));

            if($plugin->response($_GET))
            {
                $this->status = 'success';
            }
            else
            {
                $this->status = 'error';
            }

            $this->message = $plugin->message;
            $sum=0;
            foreach ($plugin->order as $item){
                    $sum+=$item['order_amount'];
            }
            $this->order = $plugin->order;

            $this->order_amount = $sum;
            $this->compiler('pay_return.html');
        }
        else
        {
            jump(url('main', '404'));
        }
    }

    public function action_notify(){
        $order_model = new order_model();
        $payment_map = vcache::instance()->payment_method_model('indexed_list');
        if (!isset($payment_map['4'])) $this->prompt('error', '付款方式不存在');
        $payment = $payment_map['4'];
        $pay_plugin = plugin::instance('payment', $payment['pcode'], array($payment['params']));
        $res = $pay_plugin->notify();
//        $log = APP_DIR . '/protected/data/log/data.txt';
//        file_put_contents($log, json_encode($res));
        if($res){
            if($res['code']){

                if(!empty($res['order'])){
                    $order=explode(',',$res['order']);
                    foreach ($order as $k=>$v){
                        if($order = $order_model->find(array('order_id' => $v))){
                            if($order['order_status'] == 1){
                                $data = array
                                (
                                    'order_status' => 2,
                                    'payment_date' => time(),
                                );
                                $order_model->update(array('order_id' => $v), $data);
                                $order = $order_model->find(array('order_id' => $v));
                                $merchant=(new merchant_model())->find(array('user_id'=>$order['merchant_id']));
                                if ($merchant){
                                    if ($merchant['open_id']){
                                        $name=(new user_profile_model())->find(array('user_id'=>$order['user_id']));
                                        if ($name['nickname']){
                                            $name=$name['nickname'];
                                        }else{
                                            $name='未设置';
                                        }
                                        $goods=(new order_goods_model())->find(array('order_id'=>$v));
                                        $this->information($merchant['open_id'],$order['goods_amount'],$order['order_id'],$name,$goods['goods_name']);
                                        //TODO 微信模板消息
                                    }
                                }
                            }
                        }
                    }
                    $log = APP_DIR . '/protected/data/log/data_' . $order['order_id'] . '_' . time() . '.txt';
                    file_put_contents($log, json_encode($res));

                }
                header('Content-type:text/html; Charset=utf-8');
                echo '<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
            }
        }
    }
    public function action_orderquery(){
        $order_model = new order_model();
        $order=bigintstr(request('order_id'));
        $payment_map = vcache::instance()->payment_method_model('indexed_list');
        if (!isset($payment_map['4'])) $this->prompt('error', '付款方式不存在');
        $payment = $payment_map['4'];
        $pay_plugin = plugin::instance('payment', $payment['pcode'], array($payment['params']));
        $res = $pay_plugin->orderquery($_SESSION['wxorder']);
        if ($res['code']=='0'){
                $order=explode(',',$res['data'][0]);
                foreach ($order as $k=>$v){
                    $data = array
                    (
                        'order_status' => 2,
                        'payment_date' => time(),
                    );
                    $order_model->update(array('order_id' =>$v), $data);
                    $order = $order_model->find(array('order_id' => $v));
                    $merchant=(new merchant_model())->find(array('user_id'=>$order['merchant_id']));
                    if ($merchant){
                        if ($merchant['open_id']){
                            $name=(new user_profile_model())->find(array('user_id'=>$order['user_id']));
                            if ($name['nickname']){
                                $name=$name['nickname'];
                            }else{
                                $name='未设置';
                            }
                            $goods=(new order_goods_model())->find(array('order_id'=>$v));
                            $this->information($merchant['open_id'],$order['goods_amount'],$order['order_id'],$name,$goods['goods_name']);
                            //TODO 微信模板消息
                        }
                    }
                }
            die(json_encode([
                'errcode' => 0
            ]));
        }

    }
    /**
     * @return mixed
     * token
     */
    public function action_token(){
        $token=new token_model();
        $info=$token->one_select();
        $time=time();
        if ($info){
            if (($info[0]['created_date']+7000)>$time){
                return $info[0]['token'];
            }else{
                $appid='wx56ad8cf895df24dc';
                $secret='f185bd54bf971eec94d2b5343a9ef263';
                $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;
                $data=file_get_contents($url);
                $data = json_decode($data, true);
                $token->add($data['access_token']);
                return $data['access_token'];
            }
        }else{
            $appid='wx56ad8cf895df24dc';
            $secret='f185bd54bf971eec94d2b5343a9ef263';
            $url='https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret;
            $data=file_get_contents($url);
            $data = json_decode($data, true);
            $token->add($data['access_token']);
            return $data['access_token'];
        }
    }
    public function information($open_id,$goods_amount,$order_id,$name,$goods){
        $token=$this->action_token();
        $template_id='3ZssdpSxY0FbdcnFGdbcXxVmt9-FQP6VSjDZ_1HPU-k';
        $url='https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$token;
        $data=[
            'touser'=>$open_id,
            'template_id'=>$template_id,
            'url'=>'',
            'data'=>[
                'first'=>[
                    'value'=>'店铺新订单成交通知',
                ],
                'keyword1'=>[
                    'value'=>$goods_amount.'元',   //金额
                ],
                'keyword2'=>[
                    'value'=>$goods,   //商品名称
                ],
                'keyword3'=>[
                    'value'=>$order_id,   //订单号
                ],
                'keyword4'=>[
                    'value'=>$name,   //买家会员
                ],
                'remark'=>[
                    'value'=>'该会员已经支付订单，请尽快安排进行发货.',
                ],
            ]
        ];
        $data=json_encode($data,JSON_UNESCAPED_UNICODE);
        $this->_fetch($url,$data);

    }
    public function _fetch($url, $data = [])
    {
        $ch = curl_init();
        $opts[CURLOPT_URL] = $url;
        $opts[CURLOPT_RETURNTRANSFER] = true;
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        if ($data and is_array($data)) {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = http_build_query($data);
        }
        if ($data and is_string($data)) {
            $opts[CURLOPT_POST] = true;
            $opts[CURLOPT_POSTFIELDS] = $data;
            $opts[CURLOPT_HTTPHEADER] = [
                'Content-type: application/json',
                'Content-length: ' . strlen($data)
            ];
        }
        curl_setopt_array($ch, $opts);
        $res = curl_exec($ch);
        curl_close($ch);
        return $res;
    }

}
