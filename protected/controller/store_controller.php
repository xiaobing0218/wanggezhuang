<?php
class store_controller extends general_controller{
    public function action_index()
    {$page = [
            request('page', 1),
            $GLOBALS['cfg']['cate_goods_per_num']
        ];

        $merchant_model = new merchant_model();
        $conditions['status']=1;
        $goods_list = $merchant_model->find_all($conditions,'','*', $page);
        $this->goods_list=$goods_list;
        $this->goods_paging = $merchant_model->page;
        $this->title = '全部店铺';
        $this->a = 'recommend';
        $this->u = '';
        $this->compiler('all_shop.html');


    }
}
