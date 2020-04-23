<?php

class merchant_model extends Model
{
    public $table_name = 'merchant';

    public $rules = array
    (
        'repassword' => array
        (
            'equal_to' => array('password', '两次密码不一致'),
        ),
        'full_name' => array
        (
            'is_required' => array(TRUE, '姓名称呼不能为空'),
            'max_length' => array(60, '姓名称呼不能超过60个字符'),
        ),
    );

    public $addrules = array
    (
        'username' => array
        (
            'addrule_username_format' => '用户名不符合格式要求',
            'addrule_username_exist' => '用户名已存在',
        ),

        'password' => array
        (
            'addrule_password_format' => '密码不符合格式要求',
        ),
    );

    //自定义验证器：检查用户名格式(可包含字母、数字或下划线，须以字母开头，长度为5-16个字符)
    public function addrule_username_format($val)
    {
        return preg_match('/^[a-zA-Z][_a-zA-Z0-9]{4,15}$/', $val) != 0;
    }

    //自定义验证器：检查用户名是否存在
    public function addrule_username_exist($val)
    {
        if ($this->find(array('username' => $val))) return FALSE;
        return TRUE;
    }

    //自定义验证器：检查密码格式(可包含字母、数字或特殊符号，长度为6-32个字符)
    public function addrule_password_format($val)
    {
        return preg_match('/^[\\~!@#$%^&*()-_=+|{}\[\],.?\/:;\'\"\d\w]{5,31}$/', $val) != 0;
    }

    /**
     * 验证保持登陆
     */
    public function check_stayed($cookie, $client_ip)
    {
        if (!empty($cookie)) {
            if ($cookie = vdecrypt($cookie, 604800)) {
                if ($admin = $this->find(array('user_id' => (int)substr($cookie, 32)))) {
                    if (md5($client_ip . $admin['hash']) == substr($cookie, 0, 32)) {
                        $this->set_logined_info($admin, $client_ip);
                        return TRUE;
                    }
                }
            }
        }
        return FALSE;
    }

    /**
     * 设置登录后信息
     */
    public function set_logined_info($admin, $client_ip)
    {
        $active_model = new admin_active_model();
        $active_model->update_active();
        if ($GLOBALS['cfg']['admin_mult_ip_login'] == 0) {
            if ($active = $active_model->find(array('user_id' => $admin['user_id']))) {
                if ($active['sess_id'] == session_id()) {
                    $active_model->delete(array('sess_id' => $active['sess_id']));
                } elseif ($active['ip'] == $client_ip) {
                    $active_model->delete(array('sess_id' => $active['sess_id']));
                } else {
                    return FALSE;
                }
            }
        }

        $_SESSION['USERADMIN'] = array
        (
            'USER_ID' => $admin['user_id'],
            'USERNAME' => $admin['username'],
            'LAST_IP' => $admin['last_ip'],
            'LAST_DATE' => $admin['last_date']
        );

        $this->update(array('user_id' => $admin['user_id']), array('last_ip' => $client_ip, 'last_date' => $_SERVER['REQUEST_TIME']));
        $active_model->adduser_active();
        return TRUE;
    }

    /**
     * 管理员列表(以主键作为数据列表索引)
     */
    public function indexed_list()
    {
        $find_all = $this->find_all(null, null, 'user_id, username');
        return array_column($find_all, null, 'user_id');
    }

    public function action_set_menu($data){
        $token=$this->action_token();
        $url='https://api.weixin.qq.com/cgi-bin/menu/create?access_token='.$token;
        $data=json_encode($data, JSON_UNESCAPED_UNICODE);
       return $this->_fetch($url,$data);
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
            $token->add($data['access_token']);
            return $data['access_token'];
        }

    }
}