<?php
class main_controller extends general_controller
{
    public function action_index()
    {

        var_dump('expression');exit();
//        setcookie("open_id", '', time() - 1, '/');
//        setcookie("open_id", '', time() - 1, '/m');
//        if (empty($_COOKIE['open_id'])) {
//            $payment_map = vcache::instance()->payment_method_model('indexed_list');
//            $payment = $payment_map[4];
//            $pay_plugin = plugin::instance('payment', $payment['pcode'] . '_jsapi', array($payment['params']));
//
//            $open_id = $pay_plugin->GetOpenid();
//            setcookie("open_id", $open_id, time() + 99 * 365 * 24 * 3600, '/');
//        }
        $this->hot_searches = !empty($GLOBALS['cfg']['goods_hot_searches']) ? explode(',', $GLOBALS['cfg']['goods_hot_searches']) : null;
        
        $vcache = vcache::instance();
        
        $newarrival = $vcache->goods_model('find_goods', array(array('newarrival' => 1), 16), $GLOBALS['cfg']['data_cache_lifetime']);
        if ($newarrival){
            foreach ($newarrival as $k => $v) {
                if ($v['merchant_id'] == '0') {
                    $newarrival[$k]['merchant_id']=array('user_id'=>'0','company_name'=>'海翔旗舰店');
                } else {
                    $newarrival[$k]['merchant_id']=(new merchant_model())->find(array('user_id'=>$v['merchant_id']));
                }
            }
        }

        $this->newarrival=$newarrival;

        $recommend = $vcache->goods_model('find_goods', array(array('recommend' => 1), 16), $GLOBALS['cfg']['data_cache_lifetime']);
        if ($recommend){
            foreach ($recommend as $k => $v) {
                if ($v['merchant_id'] == '0') {
                    $recommend[$k]['merchant_id']=array('user_id'=>'0','company_name'=>'海翔旗舰店');
                } else {
                    $recommend[$k]['merchant_id']=(new merchant_model())->find(array('user_id'=>$v['merchant_id']));
                }
            }
        }

        $this->recommend=$recommend;

        $bargain = $vcache->goods_model('find_goods', array(array('bargain' => 1), 16), $GLOBALS['cfg']['data_cache_lifetime']);
        if ($bargain){
            foreach ($bargain as $k => $v) {
                if ($v['merchant_id'] == '0') {
                    $bargain[$k]['merchant_id']=array('user_id'=>'0','company_name'=>'海翔旗舰店');
                } else {
                    $bargain[$k]['merchant_id']=(new merchant_model())->find(array('user_id'=>$v['merchant_id']));
                }
            }
        }

        $this->bargain=$bargain;

        $ad = (new adv_model())->find_all(['position_id' => 2,'type'=>'image'], 'seq ASC');
        if($ad){
            foreach ($ad as $key => $item) {
                $info = json_decode($item['params'], true);
                $ad[$key] = [
                    'src' => $info['src'],
                    'link' => $info['link']
                ];
            }
        }

        $this->ad = $ad;

        $this->compiler('index.html');
    }
    
    public function action_400()
    {
        $this->status = 400;
        $this->title = '错误请求';
        $this->content = '您的客户端发送了一个错误或非法的请求';
        $this->compiler('error.html');
        exit;
    }
    
    public function action_404()
    {
        $this->status = 404;
        $this->title = '页面未找到';
        $this->content = '很抱歉, 你要访问的页面或资源不存在';
        $this->compiler('error.html');
        exit;
    }
}
