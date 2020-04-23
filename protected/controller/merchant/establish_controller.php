<?php
/*
Author: Administrator
CreeateTame:2020/3/16 9:11
*/

class establish_controller extends general_controller
{
    /**
     * 绑定微信
     */
    public function action_index()
    {
        $admin_info = $this->get_admin_info();
        $one = $this->action_token();
        $data = $this->request_post($one, $_SESSION['USERADMIN']['USER_ID']);
        $data = json_decode($data, true);
        $this->info=$admin_info;
        $this->ticket = 'https://mp.weixin.qq.com/cgi-bin/showqrcode?ticket='.$data['ticket'];
        $this->compiler('message/establish.html');
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
            if($data['errcode'] != 0){
                echo '微信AccessToken获取失败，请检查配置项和微信公众号IP白名单';
                die;
            }

            $token->add($data['access_token']);
            return $data['access_token'];
        }

    }

    /**
     * @param $token
     * @param $user_id
     * @return bool|string
     */
    public function request_post($token, $user_id)
    {
        $postUrl = 'https://api.weixin.qq.com/cgi-bin/qrcode/create?access_token=' . $token;
        $post_data = [
            "action_name" => "QR_LIMIT_STR_SCENE",
            "action_info" => [
                "scene" => [
                    "scene_str" => $user_id
                ]
            ]
        ];
        $post_data = json_encode($post_data);
        $res = $this->_fetch($postUrl, $post_data);
        return $res;
    }
    /**
     * 发送网络请求
     * @param $url
     * @param array $data
     * @return bool|string
     */
    function _fetch($url, $data = [])
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
