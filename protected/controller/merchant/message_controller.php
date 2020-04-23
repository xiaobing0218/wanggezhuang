<?php

/*
Author: Administrator
CreeateTame:2019/10/29 11:35
企业信息
*/

class message_controller extends general_controller
{
    /**
     * 企业信息修改
     */
    public function action_index()
    {
        $admin_info = $this->get_admin_info();
        if (!$admin_info) {
            $this->prompt('error', '未找到相应的数据记录');
        }

        $this->user = $admin_info;
        $this->compiler('message/message.html');
    }

    /**
     * 修改企业信息
     */
    public function action_edit()
    {
        $back = url($this->MOD . '/message', 'index');
        $admin_info = $this->get_admin_info();
        $merchant=new merchant_model();
        $data = array
        (
            'full_name' => trim(request('full_name', '')),
            'telephone' => trim(request('telephone', '')),
            'company_name' => trim(request('company_name', '')),
            'abbreviation' => trim(request('abbreviation', '')),
            'address' => trim(request('address', '')),
            'hash' => trim(request('token', '')),
            'logo' => trim(request('logo_src', '')),
            'introduction' => trim(request('introduction', '')),
            'bannaner' => trim(request('bannaner_src', '')),
            'mail' => trim(request('mail', '')),
        );

        if (!empty($_FILES['logo']['name'])) {

            $save_path = 'upload/brand/logo';
            if (!in_array($_FILES['logo']['type'], array('image/jpeg', 'image/gif', 'image/png'))) {
                $this->prompt('error', '请选择正确的图片格式', $back);
            }
            $ext = pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION);
            $logo['url'] = $save_path . '/logo' . $admin_info['user_id'] .'.'. $ext;
            if (!move_uploaded_file($_FILES['logo']['tmp_name'], $logo['url'])) {
                $this->prompt('error', '服务器繁忙，请稍后再试', $back);
            }
            (new imgsuto( $logo['url'],'1'))->compressImg($logo['url']);
            $data['logo'] = $logo['url'].'?a='.rand(1000,9999);
        }
        if (!empty($_FILES['bannaner']['name'])) {
//            $imageinfo=getimagesize($_FILES['bannaner']['tmp_name']);
//            $width  = $imageinfo[0];
//            $height =$imageinfo[1];
//            if ($height>=100||$width==1100){
//                $this->prompt('error', '请选择正确的图片格式', $back);
//            }
            $save_path = 'upload/brand/logo';
            if (!in_array($_FILES['bannaner']['type'], array('image/jpeg', 'image/gif', 'image/png'))) {
                $this->prompt('error', '请选择正确的图片格式', $back);
            }
            $ext = pathinfo($_FILES['bannaner']['name'], PATHINFO_EXTENSION);
            $logo['url'] = $save_path . '/bannaner' . $admin_info['user_id'] .'.'. $ext;
            if (!move_uploaded_file($_FILES['bannaner']['tmp_name'], $logo['url'])) {
                $this->prompt('error', '服务器繁忙，请稍后再试', $back);
            }
            (new imgsuto( $logo['url'],'1'))->compressImg($logo['url']);
            $data['bannaner'] =  $logo['url'].'?a='.rand(1000,9999);
        }
        $res=$merchant->update(array('user_id'=>$admin_info['user_id']),$data);

        if ($res){
            $this->prompt('success', '修改企业信息成功',$back);
        }else{
            $this->prompt('error', '服务器繁忙，请稍后再试', $back);
        }

    }
}
