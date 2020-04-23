<?php
class goods_controller extends general_controller
{
    public function action_index()
    {
        $condition = array('goods_id' => (int)request('id', 0));
        $goods_model = new goods_model();
        if($goods = $goods_model->find($condition))
        {
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
            $this->album_list = $album_model->find_all($condition);
            //购买选择项
            $optl_model = new goods_optional_model();
            $this->opt_list = $optl_model->get_goods_optional($condition['goods_id'],$goods['merchant_id']);
            //商品评价
            $review_model = new goods_review_model();
            $this->review_rating = $review_model->get_rating_stats($condition['goods_id']);
            //关联商品
            $this->related = $goods_model->get_related($condition['goods_id'], $GLOBALS['cfg']['goods_related_num']);
            //热销商品
            $bestseller = vcache::instance()->goods_model('get_bestseller', null, 10, $GLOBALS['cfg']['data_cache_lifetime']);
            if ($bestseller){
                foreach ($bestseller as $k=>$v){
                    $order=(new merchant_model())->find(array('user_id'=>$v['merchant_id']));
                    $bestseller[$k]['abbreviation']=$order['abbreviation'];
                    $bestseller[$k]['logo']=$order['logo'];
                }
            }
            $this->bestseller=$bestseller;
            //保存浏览历史
            $goods_model->set_history($condition['goods_id']);
            //判断该产品是否被收藏

            if (!empty($_SESSION['USER']['USER_ID'])){
                $this->goodscollect = (new user_favorite_model())->find(array('user_id' => $_SESSION['USER']['USER_ID'], 'goods_id' => (int)request('id', 0)));
            } else {
                $this->goodscollect = '';
            }
            //判断该店铺是否被收藏
            if (!empty($_SESSION['USER']['USER_ID'])) {
                $this->shopcollect = (new user_store_model())->find(array('user_id' => $_SESSION['USER']['USER_ID'], 'merchant_id' => $goods['merchant_id']));
            } else {
                $this->shopcollect = '';
            }

            $img = 'https://hxbg.cn/upload/goods/prime/350x350/' . $goods['goods_image'];
            $this->image = $img;

            $this->compiler('goods.html');
        }
        else
        {
            jump(url('mobile/main', '404'));
        }
    }


    public function action_illustrated()
    {
        $goods_model = new goods_model();
        if($this->goods = $goods_model->find(array('goods_id' => (int)request('id', 0)), null, 'goods_name, goods_content'))
        {
            $this->compiler('goods_illustrated.html');
        }
        else
        {
            jump(url('mobile/main', '404'));
        }
    }

    public function action_specs()
    {
        $attr_model = new goods_attr_model();
        $this->specs = $attr_model->get_goods_specs((int)request('id', 0));
        $this->compiler('goods_specs.html');
    }

    public function action_reviews()
    {
        $id = (int)request('id', 0);
        $goods_model = new goods_model();
        if($this->goods = $goods_model->find(array('goods_id' => $id), null, 'goods_id, goods_name'))
        {
            $review_model = new goods_review_model();
            $this->rating = $review_model->get_rating_stats($id);
            $this->compiler('goods_review_list.html');
        }
        else
        {
            jump(url('mobile/main', '404'));
        }
    }

//店铺

    public function action_shop()
    {
        $id=request('id',0);
        $this->order=(new merchant_model())->find(array('user_id'=>$id));
       $this->list=(new goods_model())->find_all(array('merchant_id'=>$id),'','*',array(request('page', 1), request('pernum', 10)));
        if (!empty($_SESSION['USER']['USER_ID'])){
            $this->shopcollect=(new user_store_model())->find(array('user_id'=>$_SESSION['USER']['USER_ID'],'merchant_id'=>$id));
        }else{
            $this->shopcollect='';
        }
        $this->id=$id;
        $this->compiler('shop_index.html');
    }

}
