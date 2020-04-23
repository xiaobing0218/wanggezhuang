<?php
/*
Author: Administrator
CreeateTame:2019/10/31 9:33
*/

class enter_controller extends general_controller{
    //申请入驻首页
    public function action_index(){
        $user_id = $this->is_logined();
        $info=(new user_enter_model())->find(array('user_id'=>$user_id));
        if ($info['status']==3){
            $this->info='';
        }else{
            $this->info=$info;
        }
        $this->compiler('user_enter_index.html');
    }
    /**
     * 添加入驻申请
     */
    public function action_add(){
        $back = url( '/enter', 'index');
        $user_id = $this->is_logined();
        $info=(new user_enter_model())->find(array('user_id'=>$user_id));
        if ($info){
            $data = array
            (
                'company_name' => trim(request('company_name', '')),
                'legal_name' => trim(request('legal_name', '')),
                'contact' => trim(request('contact', '')),
                'address' => trim(request('address', '')),
                'user_id'=>$user_id,
                'mobile'=>time(),
                'status'=>'0',
            );
             $res=(new user_enter_model())->update(array('user_id'=>$user_id),$data);
        }else{
            $this->prompt('error', '请上传图片', $back);
        }

        if ($res){
            $this->prompt('success', '入驻申请已成功提交', $back);
        }else{
            $this->prompt('error', '系统繁忙，请稍后再试', $back);
        }

    }
    public function action_img(){
        $user_id = $this->is_logined();
        $img=request('img','','post');
        if (strstr($img,",")){
            $img = explode(',',$img);
            $img = $img[1];
        }
        $save_path = 'upload/brand/logo';
        $img_name='/license'.$user_id.'.png';
        $r = file_put_contents($save_path.$img_name, base64_decode($img));//返回的是字节数
        if (!$r) {
            $tmparr1=array('data'=>null,"code"=>1,"msg"=>"图片生成失败");
            echo json_encode($tmparr1);
        }else{
            $data['license']=$save_path.$img_name.'?a='.rand(1000,9999);
            $data['user_id']=$user_id;
            $data['mobile']=time();
            $info=(new user_enter_model())->find(array('user_id'=>$user_id));
            if ($info){
                (new user_enter_model())->update(array('user_id'=>$user_id),$data);
            }else{
                $data['status']=3;
                (new user_enter_model())->create($data);
            }
            $tmparr2=array('data'=>1,"code"=>0,"msg"=>"图片生成成功");
            echo json_encode($tmparr2);
        }

    }
}