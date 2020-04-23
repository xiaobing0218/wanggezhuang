<?php
return array
(
    '常用菜单' => array
    (
        '面板首页' => 'c=main&a=dashboard',

        '商品管理' => array
        (
            '商品列表' => 'c=goods&a=index',
            '选项类型' => 'c=goods_optional_type&a=index',
            '商品评价' => 'c=goods_review&a=index'
        ),

        '订单管理' => array
        (
            '订单列表' => 'c=order&a=index',
            '发货列表' => 'c=order_shipping&a=index',
            '订单日志' => 'c=order_log&a=index',
        ),

        '运营管理' => array
        (
            '售后服务' => 'c=aftersales&a=index',
            '订单统计' => 'c=stats&a=order',
            '营收统计' => 'c=stats&a=revenue'
        ),

        '信息管理' => array
        (
            '企业信息' => 'c=message&a=index',
            //'绑定微信' => 'c=establish&a=index',
            '修改密码' => 'c=pass&a=index',
        ),
    )
);
