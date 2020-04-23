<?php
include(VIEW_DIR.DS.'function'.DS.'backend_paging.php');

class general_controller extends Controller
{
    public function init()
    {
        $this->MOD = substr(strrchr(dirname(__FILE__), DS), 1);
        $acl = new acl($this->MOD);
        $this->baseurl = $GLOBALS['cfg']['http_host'];
    }
    
    protected function compiler($tpl_name)
    {
        $this->display($this->MOD.DS.$tpl_name);
    }
    
    protected function prompt($type = 'default', $text = '', $redirect = '', $time = 3)
    {
        if(empty($redirect)) $redirect = 'javascript:history.back()';
        $this->rs = array('type' => $type, 'text' => $text, 'redirect' => $redirect, 'time' => $time);
        $this->compiler('prompt.html');
        exit;
    }


    protected function get_admin_info(){
        if (empty($_SESSION['USERADMIN'])){
            echo '<script>window.parent.location.reload();</script>';
            die();
        }
        $merchant_model = new merchant_model();
        $admin = $merchant_model->find(array('user_id' => $_SESSION['USERADMIN']['USER_ID']));
        $admin['last_ip'] = $_SESSION['USERADMIN']['LAST_IP'];
        $admin['last_date'] = $_SESSION['USERADMIN']['LAST_DATE'];
        return $admin;
    }

} 