<?php

class main_controller extends general_controller
{
    public function action_index()
    {
        $client_ip = get_ip();
        if ($cookie = request('ADMIN_STAYED', null, 'cookie')) {
            $merchant_model = new merchant_model();
            if ($merchant_model->check_stayed($cookie, $client_ip)) jump(url($this->MOD . '/main', 'panel'));
        }
        $error_model = new request_error_model();
        $lockout = $error_model->lockout($client_ip);
        if ($lockout == 0) {
            $_SESSION['LOGIN_TOKEN'] = array('KEY' => random_chars(5), 'VAL' => random_chars(9, TRUE));
            $this->captcha = $error_model->check($client_ip, $GLOBALS['cfg']['captcha_admin_login']);
        } else {
            $this->lockout = $lockout;
        }
        $this->compiler('index.html');
    }

    public function action_login()
    {
        $back = url($this->MOD . '/main', 'index');
        if (empty($_SESSION['LOGIN_TOKEN']) || request($_SESSION['LOGIN_TOKEN']['KEY']) != $_SESSION['LOGIN_TOKEN']['VAL']) {
            $this->prompt('error', '非法请求!', $back);
        }
        $client_ip = get_ip();
        $error_model = new request_error_model();
        $lockout = $error_model->lockout($client_ip, $_SERVER['REQUEST_TIME']);
        if ($lockout > 0) {
            $this->prompt('error', "由于您多次输入错误的登录信息，本次登录请求已被拒绝，请于{$lockout}分钟后重新尝试!", $back);
        }

        if ($error_model->check($client_ip, $GLOBALS['cfg']['captcha_admin_login'])) {
            $captcha = strtolower(trim(request('captcha', '')));
            if (empty($_SESSION['CAPTCHA']) || $_SESSION['CAPTCHA'] != $captcha) {
                unset($_SESSION['CAPTCHA']);
                $this->prompt('error', '你输入的验证码不正确, 请重新尝试!', $back);
            }
        }

        $merchant_model = new merchant_model();
        $username = trim(request('username', '', 'post'));
        $password = request('password', '', 'post');
        if ($admin = $merchant_model->find(array('username' => $username, 'password' => $password,'status'=>1))) {
            if ($merchant_model->set_logined_info($admin, $client_ip)) {
                if (request('stay', 0) == 1) {
                    $cookie = vencrypt(md5($client_ip . $admin['hash']) . $admin['user_id'], TRUE);
                    setcookie('ADMIN_STAYED', $cookie, $_SERVER['REQUEST_TIME'] + 604800, '/');
                }
//                var_dump($_SESSION['ADMIN']);
//                var_dump($_SESSION['USERADMIN']);
                jump(url($this->MOD . '/main', 'panel'));
            } else {
                $logged_time = date('Y-m-d H:i:s', $admin['last_date']);
                $this->prompt('error', array('该用户已登录系统', "登录IP：{$admin['last_ip']}", "登录时间：{$logged_time}"), $back, 5);
            }
        }

        unset($_SESSION['LOGIN_TOKEN']);
        $error_model->incr_err($client_ip);
        $this->prompt('error', '错误的用户名或密码, 请重新尝试！', $back);
    }

    public function action_panel()
    {
        if (empty($_SESSION['USERADMIN'])) {
            jump(url($this->MOD . '/main', 'index'));
        }
        $merchant_model = new merchant_model();
        $admin = $merchant_model->find(array('user_id' => $_SESSION['USERADMIN']['USER_ID']));
        $admin['last_ip'] = $_SESSION['USERADMIN']['LAST_IP'];
        $admin['last_date'] = $_SESSION['USERADMIN']['LAST_DATE'];
        $this->admin = $admin;
        $this->menus = include(INCL_DIR . DS . 'sys_menu_merchant.php');
        $this->compiler('panel.html');
    }

    public function action_dashboard()
    {
        $admin_info = $this->get_admin_info();
        $merchant_id = $admin_info['user_id'];
        $condition = "merchant_id = $merchant_id";

        switch (request('step')) {
            case 'totals':

                $totals = array
                (
                    'order' => $this->get_condition_count('order_model', $condition),
                    'revenue' => $this->get_condition_count('order_model', $condition . ' and order_status >= 2', 'SUM(order_amount)'),
                    'goods' => $this->get_condition_count('goods_model', $condition),
                );
                echo json_encode($totals);

                break;

            case 'today':

                $today_timestamp = strtotime('today');
                $today = array
                (
                    'order' => $this->get_condition_count('order_model', "{$condition} and created_date >= {$today_timestamp}"),
                    'revenue' => $this->get_condition_count('order_model', "{$condition} and payment_date >= {$today_timestamp}", 'SUM(order_amount)'),
                    'aftersales' => $this->get_condition_count('aftersales_model', "{$condition} and created_date >= {$today_timestamp}"),
                );
                echo json_encode($today);

                break;

            case 'pending':

                $today_timestamp = strtotime('today');
                $pending = array
                (
                    'order' => $this->get_condition_count('order_model', $condition . ' and order_status = 2 OR (order_status = 1 AND payment_method = 2)'),
                    'aftersales' => $this->get_condition_count('aftersales_model', $condition . ' and status = 2'),
                );
                echo json_encode($pending);

                break;

            case 'sysinfo':

                $setting_model = new setting_model();
                $sysinfo = array
                (
                    'vds_version' => "Verydows {$GLOBALS['verydows']['VERSION']} Release {$GLOBALS['verydows']['RELEASE']}",
                    'server_ip' => $_SERVER['SERVER_ADDR'],
                    'server_os' => PHP_OS,
                    'server_soft' => $_SERVER['SERVER_SOFTWARE'],
                    'php_version' => PHP_VERSION,
                    'db_version' => $setting_model->get_db_version(),
                    'db_size' => $setting_model->get_db_size(),
                    'upload_max' => ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'OFF',
                    'upload_size' => $setting_model->get_upload_size(),
                    'timezone' => date_default_timezone_get(),
                    'visitor_stats' => $GLOBALS['cfg']['visitor_stats'] == 1 ? '开启' : '关闭',
                    'rewrite_enable' => $GLOBALS['cfg']['rewrite_enable'] == 1 ? '开启' : '关闭',
                );
                echo json_encode($sysinfo);

                break;

            default:
                $this->admin_info = $admin_info;
                $this->version = $GLOBALS['verydows']['VERSION'];
                $this->compiler('dashboard.html');
        }
    }

    public function action_reset_password()
    {
        $data['password'] = trim(request('new_password', '', 'post'));
        $data['repassword'] = trim(request('repassword', '', 'post'));
        $merchant_model = new merchant_model();
        $verifier = $merchant_model->verifier($data, array('username' => FALSE, 'email' => FALSE, 'name' => FALSE));
        if (TRUE === $verifier) {
            $user_id = $_SESSION['USERADMIN']['USER_ID'];
            $old_password = md5e(trim(request('old_password', '', 'post')));
            if ($merchant_model->find(array('user_id' => $user_id, 'password' => $old_password))) {
                $merchant_model->update(array('user_id' => $user_id), array('password' => md5e($data['password'])));
                $this->prompt('success', '修改密码成功');
            } else {
                $this->prompt('error', '原密码不正确，请重试');
            }
        } else {
            $this->prompt('error', $verifier);
        }
    }

    public function action_logout()
    {
        $active_model = new admin_active_model();
        $active_model->delete(array('sess_id' => session_id()));
        unset($_SESSION['USERADMIN']);
        setcookie('ADMIN_STAYED', null, $_SERVER['REQUEST_TIME'] - 3600, '/');
        jump(url($this->MOD . '/main', 'index'));
    }

    /**
     * 获取符合条件的相关数据总数
     */
    private function get_condition_count($model_name, $condition = null, $col = null)
    {
        $admin_info = $this->get_admin_info();
        $merchant_id = $admin_info['user_id'];
        $condition = empty($condition) ? '1' : $condition;
        $col = empty($col) ? 'COUNT(*)' : $col;
        $model = new $model_name();
        $sql = "SELECT {$col} AS count FROM {$model->table_name} WHERE {$condition}";
        $rs = $model->query($sql);
        return $rs[0]['count'];
    }
}
