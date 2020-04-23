<?php
class favorite_controller extends general_controller
{
    /**
     * 商品收藏
     */
    public function action_list()
    {
        $user_id = $this->is_logined();
        $favor_model = new user_favorite_model();
        $list=$favor_model->get_user_favorites($user_id, array(request('page', 1), 10));
        if ($list){
            foreach ($list as $k=>$v){
                if ($v['merchant_id']){
                    $merchant=(new merchant_model())->find(array('user_id'=>$v['merchant_id']));
                    $list[$k]['company_name']=$merchant['company_name'];
                }else{
                    $list[$k]['company_name']='海翔官方旗舰店';
                }

            }
        }

        $this->favorites = array
        (
            'list' =>$list,
            'paging' => $favor_model->page,
        );


        $this->compiler('user_favorite_list.html');
    }

    public function action_delete()
    {
        $user_id = $this->is_logined();
        $id = request('goods_id', null);
        if(!empty($id))
        {
            $favor_model = new user_favorite_model();
            if(is_array($id))
            {
                foreach($id as $v) $favor_model->delete(array('goods_id' => (int)$v, 'user_id' => $user_id));
            }
            else
            {
                $favor_model->delete(array('goods_id' => (int)$id, 'user_id' => $user_id));
            }
            jump(url('favorite', 'list'));
        }
        else
        {
            jump(url('main', '404'));
        }
    }



    public function action_store()
    {
        $user_id = $this->is_logined();
        $store_model = new user_store_model();
        $this->favorites = array
        (
            'list' =>$store_model->get_user_favorites($user_id, array(request('page', 1), 10)),
            'paging' => $store_model->page,
        );

        $this->compiler('user_store_list.html');
    }

    public function action_remove()
    {
        $user_id = $this->is_logined();
        $id = request('merchant_id', null);
        if(!empty($id))
        {
            $favor_model = new user_store_model();
            if(is_array($id))
            {
                foreach($id as $v) $favor_model->delete(array('merchant_id' => (int)$v, 'user_id' => $user_id));
            }
            else
            {
                $favor_model->delete(array('merchant_id' => (int)$id, 'user_id' => $user_id));
            }
            jump(url('favorite', 'store'));
        }
        else
        {
            jump(url('main', '404'));
        }
    }
}
