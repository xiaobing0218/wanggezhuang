<?php

class merchant_controller extends general_controller
{
    public function action_index()
    {
        $merchant_model = new merchant_model();
        $list = $merchant_model->find_all(null, 'user_id DESC', '*', array(request('page', 1), 15));
        $this->results = $list ? $list : [];
        $this->paging = $merchant_model->page;
        $this->compiler('merchant/merchant_list.html');
    }

    public function action_add()
    {
        if (request('step') == 'submit') {
            if (empty($_SESSION['SUBMIT_TOKEN']) || request('token') != $_SESSION['SUBMIT_TOKEN']) $this->prompt('error', '非法提交');

            $data = array
            (
                'username' => trim(request('username', '')),
                'password' => trim(request('password', '')),
                'repassword' => trim(request('repassword', '')),
                'full_name' => trim(request('full_name', '')),
                'telephone' => trim(request('telephone', '')),
                'company_name' => trim(request('company_name', '')),
                'abbreviation' => trim(request('abbreviation', '')),
                'address' => trim(request('address', '')),
                'license' => trim(request('license', '')),
                'other' => trim(request('other', '')),
                'created_date' => $_SERVER['REQUEST_TIME'],
            );

            $merchant_model = new merchant_model();
            $verifier = $merchant_model->verifier($data);
            if (TRUE === $verifier) {
                $data['password'] = md5e($data['password']);
                $data['hash'] = sha1(uniqid(rand(), TRUE));
                unset($data['repassword']);
                if ($user_id = $merchant_model->create($data)) {
                    $this->prompt('success', '添加商家成功', url($this->MOD . '/merchant', 'index'));
                }
                $this->prompt('error', '添加商家失败');
            } else {
                $this->prompt('error', $verifier);
            }
        } else {
            $_SESSION['SUBMIT_TOKEN'] = uniqid(rand());
            $this->token = $_SESSION['SUBMIT_TOKEN'];
            $this->compiler('merchant/merchant.html');
        }
    }

    public function action_edit()
    {
        if (request('step') == 'submit') {
            if (empty($_SESSION['SUBMIT_TOKEN']) || request('token') != $_SESSION['SUBMIT_TOKEN']) $this->prompt('error', '非法提交');

            $user_id = request('id');
            $condition = array('user_id' => $user_id);
            $data = array
            (
                'username' => trim(request('username', '')),
                'password' => trim(request('password', '')),
                'repassword' => trim(request('repassword', '')),
                'full_name' => trim(request('full_name', '')),
                'telephone' => trim(request('telephone', '')),
                'company_name' => trim(request('company_name', '')),
                'abbreviation' => trim(request('abbreviation', '')),
                'address' => trim(request('address', '')),
                'license' => trim(request('license', '')),
                'other' => trim(request('other', '')),
            );
            $rule_slices = array();
            $merchant_model = new merchant_model();
            $user = $merchant_model->find($condition);
            if ($user['username'] == $data['username']) {
                $rule_slices['username'] = FALSE;
                unset($data['username']);
            }
            if (empty($data['password']) && empty($data['repassword'])) {
                $rule_slices['password'] = $rule_slices['repassword'] = FALSE;
                unset($data['password']);
            }
            $verifier = $merchant_model->verifier($data, $rule_slices);
            if (TRUE === $verifier) {
                if (isset($data['password'])) $data['password'] = md5e($data['password']);
                unset($data['repassword']);
                $merchant_model->update($condition, $data);

                unset($_SESSION['SUBMIT_TOKEN']);
                $this->prompt('success', '更新商家成功', url($this->MOD . '/merchant', 'index'));
            } else {
                $this->prompt('error', $verifier);
            }
        } else {
            $user_id = (int)request('id', 0);
            $condition = array('user_id' => $user_id);
            $merchant_model = new merchant_model();
            if ($rs = $merchant_model->find($condition)) {

                   $rs['license']=str_replace('"','',$rs['license']);
                   if ($rs['other']){
                       $rs['other']=str_replace('"','',$rs['other']);
                   }

                $this->rs = $rs;
                $_SESSION['SUBMIT_TOKEN'] = uniqid(rand());
                $this->token = $_SESSION['SUBMIT_TOKEN'];
                $this->compiler('merchant/merchant.html');
            } else {
                $this->prompt('error', '未找到相应的数据记录');
            }
        }
    }

    public function action_status(){
        $id = request('id');
        if ($id == 12){
            $this->prompt('error', '此商家为官方自营，不能暂停');
            exit();
        }
        $merchant_model = new merchant_model();
        $condition = array('user_id' => $id);
        $rs = $merchant_model->find($condition);
        if (!$rs){
            $this->prompt('error', '未找到相应的数据记录');
        }
        $update=$merchant_model->update($condition,array('status'=>0));
        if (!$update){
            $this->prompt('error', '暂停使用失败');
        }
        $good=new goods_model();
        $good_up=$good->update(array('merchant_id'=>$id),array('status'=>0));
        if ($good_up){
            $this->prompt('success', '暂停商家成功', url($this->MOD . '/merchant', 'index'));
        }else{
            $this->prompt('error', '暂停商家失败');
        }
    }
    public function action_enable(){
        $id = request('id');
        if ($id == 12){
            $this->prompt('error', '此商家为官方自营，不能暂停');
            exit();
        }
        $merchant_model = new merchant_model();
        $condition = array('user_id' => $id);
        $rs = $merchant_model->find($condition);
        if (!$rs){
            $this->prompt('error', '未找到相应的数据记录');
        }
        $update=$merchant_model->update($condition,array('status'=>1));
        if (!$update){
            $this->prompt('error', '启用使用失败');
        }
        $good=new goods_model();
        $good_up=$good->update(array('merchant_id'=>$id),array('status'=>1));
        if ($good_up){
            $this->prompt('success', '启用商家成功', url($this->MOD . '/merchant', 'index'));
        }else{
            $this->prompt('error', '启用商家失败');
        }
    }
    public function action_delete()
    {
        $id = request('id');
        if ($id == 12){
            $this->prompt('error', '此商家为官方自营，不能被删除');
            exit();
        }
        $merchant_model = new merchant_model();
        if ($merchant_model->delete(array('user_id' => $id)) > 0) $this->prompt('success', '删除商家成功', url($this->MOD . '/merchant', 'index'));
        $this->prompt('error', '删除商家失败');
    }

    //清除缓存
    private function clear_cache()
    {
        return vcache::instance()->merchant_model('indexed_list', null, -1);
    }

    public function action_img(){

        $img=request('img','','post');
        if (strstr($img,",")){
            $img = explode(',',$img);
            $img = $img[1];
        }
        $save_path = 'upload/brand/logo';
        $img_name='/license'.time(). rand('100','999').'.png';
        $r = file_put_contents($save_path.$img_name, base64_decode($img));//返回的是字节数
        if (!$r) {
            $tmparr1=array('data'=>null,"code"=>1,"msg"=>"图片生成失败");
            echo json_encode($tmparr1);
        }else{
            $license=$save_path.$img_name.'?a='.rand(1000,9999);

            echo json_encode($license);
        }

    }
    public function action_preview(){
        $user_id = (int)request('id', 0);
        $condition = array('user_id' => $user_id);
        $merchant_model = new merchant_model();
        if ($rs = $merchant_model->find($condition)) {

            $rs['license']=str_replace('"','',$rs['license']);
            if ($rs['other']){
                $rs['other']=str_replace('"','',$rs['other']);
            }

            $this->rs = $rs;
            $_SESSION['SUBMIT_TOKEN'] = uniqid(rand());
            $this->token = $_SESSION['SUBMIT_TOKEN'];
            $this->compiler('merchant/preview.html');
        }
    }
    public function action_remove(){
        $user_id = (int)request('id', 0);
        $merchant_model = new merchant_model();
        $condition = array('user_id' => $user_id);
        $rs = $merchant_model->find($condition);
        if (!$rs){
            $this->prompt('error', '未找到相应的数据记录');
        }
         if (!$rs['open_id']){
             $this->prompt('success', '该商家未绑定微信', url($this->MOD . '/merchant', 'index'));
         }
        $update=$merchant_model->update($condition,array('open_id'=>''));
        if ($update){
            $this->prompt('success', '解绑微信成功', url($this->MOD . '/merchant', 'index'));
        }else{
            $this->prompt('error', '解绑微信失败');
        }
    }

}
