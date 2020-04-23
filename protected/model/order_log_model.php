<?php
class order_log_model extends Model
{
    public $table_name = 'order_log';
    
    public $operate_map = array
    (   
        'consignee' => '更改了收件人信息',
        'amount' => '更改了订单金额',
        'cancel' => '取消了该笔订单交易',
        'resume' => '恢复了该笔订单交易',
    );
    /**
     * 记录订单日志
     */
    public function record($order_id, $operate, $cause, $merchant_id = 0)
    {
        if (empty($_SESSION['ADMIN']['USER_ID'])){
            $admin_id=$_SESSION['USERADMIN']['USER_ID'];
        }else{
            $admin_id=$_SESSION['ADMIN']['USER_ID'];
        }
        $data = array
        (
            'merchant_id' => $merchant_id,
            'order_id' => $order_id,
            'admin_id' => $admin_id,
            'operate' => $operate,
            'cause' => $cause,
            'dateline' => $_SERVER['REQUEST_TIME'],
        );
       return $data;
//           $this->create($data);
    }
}