<?php


class qrcode_controller extends general_controller
{
    public function action_index(){
        $url = request('url');
        include  APP_DIR.'/plugin/phpqrcode/phpqrcode.php';
        ob_start();
        QRcode::png($url, false , '1', 10 , $margin = 2, $saveandprint=true);
    }
}