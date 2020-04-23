<?php
class order_controller extends general_controller
{
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
            $order_status = request('order_status', '');
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
            $payment_method=request('payment_method','');
            if($payment_method != '')
            {
                $where .= ' AND a.payment_method = :payment_method';
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
                if ($list){
                    foreach($list as &$v)
                    {
                        $v['order_status'] = $status_map[$v['order_status']];
                        $v['created_date'] = date('Y-m-d H:i:s', $v['created_date']);
                    }
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
            $this->compiler('order/order_list.html');
        }
    }

    public function action_view()
    {
        $condition = array('order_id' => request('id', ''));
        $order_model = new order_model();
        if($order = $order_model->find($condition))
        {
            $vcache = vcache::instance();
            $payment_map = $vcache->payment_method_model('indexed_list');
            $shipping_map = $vcache->shipping_method_model('indexed_list');
            $order['payment_method_name'] = $payment_map[$order['payment_method']]['name'];
            $order['shipping_method_name'] = $shipping_map[$order['shipping_method']]['name'];

            $invoice_type_title = '';
            switch ($order['invoice_type']) {
                case 0:
                    $invoice_type_title = '不开发票';
                    break;
                case 1:
                    $invoice_type_title = '普通发票';
                    break;
                case 2:
                    $invoice_type_title = '增值税发票';
                    break;
            }
            $order['invoice_type_title'] = $invoice_type_title;

            $invoice_array = [];
            $invoice = json_decode($order['invoice_json'], true);
            if ($order['invoice_type'] == 1) {
                $invoice_array = [
                    [
                        'title' => '公司名称/姓名',
                        'text' => $invoice['name']
                    ],[
                        'title' => '税号',
                        'text' => $invoice['tax_id']
                    ]
                ];
            } else if ($order['invoice_type'] == 2) {
                $invoice_array = [
                    [
                        'title' => '公司名称',
                        'text' => $invoice['company_name']
                    ],[
                        'title' => '税号',
                        'text' => $invoice['tax_id']
                    ],[
                        'title' => '开户行',
                        'text' => $invoice['bank_name']
                    ],[
                        'title' => '开户银行账号',
                        'text' => $invoice['account_number']
                    ],[
                        'title' => '注册地址',
                        'text' => $invoice['registered_address']
                    ],[
                        'title' => '注册电话',
                        'text' => $invoice['registration_call']
                    ]
                ];
            }
            $order['invoice_array'] = $invoice_array;

            $consignee_model = new order_consignee_model();
            $this->consignee = $consignee_model->find($condition);
            $this->order = $order;
            $this->status_map = $order_model->status_map;
            //用户信息
            $user_model = new user_model();
            $this->user = $user_model->find(array('user_id' => $order['user_id']));
            //商品列表
            $order_goods_model = new order_goods_model();
            $goods_list = $order_goods_model->get_goods_list($condition['order_id']);
            if(!empty($goods_list)&&is_array($goods_list)){
                foreach($goods_list as $key => $v)
                {
                    $tion = array('order_id' => $v['order_id']);
                    $info = $order_model->find($tion);
                    $goods_list[$key]['merchant_id'] = $info['merchant_id'];
                }
            }
            $this->goods_list = $goods_list;
            //发货列表
            $this->carrier_list = $vcache->shipping_carrier_model('indexed_list');
            $shipping_model = new order_shipping_model();
            $shipped_list = $shipping_model->find_all($condition, 'dateline DESC');
            if(!empty($shipped_list))
            {
                foreach($shipped_list as &$v) $v['carrier'] = $this->carrier_list[$v['carrier_id']]['name'];
            }
            $this->shipped_list = $shipped_list;
            //日志列表
            $log_model = new order_log_model();
            if($this->log_list = $log_model->find_all($condition, 'dateline DESC'))
            {
                $this->admin_list = $vcache->admin_model('indexed_list');
                $this->operate_map = $log_model->operate_map;
            }

            $this->compiler('order/order.html');
        }
        else
        {
            $this->prompt('error', '无法找到相应的订单记录');
        }
    }

    public function action_operate()
    {
        $order_id = request('id', '');
        $condition = array('order_id' => $order_id);
        $order_model = new order_model();
        if($order = $order_model->find($condition))
        {
            $errno = 0;
            switch(request('step'))
            {
                case 'consignee':

                    if($order['order_status'] == 1 || $order['order_status'] == 2)
                    {
                        $data = array
                        (
                            'receiver' => trim(request('receiver', '')),
                            'province' => trim(request('province', '')),
                            'city' => trim(request('city', '')),
                            'borough' => trim(request('borough', '')),
                            'address' => trim(request('address', '')),
                            'zip' => trim(request('zip', '')),
                            'mobile' => trim(request('mobile', '')),
                        );
                        $consignee_model = new order_consignee_model();
                        $verifier = $consignee_model->verifier($data);
                        if(TRUE === $verifier)
                        {
                            if($consignee_model->update(array('order_id' => $order_id), $data) > 0)
                            {
                                $cause = trim(request('cause', ''));
                                $log_model = new order_log_model();
                                $log_model->record($order_id, 'consignee', $cause);
                            }
                            else
                            {
                                $errno = 1;
                            }
                        }
                        else
                        {
                             $this->prompt('error', $verifier);
                        }
                    }
                    else
                    {
                        $errno = 2;
                    }

                break;

                case 'amount': //更改金额

                    if($order['order_status'] == 1)
                    {
                        $order_amount = sprintf('%.2f', abs(request('order_amount', 0, 'post')));
                        if($order_model->update($condition, array('order_amount' => $order_amount)) > 0)
                        {
                            $cause = request('cause', '', 'post');
                            $log_model = new order_log_model();
                            $log_model->record($order_id, 'amount', $cause);
                        }
                        else
                        {
                            $errno = 1;
                        }
                    }
                    else
                    {
                        $errno = 2;
                    }

                break;

                case 'cancel': //取消交易

                    if($order['order_status'] == 1)
                    {
                        if($order_model->update($condition, array('order_status' => 0)) > 0)
                        {
                            $order_goods_model = new order_goods_model();
                            $order_goods_model->restocking($order_id);
                            $cause = trim(request('cause', ''));
                            $log_model = new order_log_model();
                            $log_model->record($order_id, 'cancel', $cause);
                        }
                        else
                        {
                            $errno = 1;
                        }
                    }
                    else
                    {
                        $errno = 2;
                    }

                break;

                case 'resume': //恢复被取消交易

                    if($order['order_status'] == 0)
                    {
                        if($order_model->update($condition, array('order_status' => 1)) > 0)
                        {
                            $order_goods_model = new order_goods_model();
                            $order_goods_model->restocking($order_id, 'decr');
                            $cause = trim(request('cause', ''));
                            $log_model = new order_log_model();
                            $log_model->record($order_id, 'resume', $cause);
                        }
                        else
                        {
                            $errno = 1;
                        }
                    }
                    else
                    {
                        $errno = 2;
                    }

                break;

                case 'shipping':

                    if($order['order_status'] >= 2 || ($order['order_status'] != 0 && $order['payment_method'] == 2))
                    {
                        $data = array
                        (
                            'order_id' => $order_id,
                            'carrier_id' => (int)request('carrier_id', ''),
                            'tracking_no' => request('tracking_no', ''),
                            'memos' => trim(request('memos', '')),
                            'dateline' => $_SERVER['REQUEST_TIME'],
                        );
                        $shipping_model = new order_shipping_model();
                        if($shipping_model->create($data) > 0)
                        {
                            $order_model->update(array('order_id' => $order_id), array('order_status' => 3));
                        }
                        else
                        {
                            $errno = 1;
                        }
                    }
                    else
                    {
                        $errno = 2;
                    }

                break;
            }

            $errormap = array
            (
                0 => '操作成功',
                1 => '操作失败',
                2 => '当前订单无法进行此操作',
            );
            $this->prompt($errno == 0 ? 'success' : 'error', $errormap[$errno], url($this->MOD.'/order', 'view', array('id' => $order_id)));
        }
        else
        {
            $this->prompt('error', '订单不存在');
        }
    }

    public function action_delete()
    {
        $id = request('id');
        $condition = array('order_id' => $id);
        $order_model = new order_model();
        if($order = $order_model->find($condition))
        {
            if($order['order_status'] == 0)
            {
                if($order_model->delete($condition) > 0)
                {
                    //删除订单商品
                    $order_goods_model = new order_goods_model();
                    $goods_list = $order_goods_model->find_all($condition);
                    $order_goods_model->delete($condition);
                    if($goods_list)
                    {
                        $order_goods_optional = new order_goods_optional_model();
                        foreach($goods_list as $v)
                        {
                            $order_goods_optional->delete(array('map_id' => $v['id']));
                        }
                    }

                    //删除订单收件人地址
                    $order_consignee_model = new order_consignee_model();
                    $order_consignee_model->delete($condition);
                    $this->prompt('success', '删除成功', url($this->MOD.'/order', 'index'));
                }
                else
                {
                    $this->prompt('error', '删除失败');
                }
            }
            else
            {
                $this->prompt('error', '该订单无法删除');
            }
        }
        else
        {
            $this->prompt('error', '订单不存在');
        }
    }


}
