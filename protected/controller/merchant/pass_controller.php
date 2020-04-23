<?php
/*
Author: Administrator
CreeateTame:2019/10/29 15:42
*/

/**
 * Class pass_controller
 * 修改密码
 */
class pass_controller extends general_controller{
    public function action_index(){

        $this->compiler('pass/pass.html');
    }
    public function action_edit(){
        $back = url($this->MOD . '/pass', 'index');
        $admin_info = $this->get_admin_info();
        $merchant=new merchant_model();
        $data = array
        (
            'passworda' => trim(request('passworda', '')),
            'passwordb' => trim(request('passwordb', '')),
            'repassword' => trim(request('repassword', '')),
        );
        if ($data['passworda']==$data['passwordb']){
            $this->prompt('error', '原密码与新密码相同', $back);
        }
        if ($data['repassword']!=$data['passwordb']){
            $this->prompt('error', '两次密码不一致', $back);
        }
        $password=md5e($data['passworda']);
        if ($admin_info['password']!=$password){
            $this->prompt('error', '密码错误', $back);
        }
        $arr['password']=md5e($data['passwordb']);
        $etid=$merchant->update(array('user_id'=>$admin_info['user_id']),$arr);
        if ($etid){
            $active_model = new admin_active_model();
            $active_model->delete(array('sess_id' => session_id()));
            unset($_SESSION['USERADMIN']);
            setcookie('ADMIN_STAYED', null, $_SERVER['REQUEST_TIME'] - 3600, '/');
            echo '<script>window.parent.location.reload();</script>';
            die();
        }else{
            $this->prompt('error', '系统繁忙，请稍后再试', $back);
        }
    }
}
