<?php
class pay_controller extends general_controller
{
    public function action_index()
    {
        $user_id = $this->is_logined();
        $order_model = new order_model();
        $user_model = new user_model();
        $conditions=explode(",",request('order_id'));
        $order=array();
        foreach ($conditions as $k=>$v){
            $order[$k] = $order_model->find(array('order_id' => $v, 'user_id' => $user_id));
        }
        if($order)
        {
            foreach ($order as $k=>$v){
                $this->payment_method=$v['payment_method'];
                if($v['order_status'] != 1){
                    jump(url('mobile/order', 'list'));
                }
            }
            $sum = 0;
            foreach($order as $item){
                $sum +=$item['order_amount'];
            }
            $id=implode(',',$conditions);
            $this->order=$order;
            $this->order_amount=$sum;
            $this->order_id=$id;
            $this->user_info = $user_model->find(['user_id'=>$user_id]);
            $this->payment_list = vcache::instance()->payment_method_model('indexed_list');
            $this->compiler('pay.html');
        }
        else
        {
            jump(url('mobile/main', '400'));
        }
    }

    public function action_pay_wx()
    {
        $user_id = $this->is_logined();
        $order_model = new order_model();
        $cid=request('order_id');
        $order_id= explode(',',$cid) ;
        $order=array();
        foreach ($order_id as $k=>$v){
            $order[$k] = $order_model->find(array('order_id' => $v, 'user_id' => $user_id));
        }
        if($order)
        {

            foreach ($order as $k=>$v){
                if($v['order_status'] != 1){
                    jump(url('mobile/order', 'list'));
                }
                $payment_id = $v['payment_method'];
            }
            $sum = 0;
            foreach($order as $item){
                $sum += $item['order_amount'];
            }
            $payment_map = vcache::instance()->payment_method_model('indexed_list');
            $plugin = plugin::instance('payment', $payment_map[$payment_id]['pcode'].'_jsapi', array($payment_map[$payment_id]['params']));

            if (empty($_COOKIE['open_id'])) {
                //获取唯一标识
                $open_id = $plugin->GetOpenid();
                setcookie("open_id", $open_id, time() + 99 * 365 * 24 * 3600, '/');
            }else{
                $open_id = $_COOKIE['open_id'];
            }
            $wxorder=rand(10000,99999).rand(10000,99999).rand(10000,99999);
            $_SESSION['wxorder']=$wxorder;
            $outTradeNo = $wxorder;     //你自己的商品订单号
            $payAmount = $sum;          //付款金额，单位:元
            $orderName = '微信支付';    //订单标题
            $notifyUrl = "https://{$_SERVER['SERVER_NAME']}/pay/notify.html";     //付款成功后的回调地址(不要有问号)
            $payTime = time();      //付款时间

            $jsApiParameters = $plugin->createJsBizPackage($open_id, $payAmount, $outTradeNo, $orderName, $notifyUrl, $payTime,$cid);

            //$jsApiParameters = json_encode($jsApiParameters);
            $order_id=implode(',',$order_id);
            $this->jsapi = $jsApiParameters;
            $this->order_id=$order_id;
            $this->order = $order;
            $this->compiler('pay_wx.html');
        }
        else
        {
            jump(url('mobile/main', '400'));
        }
    }

    public function action_return()
    {
        $pcode = request('pcode', '', 'get');
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
            $sum=0;
            foreach ($plugin->order as $item){
                $sum+=$item['order_amount'];
            }
            $this->message = $plugin->message;
            $this->order = $plugin->order;
            $this->order_amount = $sum;
            $this->compiler('pay_return.html');
        }
        else
        {
            jump(url('mobile/main', '400'));
        }
    }
}
