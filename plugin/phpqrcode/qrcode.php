<?php


class qrcode
{
    public function get_qrcode(){
        include 'phpqrcode.php';
        QRcode::png('http://www.cnblogs.com/txw1958/');
    }
}