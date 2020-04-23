<?php
class search_controller extends general_controller
{
    public function action_index()
    {
        $conditions = array
        (
            'cate' => (int)request('cate', 0),
            'brand' => (int)request('brand', 0),
            'att' => request('att', ''),
            'minpri' => (int)request('minpri', 0),
            'maxpri' => (int)request('maxpri', 0),
            'kw' => strip_tags(trim(request('kw', ''))),
            'sort' => (int)request('sort', 0),
            'page' => (int)request('page', 1),
            'merchant_id'=>(int)request('user_id','0'),
        );
        $goods_model = new goods_model();
        $goods_list = $goods_model->find_goods($conditions, array($conditions['page'], 10));
        if ($goods_list){
            foreach ($goods_list as $k => $v) {
                if ($v['merchant_id'] == '0') {
                    $goods_list[$k]['merchant_id']=array('user_id'=>'0','company_name'=>'海翔旗舰店');
                } else {
                    $goods_list[$k]['merchant_id']=(new merchant_model())->find(array('user_id'=>$v['merchant_id']));
                }
            }
        }


        $this->goods_list=$goods_list;
        $this->filters = $goods_model->set_search_filters($conditions);
        $this->u = $conditions;

        if (empty($conditions['merchant_id'])){
            $order['user_id']=0;
        }else{
            $order['user_id']=$conditions['merchant_id'];
        }
        $this->order=$order;
        $this->hot_searches = !empty($GLOBALS['cfg']['goods_hot_searches']) ? explode(',', $GLOBALS['cfg']['goods_hot_searches']) : null;
        $this->compiler('search.html');
    }

}
