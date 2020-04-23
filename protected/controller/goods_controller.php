<?php

/**
 * Class goods_controller
 * 前台
 * 新品推荐（）
 * 政府采购（）
 * 促销商品（）
 */
class goods_controller extends general_controller
{
    public function action_index()
    {
        $id = (int)request('id', 0);
        $condition = array('goods_id' => $id);
        $goods_model = new goods_model();
        if($goods = $goods_model->find($condition))
        {
            $cate_model = new goods_cate_model();
            $this->breadcrumbs = $cate_model->breadcrumbs($goods['cate_id']);
            //商品信息
            $this->goods = $goods;
            //店铺信息
            if ($goods['merchant_id']){
                $this->store=(new merchant_model())->find(array('user_id'=>$goods['merchant_id']));
            }else{
                $this->store=array('user_id'=>0,'company_name'=>'官方自营');

            }
            //商品相册
            $album_model = new goods_album_model();
            $album_list = $album_model->find_all($condition);
            if(!empty($album_list)&&is_array($album_list)){
                foreach($album_list as $key => $v)
                {
                    $tion = array('goods_id' => $v['goods_id']);
                    $info = $goods_model->find($tion);
                    $album_list[$key]['merchant_id'] = $info['merchant_id'];
                }
            }
            $this->album_list = $album_list;
            //商品评分
            $review_model = new goods_review_model();
            $this->rating = $review_model->get_rating_stats($id,'1');
            //购买选择项
            $optl_model = new goods_optional_model();
            $this->opt_list = $optl_model->get_goods_optional($id,$goods['merchant_id']);
            //商品规格
            $attr_model = new goods_attr_model();
            $this->specs = $attr_model->get_goods_specs($id);
            //关联商品
            $this->related = $goods_model->get_related($id, $GLOBALS['cfg']['goods_related_num']);
            //热销商品
            $this->bestseller = vcache::instance()->goods_model('get_bestseller', null, 10, $GLOBALS['cfg']['data_cache_lifetime']);
            //保存浏览历史
            $goods_model->set_history($id);

            //判断该产品是否被收藏

            if (!empty($_SESSION['USER']['USER_ID'])){
                $this->goodscollect=(new user_favorite_model())->find(array('user_id'=>$_SESSION['USER']['USER_ID'],'goods_id'=>$id));
            }else{
                $this->goodscollect='';
            }
            //判断该店铺是否被收藏
            if (!empty($_SESSION['USER']['USER_ID'])){
                $this->shopcollect=(new user_store_model())->find(array('user_id'=>$_SESSION['USER']['USER_ID'],'merchant_id'=>$goods['merchant_id']));
            }else{
                $this->shopcollect='';
            }

            $this->compiler('goods.html');
        }
        else
        {
            jump(url('main', '404'));
        }
    }

    /**
     * 新品推荐
     */
    public function action_recommend()
    {
        $conditions = [
            'recommend' => 1,
            'kw' => (string)request('kw', ''),
            'minpri' => (int)request('minpri', 0),
            'maxpri' => (int)request('maxpri', 0),
            'sort' => (int)request('sort', 0)
        ];

        $page = [
            request('page', 1),
            $GLOBALS['cfg']['cate_goods_per_num']
        ];

        $goods_model = new goods_model();
        $goods_list = $goods_model->find_goods($conditions, $page);
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
        $this->goods_paging = $goods_model->page;
        $this->title = '热门推荐';
        $this->a = 'recommend';
        $this->u = $conditions;
        $this->compiler('goods_recommend.html');
    }

    /**
     * 促销商品
     */
    public function action_promotion()
    {
        $conditions = [
            'bargain' => 1,
            'kw' => (string)request('kw', ''),
            'minpri' => (int)request('minpri', 0),
            'maxpri' => (int)request('maxpri', 0),
            'sort' => (int)request('sort', 0)
        ];
        $page = [
            request('page', 1),
            $GLOBALS['cfg']['cate_goods_per_num']
        ];

        $goods_model = new goods_model();
        $goods_list = $goods_model->find_goods($conditions, $page);
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
        $this->goods_paging = $goods_model->page;
        $this->title = '特价促销';
        $this->a = 'promotion';
        $this->u = $conditions;
        $this->compiler('goods_recommend.html');
    }

    /**
     * 政府采购
     */
    public function action_purchase()
    {
        $conditions = [
            'g_p' => 1,
            'kw' => (string)request('kw', ''),
            'minpri' => (int)request('minpri', 0),
            'maxpri' => (int)request('maxpri', 0),
            'sort' => (int)request('sort', 0)
        ];
        $page = [
            request('page', 1),
            $GLOBALS['cfg']['cate_goods_per_num']
        ];

        $goods_model = new goods_model();
        $goods_list = $goods_model->find_goods($conditions, $page);
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
        $this->goods_paging = $goods_model->page;
        $this->title = '政府采购';
        $this->a = 'purchase';
        $this->u = $conditions;
        $this->compiler('goods_recommend.html');
    }

    public function action_newarrival()
    {
        $conditions = [
            'newarrival' => 1,
            'kw' => (string)request('kw', ''),
            'minpri' => (int)request('minpri', 0),
            'maxpri' => (int)request('maxpri', 0),
            'sort' => (int)request('sort', 0)
        ];
        $page = [
            request('page', 1),
            $GLOBALS['cfg']['cate_goods_per_num']
        ];

        $goods_model = new goods_model();
        $goods_list = $goods_model->find_goods($conditions, $page);
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
        $this->goods_paging = $goods_model->page;
        $this->title = '新品推荐';
        $this->a = 'newarrival';
        $this->u = $conditions;
        $this->compiler('goods_recommend.html');
    }

    /**
     * 店铺商品页
     */
    public function action_shop(){

        $conditions = [
            'merchant_id' => (int)request('id', 0),
            'kw' => (string)request('kw', ''),
            'minpri' => (int)request('minpri', 0),
            'maxpri' => (int)request('maxpri', 0),
            'sort' => (int)request('sort', 0)
        ];
        $page = [
            request('page', 1),
           20
        ];
        $merchant_model = new merchant_model();
        $merchant= $merchant_model->find(array('user_id'=>$conditions['merchant_id'],'status'=>1));
        if (!$merchant){
            jump(url('main', '404'));
        }
        $this->merchant=$merchant;
        $goods=new goods_model();
        $this->goods_list=$goods->find_goods($conditions,$page);
        $this->title = $merchant['company_name'];
        $this->goods_paging = $goods->page;
        $this->a = 'shop';
        $this->u = $conditions;
        $this->id=$conditions['merchant_id'];
        $this->compiler('goods_shop.html');
    }
}
