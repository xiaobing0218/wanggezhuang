<?php
include(VIEW_DIR . DS . 'function' . DS . 'mobile_layout.php');
include(VIEW_DIR . DS . 'function' . DS . 'reviser.php');

class general_controller extends Controller
{
    public function init()
    {
        $this->common = array
        (
            'baseurl' => $GLOBALS['cfg']['http_host'],
            'theme' => $GLOBALS['cfg']['http_host'] . '/public/theme/mobile/' . $GLOBALS['cfg']['enabled_theme'],
        );

        $appid = 'wx56ad8cf895df24dc';
        $timestamp = time();
        $nonceStr = $this->_randstr();
        $url = 'https://hxbg.cn' . $_SERVER["REQUEST_URI"];
//        if (!empty($_GET['id'])){
//            $condition = array('goods_id' => (int)request('id', 0));
//            $goods_model = new goods_model();
//            $goods = $goods_model->find($condition);
//            $img='https://hxbg.cn/upload/goods/prime/350x350/'.$goods['goods_image'];
//        }else{
//            $img='https://hxbg.cn/logo.png';
//        }
        $img='https://hxbg.cn/logo.png';
        $this->image=$img;
        $signature = $this->ticket($nonceStr, $timestamp, $url);

//        $timestamp = '1584523007';
//        $nonceStr = 'ThdWQ154xACCtQGI';
//        $url = 'https://hxbg.cn/m/goods/index.html?id=5973';
//        $signature = '7a7f9069a32a5fde043ecb8d7164c439eee30b0d';

        $this->appid = $appid;//appid
        $this->timestamp = $timestamp;//签名时间
        $this->nonceStr = $nonceStr;//随机字符串
        $this->url = $url;
        $this->signature = $signature;
        utilities::crontab();
    }

    function _randstr($length = 16)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
        }
        return $str;
    }

    /**
     * @return mixed
     */
    public function action_token()
    {

        $token = new token_model();
        $info = $token->one_select();
        $time = time();
        if ($info) {
            if (($info[0]['created_date'] + 7000) > $time) {
                return $info[0]['token'];
            } else {
                $appid = 'wx56ad8cf895df24dc';
                $secret = 'f185bd54bf971eec94d2b5343a9ef263';
                $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $secret;
                $data = file_get_contents($url);
                $data = json_decode($data, true);
                $token->add($data['access_token']);
                return $data['access_token'];
            }
        } else {
            $appid = 'wx56ad8cf895df24dc';
            $secret = 'f185bd54bf971eec94d2b5343a9ef263';
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=' . $appid . '&secret=' . $secret;
            $data = file_get_contents($url);
            $data = json_decode($data, true);
            if ($data['errcode'] != 0) {
                return '';
            }
            //$token->add($data['access_token']);
            return $data['access_token'];
        }
    }

    public function ticket($nonceStr, $timestamp, $url)
    {
        $token = $this->action_token();
        $jsapi = new jsapi_model();
        $info = $jsapi->one_select();
        if ($info) {
            $time = time();
            if (($info[0]['created_date'] + 7000) > $time) {
                $ticket= $info[0]['jsapi'];
            } else {
            $ticket = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=' . $token . '&type=jsapi';
            $ticket = file_get_contents($ticket);
            $data = json_decode($ticket, true);
            $jsapi->add($data['ticket']);
            $ticket = $data['ticket'];
            }
        } else {
            $ticket = 'https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=' . $token . '&type=jsapi';
            $ticket = file_get_contents($ticket);
            $data = json_decode($ticket, true);
            if ($data['errcode'] != 0) {
                return '';
            }
            $jsapi->add($data['ticket']);
            $ticket = $data['ticket'];
        }

        $string = 'jsapi_ticket=' . $ticket . '&noncestr=' . $nonceStr . '&timestamp=' . $timestamp . '&url=' . $url;
        $signature = sha1($string);
        return $signature;
    }

    protected function compiler($tpl)
    {
        $this->display('mobile' . DS . $GLOBALS['cfg']['enabled_theme'] . DS . $tpl);
    }

    protected function is_logined($jump = TRUE)
    {
        if (empty($_SESSION['USER']['USER_ID'])) {
            if ($cookie = request('USER_STAYED', null, 'cookie')) {
                $user_model = new user_model();
                if ($user_model->check_stayed($cookie, get_ip())) {
                    $_SESSION['REDIRECT'] = $_SERVER['REQUEST_URI'];
                    if ($jump) jump($_SERVER['REQUEST_URI']);
                }
            }
            if ($jump) jump(url('mobile/user', 'login'));
            return FALSE;
        }
        return $_SESSION['USER']['USER_ID'];
    }

    protected function prompt($type = null, $text = null, $redirect = null, $time = 3)
    {
        if (empty($type)) $type = 'default';
        if (empty($redirect)) $redirect = 'javascript:history.back()';
        $this->rs = array('type' => $type, 'text' => $text, 'redirect' => $redirect, 'time' => $time);
        $this->compiler('prompt.html');
        exit;
    }

}
