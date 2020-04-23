<?php
class pay_controller extends general_controller
{
    public function action_url()
    {
        $user_id = $this->is_logined();
        $number=request('order_id');
        $order_id = explode(',',$number);

        $order_model = new order_model();
        $order=array();
        foreach ($order_id as $k=>$v){
            $order[$k] = $order_model->find(array('user_id' => $user_id, 'order_id' => $v, 'order_status' => 1));
        }

        if($order)
        {
            $payment_id = (int)request('payment_id');
            $payment_map = vcache::instance()->payment_method_model('indexed_list');
            if(isset($payment_map[$payment_id]))
            {


                foreach ($order as $k=>$v){
                    $order_model->update(array('order_id' => $v['order_id']), array('payment_method' => $payment_id));
                    $order[$k]['payment_method'] = $payment_id;
                }

                $plugin = plugin::instance('payment', $payment_map[$payment_id]['pcode'], array($payment_map[$payment_id]['params']));
                $plugin->device = request('device', 'pc');
                if ($payment_map[$payment_id]['id'] == 4) {
                    $res = array('status' => 'success', 'url' => '/m/pay/pay_wx.html?order_id=' . $number);
                } else {
                    $res = array('status' => 'success', 'url' => $plugin->create_pay_url($number));
                }
            }
            else
            {
                $res = array('status' => 'error', 'msg' => '支付方式不存在');
            }
        }
        else
        {
            $res = array('status' => 'error', 'msg' => '订单不存在');
        }
        echo json_encode($res);

    }

    public function action_notify()
    {
        $pcode = request('pcode', '', 'get');
        $res = 'fail';
        $payment_model = new payment_method_model();
        if($payment = $payment_model->find(array('pcode' => $pcode, 'enable' => 1), null, 'params'))
        {
            $plugin = plugin::instance('payment', $pcode, array($payment['params']));
            if($plugin->response($_POST)) $res = 'success';
        }
        echo $res;
    }


    public function action_pay_success()
    {
        $user_id = $this->is_logined();
        $order_model = new order_model();
        $order_id = request('order_id');
        $order_id= explode(',',$order_id) ;
        foreach ($order_id as $k=>$v){
            $order = $order_model->find(array('order_id' => $v, 'user_id' => $user_id));
            if ($order) {
                $payment_id = $order['payment_method'];
                $payment_map = vcache::instance()->payment_method_model('indexed_list');
                $plugin = plugin::instance('payment', $payment_map[$payment_id]['pcode'], array($payment_map[$payment_id]['params']));

                $wxorder = $_SESSION['wxorder'];
                $order_info = $plugin->orderquery($wxorder);

                if ($order_info['code'] == 0) {
                    $order=explode(',',$order_info['data'][0]);
                    foreach ($order as $k=>$v){
                        $data = array
                        (
                            'order_status' => 2,
                            'payment_date' => time(),
                        );
                        $order_model->update(array('order_id' =>$v), $data);
                        $this->action_information($v);
                    }
                }else{
                    $res = array('status' => 'error', 'msg' => '订单支付失败');
                    die(json_encode($res,JSON_UNESCAPED_UNICODE));
                }
            } else {
                $res = array('status' => 'error', 'msg' => '订单不存在');
                die(json_encode($res,JSON_UNESCAPED_UNICODE));
            }
        }

        $res = array('status' => 'success', 'msg' => '订单支付成功');
        die(json_encode($res,JSON_UNESCAPED_UNICODE));
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
            if (($info[0]['created_date']+100)>$time){
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
    public function action_information($order_id){
        $order = (new order_model())->find(array('order_id' =>$order_id));
        $merchant = (new merchant_model())->find(array('user_id' => $order['merchant_id']));
        if ($merchant) {
            if ($merchant['open_id']) {
                $user = (new user_model())->find(array('user_id' => $order['user_id']));
                $name = (new user_profile_model())->find(array('user_id' => $order['user_id']));
                if ($name['nickname']) {
                    $name = $name['nickname'];
                } else {
                    $name = $user['username'];
                }
                $goods = (new order_goods_model())->find(array('order_id'=>$order_id));
                $token=$this->action_token();
                $template_id='3ZssdpSxY0FbdcnFGdbcXxVmt9-FQP6VSjDZ_1HPU-k';
                $url='https://api.weixin.qq.com/cgi-bin/message/template/send?access_token='.$token;
                $data=[
                    'touser'=>$merchant['open_id'],
                    'template_id'=>$template_id,
                    'url'=>'',
                    'data'=>[
                        'first'=>[
                            'value'=>'店铺新订单成交通知',
                        ],
                        'keyword1'=>[
                            'value'=>$order['goods_amount'],   //金额
                        ],
                        'keyword2'=>[
                            'value'=>$goods['goods_name'],   //商品名称
                        ],
                        'keyword3'=>[
                            'value'=>$order['order_id'],   //订单号
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
        }
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
