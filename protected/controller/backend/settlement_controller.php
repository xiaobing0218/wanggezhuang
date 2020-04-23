<?php
/*
Author: Administrator
CreeateTame:2019/12/18 8:44
*/
class settlement_controller extends general_controller{

    public function action_index()
    {
        if(request('step') == 'search')
        {
            $where = 'WHERE 1';
            $binds = array();

            $user_id = (int)request('user_id', 0);
            $shop_id = (int)request('shop_id', 0);
            if(!empty($user_id))
            {
                $where .= ' AND a.user_id = :user_id';
                $binds[':user_id'] = $user_id;
            }

            if (!empty($shop_id)){
                $where .=' AND merchant_id=:merchant_id';
                $binds['merchant_id']=$shop_id;
            }
            $settle_accounts=(int)request('settle_accounts');
            if (!empty($settle_accounts)){
                $where .= ' AND a.settle_accounts = :settle_accounts';
                $binds[':settle_accounts'] = $settle_accounts;
            }
            $order_status = 4;
            if($order_status != '')
            {
                $where .= ' AND a.order_status = :order_status';
                $binds[':order_status'] = $order_status;
            }

            $start_date = request('start_date', '');
            if($start_date != '')
            {
                $where .= ' AND a.created_date >= :start_date';
                $binds[':start_date'] = strtotime($start_date);
            }

            $end_date = request('end_date', '');
            if($end_date != '')
            {
                $where .= ' AND a.created_date <= :end_date';
                $binds[':end_date'] = strtotime($end_date);
            }

            $order_id = request('order_id', '');
            if($order_id != '')
            {
                $where .= ' AND a.order_id = :order_id';
                $binds[':order_id'] = $order_id;
            }
            $payment_method=2;
            if($payment_method != '')
            {
                $where .= ' AND a.payment_method <> :payment_method';
                $binds[':payment_method'] = $payment_method;
            }
            $order_model = new order_model();
            $total = $order_model->query("SELECT COUNT(*) as count FROM {$order_model->table_name} AS a {$where}", $binds);
            if($total[0]['count'] > 0)
            {
                $limit = $order_model->set_limit(array(request('page', 1), request('pernum', 10)), $total[0]['count']);

                $sql = "SELECT a.id,a.merchant_id, a.settle_accounts,a.order_id, a.order_status, a.order_amount, a.created_date,
                               b.receiver, b.province, b.city, b.borough, b.address, b.zip, b.mobile
                        FROM {$order_model->table_name} AS a
                        INNER JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}order_consignee AS b
                        ON a.order_id = b.order_id
                        {$where}
                        ORDER BY id DESC {$limit}
                       ";

                $list = $order_model->query($sql, $binds);
                if ($list){
                    foreach ($list as $k=>$v){
                        $merchant=(new merchant_model())->find(array('user_id'=>$v['merchant_id']));
                        $list[$k]['abbreviation']=$merchant['abbreviation'];

                    }

                }
                $aggsql="SELECT a.id,a.merchant_id, a.settle_accounts,a.order_id, a.order_status, a.order_amount, a.created_date,
                               b.receiver, b.province, b.city, b.borough, b.address, b.zip, b.mobile
                        FROM {$order_model->table_name} AS a
                        INNER JOIN {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}order_consignee AS b
                        ON a.order_id = b.order_id
                        {$where}
                        ORDER BY id DESC";
                $agg= $order_model->query($aggsql, $binds);
                $status_map = $order_model->status_map;
                foreach($list as &$v)
                {
                    $v['order_status'] = $status_map[$v['order_status']];
                    $v['created_date'] = date('Y-m-d H:i:s', $v['created_date']);
                }


                $aggregate=array_sum(array_map(function($val){return $val['order_amount'];}, $agg));
                $results = array
                (
                    'status' => 'success',
                    'list' => $list,
                    'paging' => $order_model->page,
                    'aggregate'=>$aggregate,
                );
            }
            else
            {
                $results = array('status' => 'nodata');
            }

            echo json_encode($results);
        }
        else
        {  $this->payment_map = (new payment_method_model())->indexed_arres();
            $this->shop=(new merchant_model())->find_all('','','abbreviation,user_id');
            $order_model = new order_model();
            $this->status_map = $order_model->status_map;
            $this->compiler('settlement/list.html');
        }
    }
    public function action_aggregate(){
        $id = request('id');
        $order_model = new order_model();
        foreach ($id as$k=>$v){
            $condition = array('order_id' => $v);
            if($order = $order_model->find($condition)) {
                $sql="update {$order_model->table_name} set settle_accounts='1' where order_id = {$v}";
                $list = $order_model->query($sql);
//                if (!$list){
//                    $this->prompt('error', '订单结算失败！');
//                }
            } else {

                $this->prompt('error', '订单不存在');
            }

        }
        $this->prompt('success', '订单结算成功', url($this->MOD.'/settlement', 'index'));
    }
}
