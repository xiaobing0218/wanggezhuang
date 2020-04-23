<?php
class user_invoice_model extends Model
{
    public $table_name = 'user_invoice';
    public $rules = array
    (
        'name' => array
        (
            'is_required' => array(TRUE, '公司名称或者姓名不能为空'),
        )
    );

    public $rules_1 = array
    (
        'company_name' => array
        (
            'is_required' => array(TRUE, '公司名称不能为空'),
        ),'vat_tax_id' => array
        (
            'is_required' => array(TRUE, '税号不能为空'),
        ),'bank_name' => array
        (
            'is_required' => array(TRUE, '开户行不能为空'),
        ),'account_number' => array
        (
            'is_required' => array(TRUE, '银行账户不能为空'),
        ),'registered_address' => array
        (
            'is_required' => array(TRUE, '注册地址不能为空'),
        ),'registration_call' => array
        (
            'is_required' => array(TRUE, '注册电话不能为空'),
        ),
    );
}
