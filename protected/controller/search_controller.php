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
            'kw' => trim(strip_tags(request('kw', ''))),
            'sort' => (int)request('sort', 0),
            'page' => (int)request('page', 1),
        );

        $goods_model = new goods_model();
        $goods_list = $goods_model->find_goods($conditions, array($conditions['page'], $GLOBALS['cfg']['goods_search_per_num']));
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
        $this->goods_paging = $goods_model->page;
        $this->guess_likes = $goods_model->get_guess_like();
        $this->history = $goods_model->get_history();
        $this->u = $conditions;
        $this->compiler('search.html');
    }

}
