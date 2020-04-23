<?php

/*
Author: Administrator
CreeateTame:2019/10/31 11:01
*/

class application_controller extends general_controller
{
    /**
     * 申请入驻首页
     */
    public function action_index()
    {
        $enter = new user_enter_model();
        $list = $enter->find_all('', 'mobile desc', '*', array(request('page', 1), request('pernum', 15)));
        if ($list){
            foreach ($list as $k => $v) {
                $list[$k]['user_id'] = (new user_profile_model())->find(array('user_id' => $v['user_id']));
            }
        }
        $this->results = $list;
        $this->paging = $enter->page;
        $this->compiler('application/application_index.html');
    }

    /**
     * 申请入驻详情页面
     */
    public function action_show()
    {
        $id = (int)request('id', 0);
        $enter = new user_enter_model();
        $info = $enter->find(array('id' => $id));
        $info['user_id'] = (new user_profile_model())->find(array('user_id' => $info['user_id']));
        $this->info = $info;
        $this->compiler('application/application.html');
    }

    public function action_examine()
    {

        $data = array(
            'meta_description' => request('meta_description', ''),
            'status' => request('status', '')
        );
        $id = (int)request('id', '');
        $enter = new user_enter_model();
        $res = $enter->update(array('id' => $id), $data);
        if ($res) {
            $this->prompt('success', '提交成功');
        } else {
            $this->prompt('error', '系统繁忙，请稍后再试');
        }
    }
}
