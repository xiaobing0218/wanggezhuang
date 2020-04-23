<?php


class wechat
{
    protected $AppID = 'wx56ad8cf895df24dc';
    protected $AppSecret = 'f185bd54bf971eec94d2b5343a9ef263';
    protected $token = 'rubychan';

    public function valid()
    {
        if (empty($_GET["echostr"])) {
            echo "Request failed";
            exit();
        }
        $echoStr = $_GET["echostr"];
        if ($this->checkSignature()) {
            echo $echoStr;
            exit;
        }
    }

    /**
     * @throws \think\exception\DbException
     */
    public function responseMsg()
    {
        //get post data, May be due to the different environments
        $postStr = file_get_contents('php://input');
        //extract post data
        if (!empty($postStr)) {
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $fromUsername = $postObj->FromUserName;
            $toUsername = $postObj->ToUserName;
            $keyword = trim($postObj->Content);
            $time = time();
            $textTpl = "<xml>
						<ToUserName><![CDATA[%s]]></ToUserName>
						<FromUserName><![CDATA[%s]]></FromUserName>
						<CreateTime>%s</CreateTime>
						<MsgType><![CDATA[%s]]></MsgType>
						<Content><![CDATA[%s]]></Content>
						<FuncFlag>0</FuncFlag>
						</xml>";


            //事件推送------------------------------------------------------------------------------------------------
            $ev = $postObj->Event;
            if ($ev == "subscribe") {//关注
                $qrscene = $postObj->EventKey;
                $open_id = $fromUsername;
                $value = substr($qrscene, 8, strlen($qrscene));
                $content_state = $this->set_open_id($value, $open_id);
                $msgType = "text";
                $contentStr = '感谢您关注公众号。';
                if ($value) {
                    $contentStr .= '
 '. $content_state;
                }
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;

            } else if ($ev == "unsubscribe") {//取消关注

            } else if ($ev == "SCAN") {//扫码事件
                $qrscene = $postObj->EventKey;
                $open_id = $fromUsername;
                $contentStr = $this->set_open_id($qrscene, $open_id);
                $msgType = "text";
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            } else if ($ev == "CLICK") {//菜单事件
                $EventKey = $postObj->EventKey;
                if ($EventKey == 'contact') {
                    $msgType = "text";
                    $contentStr = '联系电话：0532-66085004
QQ客服：2027599709';
                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                    echo $resultStr;
                }
            }

            //关键字--------------------------------------------------------------------------------------------------
            if (!empty($keyword)) {
                if ($keyword == '@绑定') {
                    $msgType = "text";

//                    $user_info = $this->get_user_info($fromUsername);
//                    $WechatModel = new WechatModel();
//                    $unionid_info = $WechatModel->where([
//                        'unionid' => $user_info['unionid']
//                    ])->find();
//
//                    if (empty($unionid_info)) {
//                        $insert_data = [
//                            'unionid' => $user_info['unionid'],
//                            'open_id' => $user_info['openid'],
//                            'created_at' => time()
//                        ];
//                        $WechatModel->save($insert_data);
//                        $contentStr = "绑定成功，可以收到推送信息。";
//
//                    } else {
//                        $contentStr = "您已经绑定过，请勿重复绑定。";
//                    }
//                    $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
//                    echo $resultStr;
                }
            } else if ($keyword == '@获取') {
                $msgType = "text";
                $contentStr = $fromUsername;
                $resultStr = sprintf($textTpl, $fromUsername, $toUsername, $time, $msgType, $contentStr);
                echo $resultStr;
            } else {
                echo "Input something...";
            }
        } else {
            echo "";
            exit;
        }
    }

    public function send($template_id, $open_id, $data, $path = '')
    {
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token={$access_token}";
        $array = [
            'touser' => $open_id,
            'template_id' => $template_id,
            'url' => $path,
            'data' => $data
        ];

        $res = _fetch($url, json_encode($array));
        return $res;
    }

    public function get_user_info($open_id)
    {
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/user/info?access_token={$access_token}&openid={$open_id}&lang=zh_CN";
        $res = json_decode(file_get_contents($url), true);
        return $res;
    }

    protected function get_access_token()
    {
        $access_token = cache('wechat_access_token');
        if ($access_token && $access_token['expires_in'] > time()) {
            return $access_token['access_token'];
        } else {
            $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid={$this->AppID}&secret={$this->AppSecret}";
            $res = json_decode(file_get_contents($url), true);
            if (empty($res['access_token'])) {
                return '';
            }
            $access_token = $res['access_token'];
            $expires_in = time() + 7200;
            cache('wechat_access_token', [
                'access_token' => $access_token,
                'expires_in' => $expires_in
            ]);
            return $access_token;
        }
    }

    protected function get_ticket()
    {
        $ticket = cache('wechat_ticket');
        if ($ticket && $ticket['expires_in'] > time()) {
            return $ticket['ticket'];
        } else {
            $access_token = $this->get_access_token();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token={$access_token}&type=jsapi";
            $res = json_decode(file_get_contents($url), true);
            if (empty($res['ticket'])) {
                return '';
            }
            $ticket = $res['ticket'];
            $expires_in = time() + 7200;
            cache('wechat_ticket', [
                'ticket' => $ticket,
                'expires_in' => $expires_in
            ]);
            return $ticket;
        }
    }

    public function get_signature($noncestr, $timestamp, $url)
    {
        $ticket = $this->get_ticket();
        $key = "jsapi_ticket={$ticket}&noncestr={$noncestr}&timestamp={$timestamp}&url={$url}";
        return sha1($key);
    }


    private function checkSignature()
    {
        if (empty($_GET["signature"]) || empty($_GET["timestamp"]) || empty($_GET["nonce"])) {
            echo "Request failed";
            exit();
        }

        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = $this->token;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode($tmpArr);
        $tmpStr = sha1($tmpStr);

        if ($tmpStr == $signature) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param array $menu
     * @return bool|string
     */
    public function set_menu(array $menu)
    {
        $access_token = $this->get_access_token();
        $url = "https://api.weixin.qq.com/cgi-bin/menu/create?access_token={$access_token}";

        $menu = json_encode($menu, JSON_UNESCAPED_UNICODE);
        $res = _fetch($url, $menu);
        return $res;
    }

    public function get_appid()
    {
        return $this->AppID;
    }

    public function set_open_id($user_id, $openid)
    {
        if ($user_id) {
            $merchant = (new merchant_model())->find(array('user_id' => $user_id));
            if ($merchant) {
                if ($merchant['open_id']) {
                    if ($merchant['open_id'] == $openid) {
                        return '商户新订单提醒微信已经绑定，请勿重复操作，如需解绑请联系管理员。';
                    }
                    return '商户新订单提醒微信已经绑定其他微信，如需更换请联系管理员。';
                }
                (new merchant_model())->update(array('user_id' => $user_id), array('open_id' => $openid));
                return '商户新订单提醒微信绑定成功。';
            }
        }
    }



}
