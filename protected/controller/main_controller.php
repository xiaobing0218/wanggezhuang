<?php

class main_controller extends general_controller
{
    public function action_index()
    {
        /**
         * 首页
         */

        if (is_mobile_device() && request('display') != 'pc') jump(url('mobile/main', 'index'));

        $goods_cate_model = new goods_cate_model();
        $this->catebar = $goods_cate_model->goods_cate_bar();
        $vcache = vcache::instance();

        $this->nav = $vcache->nav_model('get_site_nav');

        $this->hot_searches = explode(',', $GLOBALS['cfg']['goods_hot_searches']);

        $newarrival = $vcache->goods_model('find_goods', array(array('newarrival' => 1), 8), $GLOBALS['cfg']['data_cache_lifetime']);
        if ($newarrival){
            foreach ($newarrival as $k => $v) {
                if ($v['merchant_id'] == '0') {
                    $newarrival[$k]['merchant_id']=array('user_id'=>'0','company_name'=>'海翔旗舰店');
                } else {
                    $newarrival[$k]['merchant_id']=(new merchant_model())->find(array('user_id'=>$v['merchant_id']));
                }
            }
        }

        $this->newarrival=$newarrival;
        $recommend = $vcache->goods_model('find_goods', array(array('recommend' => 1), 8), $GLOBALS['cfg']['data_cache_lifetime']);
        if ($recommend) {
            foreach ($recommend as $k => $v) {
                if ($v['merchant_id'] == '0') {
                    $recommend[$k]['merchant_id'] = array('user_id' => '0', 'company_name' => '海翔旗舰店');
                } else {
                    $recommend[$k]['merchant_id'] = (new merchant_model())->find(array('user_id' => $v['merchant_id']));
                }
            }
        }
        $this->recommend=$recommend;
        $bargain = $vcache->goods_model('find_goods', array(array('bargain' => 1), 8), $GLOBALS['cfg']['data_cache_lifetime']);
        if ($recommend) {
            foreach ($bargain as $k => $v) {
                if ($v['merchant_id'] == '0') {
                    $bargain[$k]['merchant_id'] = array('user_id' => '0', 'company_name' => '海翔旗舰店');
                } else {
                    $bargain[$k]['merchant_id'] = (new merchant_model())->find(array('user_id' => $v['merchant_id']));
                }
            }
        }
        $this->bargain=$bargain;

        $youwan = $vcache->goods_model('find_goods', array(array('g_p' => 1), 8), $GLOBALS['cfg']['data_cache_lifetime']);
        if ($youwan) {
            foreach ($youwan as $k => $v) {
                if ($v['merchant_id'] == '0') {
                    $youwan[$k]['merchant_id'] = array('user_id' => '0', 'company_name' => '海翔旗舰店');
                } else {
                    $youwan[$k]['merchant_id'] = (new merchant_model())->find(array('user_id' => $v['merchant_id']));
                }
            }
        }
        $this->youwan=$youwan;

        $this->latest_article = $vcache->article_model('get_latest_article', array(4), $GLOBALS['cfg']['data_cache_lifetime']);

        $ad = (new adv_model())->find_all(['position_id' => 1, 'type' => 'image'], 'seq ASC');
        foreach ($ad as $key => $item) {
            $info = json_decode($item['params'], true);
            $ad[$key] = [
                'src' => $info['src'],
                'link' => $info['link']
            ];
        }
        $ce1=(new adv_model())->find(['position_id' => 3, 'type' => 'image']);
            $info = json_decode($ce1['params'], true);
            $ce[] = [
                'src' => $info['src'],
                'link' => $info['link']
            ];
        $ce2=(new adv_model())->find(['position_id' => 4, 'type' => 'image']);
        $info = json_decode($ce2['params'], true);
        $ce[] = [
            'src' => $info['src'],
            'link' => $info['link']
        ];
        $ce3=(new adv_model())->find(['position_id' => 5, 'type' => 'image']);
        $info = json_decode($ce3['params'], true);
        $ce[] = [
            'src' => $info['src'],
            'link' => $info['link']
        ];

        $ce4=(new adv_model())->find(['position_id' => 14, 'type' => 'image']);
        $info = json_decode($ce4['params'], true);
        $ce[] = [
            'src' => $info['src'],
            'link' => $info['link']
        ];

        $this->ce = $ce;
        $this->ad = $ad;

        //广告位1
        $adv11 = (new adv_model())->find_all(['position_id' => 11, 'type' => 'image']);
        foreach ($adv11 as $key => $item) {
            $info = json_decode($item['params'], true);
            $adv11[$key] = [
                'src' => $info['src'],
                'link' => $info['link']
            ];
        }
        $this->adv11 = $adv11;

        //广告位2
        $adv12 = (new adv_model())->find_all(['position_id' => 12, 'type' => 'image']);
        foreach ($adv12 as $key => $item) {
            $info = json_decode($item['params'], true);
            $adv12[$key] = [
                'src' => $info['src'],
                'link' => $info['link']
            ];
        }
        $this->adv12 = $adv12;

        //广告位3
        $adv13 = (new adv_model())->find_all(['position_id' => 13, 'type' => 'image']);
        foreach ($adv13 as $key => $item) {
            $info = json_decode($item['params'], true);
            $adv13[$key] = [
                'src' => $info['src'],
                'link' => $info['link']
            ];
        }
        $this->adv13 = $adv13;


        //广告位4
        $adv14 = (new adv_model())->find_all(['position_id' => 15, 'type' => 'image']);
        foreach ($adv14 as $key => $item) {
            $info = json_decode($item['params'], true);
            $adv14[$key] = [
                'src' => $info['src'],
                'link' => $info['link']
            ];
        }
        $this->adv14 = $adv14;


        $this->compiler('index.html');
    }

    public function action_404()
    {
        $this->compiler('404.html');
    }
}
