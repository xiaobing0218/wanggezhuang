<?php

class wechat_controller
{
    public function action_index()
    {
        $wechat = (new wechat);
        //$wechat->valid();
        $wechat->responseMsg();
    }
    public function action_set_menu(){
        $data=[
            'button'=>[
                [
                    'type'=>'view',
                    'name'=>'海翔资讯',
                    'url'=>'https://hxbg.cn/m/article/index.html',
                ],
                [
                    'type'=>'view',
                    'name'=>'办公商城',
                    'url'=>'https://hxbg.cn/',
                ],
                [
                    'name'=>'关于我们',
                    'sub_button'=>[
                        [
                            'type'=>'view',
                            'name'=>'公司简介',
                            'url'=>'https://hxbg.cn/m/help/view.html?id=3',
                        ],
                        [
                            'type'=>'view',
                            'name'=>'联系我们',
                            'url'=>'https://hxbg.cn/m/help/view.html?id=4',
                        ],
                    ],

                ],
            ]
        ];
        $merchant = (new merchant_model());
       $info= $merchant->action_set_menu($data);
       var_dump($info);
    }


}
