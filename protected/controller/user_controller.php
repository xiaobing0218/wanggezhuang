<?php
class user_controller extends general_controller
{
    public function action_index()
    {
        $user_id = $this->is_logined();
        $condition = array('user_id' => $user_id);
        $user_model = new user_model();
        $user = $user_model->find($condition);
        //用户资料
        $profile_model = new user_profile_model();
        $user['profile'] = $profile_model->find($condition);
        //账户信息
        $account_model = new user_account_model();
        $user['account'] = $account_model->get_user_account($user_id);
        $this->user = $user;
        //近期订单
        $order_model = new order_model();
        if($order_list = $order_model->find_all($condition, 'id DESC', 'order_id, order_amount, order_status, payment_method, created_date', 5))
        {
            foreach($order_list as $k => $v)
            {
                $progress = $order_model->get_user_order_progress($v['order_status'], $v['payment_method']);
                $order_list[$k]['progress'] = array_pop($progress);
            }
        }
        $this->order_list = $order_list;
        //近期收藏
        $favor_model = new user_favorite_model();
        $favorite_list = $favor_model->get_user_favorites($user_id, 5);
        if ($favorite_list){
            foreach ($favorite_list as $k=>$v){
                $favorite_list[$k]['merchant_id']=(new merchant_model())->find(array('user_id'=>$v['merchant_id']));
            }
        }
        $this->favorite_list=$favorite_list;
        //最近浏览
        $goods_model = new goods_model();
        $this->history = $goods_model->get_history();

        //订单数
        $this->order_count = $order_model->find_count(array('user_id' => $user_id));

        //收藏数
        $fav_model = new user_favorite_model();
        $this->fav_count = $fav_model->find_count(array('user_id' => $user_id));

        //评论数
        $review_model = new goods_review_model();
        $this->review_count = $review_model->find_count(array('user_id' => $user_id));


            $this->compiler('user_index.html');
    }

    public function action_profile()
    {
        $user_id = $this->is_logined();
        if(request('step') == 'update')
        {
            $data = array
            (
                'nickname' => trim(strip_tags(request('nickname', ''))),
                'gender' => (int)request('gender', 0),
                'birth_year' => (int)request('birth_year', 0),
                'birth_month' => (int)request('birth_month', 0),
                'birth_day' => (int)request('birth_day', 0),
                'qq' => trim(request('qq', '')),
                'signature' => trim(strip_tags(request('signature', ''))),
            );

            $profile_model = new user_profile_model();
            $verifier = $profile_model->verifier($data);
            if(TRUE === $verifier)
            {
                $profile_model->update(array('user_id' => $user_id), $data);
                $this->prompt('success', '更新资料成功', url('user', 'profile'));
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            include(VIEW_DIR.DS.'function'.DS.'html_date_options.php');
            $condition = array('user_id' => $user_id);
            $user_model = new user_model();
            $this->user = $user_model->find($condition);
            $profile_model = new user_profile_model();
            $this->profile = $profile_model->find($condition);
            $this->compiler('user_profile.html');
        }
    }

    public function action_login()
    {
        $client_ip = get_ip();
        if(request('step') == 'submit')
        {
            $error_model = new request_error_model();
            if($is_captcha = $error_model->check($client_ip, $GLOBALS['cfg']['captcha_user_login']))
            {
                $captcha = strtolower(trim(request('captcha', '', 'post')));
                if(empty($_SESSION['CAPTCHA']) || $_SESSION['CAPTCHA'] != $captcha)
                {
                    unset($_SESSION['CAPTCHA']);
                    $this->prompt('error', '验证码不正确', url('user', 'login'), 5);
                }
            }

            $username = trim(request('username', '', 'post'));
            $password = request('password', '', 'post');

            $user_model = new user_model();
            if($user = $user_model->find(array('username' => $username, 'password' => $password)))
            {
                if(request('stay')) $user_model->stay_login($user['user_id'], $user['password'], $client_ip);
                $user_model->set_logined_info($client_ip, $user['user_id'], $user['username'], $user['avatar']);
                $redirect = isset($_SESSION['REDIRECT']) ? $_SESSION['REDIRECT'] : url('user', 'index');
                unset($_SESSION['REDIRECT']);
                $this->prompt('success', '登录成功', $redirect, 3);
            }
            else if ($user = $user_model->find(array('mobile' => $username, 'password' => $password)))
            {
                if(request('stay')) $user_model->stay_login($user['user_id'], $user['password'], $client_ip);
                $user_model->set_logined_info($client_ip, $user['user_id'], $user['username'], $user['avatar']);
                $redirect = isset($_SESSION['REDIRECT']) ? $_SESSION['REDIRECT'] : url('user', 'index');
                unset($_SESSION['REDIRECT']);
                $this->prompt('success', '登录成功', $redirect, 3);

            }else{
                 $error_model->incr_err($client_ip);
                $this->prompt('error', '用户名或密码错误', url('user', 'login'), 5);
            }
        }
        else
        {
            if($cookie = request('USER_STAYED', null, 'cookie'))
            {
                $user_model = new user_model();
                if($user_model->check_stayed($cookie, $client_ip)) jump(url('user', 'index'));
            }
            $error_model = new request_error_model();
            $this->captcha = $error_model->check($client_ip, $GLOBALS['cfg']['captcha_user_login']);
            $oauth_model = new oauth_model();
            $this->oauth_list = $oauth_model->get_enable_list();
            $this->compiler('login.html');
        }
    }

    public function action_register()
    {
        if(request('step') == 'submit')
        {
            $data = array
            (
                'username' => trim(request('username', '', 'post')),
                'mobile' => trim(request('phone', '', 'post')),
                'password' => trim(request('password', '', 'post')),
                'repassword' => trim(request('repassword', '', 'post')),
                //'captcha' => strtolower(trim(request('captcha', '','post'))),
            );
            // if (empty($_COOKIE[$data['mobile']])) {
            //     $this->prompt('error', '验证码已失效，请重新获取');
            // }

            // if (vdecrypt($_COOKIE[$data['mobile']]) != $data['captcha']) {
            //     $this->prompt('error', '验证码不正确');
            // }

            $user_model = new user_model();
            $verifier = $user_model->verifier($data);
            if(TRUE === $verifier)
            {
                setcookie($data['mobile'], '', time() - 1, '/');
                $data['mobile_status'] = 1;
                if($user_model->register($data)) $this->prompt('success', '恭喜您，注册成功！请您务必牢记您的用户名和邮箱.', url('user', 'index'));
                $this->prompt('error', '注册失败！请稍后重新尝试.');
            }
            else
            {
                $this->prompt('error', $verifier);
            }
        }
        else
        {
            $help_model = new help_model();
            $this->help = $help_model->find(array('id' => 13));
            $this->compiler('register.html');
        }
    }

    public function action_logout()
    {
        $user_model = new user_model();
        $user_model->logout();
        jump(url('user', 'login'));
    }

    public function action_modify_pw()
    {
        if (request('step') == 'submit') {
            $data = array
            (
                'mobile' => trim(request('phone', '', 'post')),
                'password' => trim(request('password', '', 'post')),
                'repassword' => trim(request('repassword', '', 'post')),
                'captcha' => strtolower(trim(request('captcha', '', 'post'))),
            );
            if (empty($_COOKIE[$data['mobile']])) {
                $this->prompt('error', '验证码已失效，请重新获取');
            }

            if (vdecrypt($_COOKIE[$data['mobile']]) != $data['captcha']) {
                $this->prompt('error', '验证码不正确');
            }

            $user_model = new user_model();
            $verifier = $user_model->verifier($data, [
                'username' => FALSE,
                'mobile' => FALSE
            ]);
            if (TRUE === $verifier) {
                $info = $user_model->find(['mobile' => $data['mobile']]);
                $user_model->update(array('user_id' => $info['user_id']), array('password' => md5e($data['password'])));
                setcookie($data['mobile'], '', time() - 1, '/');
                $this->prompt('success', '密码更改成功，请用新密码重新登录', url('user', 'login'));
            } else {
                $this->prompt('error', $verifier);
            }
        } else {
            $this->prompt('error', '修改失败！请稍后重新尝试.');
        }
    }

    public function action_get_captcha()
    {
        header("Content-Type: application/json;charset=utf-8");
        if (empty($_POST)) {
            die(json_encode([
                'errcode' => 2,
                'errmsg' => '请求方式不正确'
            ], JSON_UNESCAPED_UNICODE));
        }
        $phone = request('phone');
        $type = request('type');
        if (!preg_match("/^1[3456789]{1}\d{9}$/", $phone)) {
            die(json_encode([
                'errcode' => -1,
                'errmsg' => '请输入正确的手机号码'
            ], JSON_UNESCAPED_UNICODE));
        }

        $user_model = new user_model();
        $is_exist = $user_model->addrule_mobile_exist($phone);
        if ($type == 1) {
            if (!$is_exist) {
                die(json_encode([
                    'errcode' => -1,
                    'errmsg' => '输入的手机号已经存在请重新输入'
                ], JSON_UNESCAPED_UNICODE));
            }
        } else {
            if ($is_exist) {
                die(json_encode([
                    'errcode' => -1,
                    'errmsg' => '输入的手机号不存在请重新输入'
                ], JSON_UNESCAPED_UNICODE));
            }
        }

        $code = rand(1000, 9999);
        setcookie($phone, vencrypt((string)$code), time() + 60 * 5, '/');
        $content = "您好，您的验证码为：{$code}，请妥善保管。";
        $res = sms::send($phone, $content);
        if ($res == 0) {
            die(json_encode([
                'errcode' => 0,
                'errmsg' => '验证码发送成功'
            ], JSON_UNESCAPED_UNICODE));
        } else {
            die(json_encode([
                'errcode' => -1,
                'errmsg' => '因网络原因验证码发送失败，请稍后再试'
            ], JSON_UNESCAPED_UNICODE));
        }
    }
}
