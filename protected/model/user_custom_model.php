<?php
class user_custom_model extends Model
{
    public $table_name = 'custom';
    public $rules = array
    (
        'product_list' => array
        (
            'is_required' => array(TRUE, '商品清单不能为空'),
        ),
        'full_name' => array
        (
            'is_required' => array(TRUE, '姓名不能为空'),
        ),
        'telephone' => array
        (
            'is_required' => array(TRUE, '手机号不能为空'),
            'is_moblie_no' => array(TRUE, '无效的手机号码'),
        ),
    );
}
