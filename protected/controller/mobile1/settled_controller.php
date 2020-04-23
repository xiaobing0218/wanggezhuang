<?php
class settled_controller extends general_controller{
    public function action_index()
    {
        $user_id = $this->is_logined();
        $info=(new user_enter_model())->find(array('user_id'=>$user_id));
        if ($info['status']==3){
            $this->info='';
        }else{
            $this->info=$info;
        }
        $this->compiler('user_settled.html');
    }
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
            (new user_enter_model())->update(array('user_id'=>$user_id),$data);
            $res='success';
        }else{
            $res='请上传图片';
        }



        echo $res;
    }
}
