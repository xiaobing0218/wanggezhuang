<?php
class order_controller extends general_controller
{
    public function action_freight()
    {
        $user_id = $this->is_logined();
        //购物车信息
        $cart = json_decode(stripslashes(request('CARTS', null, 'cookie')), TRUE);
        if(!$cart) die(json_encode(array('status' => 'error', 'msg' => '无法获取购物车数据')));
        $goods_model = new goods_model();
        if(!$cart = $goods_model->get_cart_items($cart)) die(json_encode(array('status' => 'error', 'msg' => '购物车商品数据不正确')));
        //收件人信息
        $csn_id = (int)request('csn_id', 0);
        $consignee_model = new user_consignee_model();
        if(!$consignee = $consignee_model->find(array('id' => $csn_id, 'user_id' => $user_id)))
        {
            die(json_encode(array('status' => 'error', 'msg' => '收件人地址不存在')));
        }
        //计算运费
        $shipping_id = (int)request('shipping_id', 0);
        $shipping_model = new shipping_method_model();
        $amount = $shipping_model->check_freight($user_id, $shipping_id, $consignee['province'], $cart);
        if(FALSE === $amount) die(json_encode(array('status' => 'error', 'msg' => '计算运费失败')));

        echo json_encode(array('status' => 'success', 'amount' => sprintf('%.2f', $amount)));
    }

    public function action_submit()
    {
        $user_id = $this->is_logined();
        //检查购物车信息
        $cart = json_decode(stripslashes(request('CARTS', null, 'cookie')), TRUE);
        if(!$cart) die(json_encode(array('status' => 'error', 'msg' => '无法获取购物车数据')));
        $goods_model = new goods_model();
        if(!$cart = $goods_model->get_cart_items($cart)) die(json_encode(array('status' => 'error', 'msg' => '购物车商品数据不正确')));
        //检查收件人信息
        $csn_id = (int)request('csn_id', 0);
        $consignee_model = new user_consignee_model();
        if(!$consignee = $consignee_model->find(array('id' => $csn_id, 'user_id' => $user_id))) die(json_encode(array('status' => 'error', 'msg' => '无法获取收件人地址信息')));
        //检查配送方式
        $shipping_id = (int)request('shipping_id', 0);
        $shipping_map = vcache::instance()->shipping_method_model('indexed_list');
        if(!isset($shipping_map[$shipping_id])) die(json_encode(array('status' => 'error', 'msg' => '配送方式不存在')));
        //检查运费
        $shipping_model = new shipping_method_model();
        $shipping_amount = $shipping_model->check_freight($user_id, $shipping_id, $consignee['province'], $cart);
        if(FALSE === $shipping_amount) die(json_encode(array('status' => 'error', 'msg' => '无法获取运费')));
        //检查付款方式
        $payment_id = (int)request('payment_id', 0);
        $payment_map = vcache::instance()->payment_method_model('indexed_list');
        if(!isset($payment_map[$payment_id]))
        {
            $payment_id = current($payment_map);
            $payment_id = $payment_id['id'];
        }

        //检查发票信息
        $invoice_id = ((int)request('invoice_id', 1));
        $user_invoice_model = new user_invoice_model();
        $invoice_info = $user_invoice_model->find(array('user_id' => $user_id));
        $invoice_array = [];
        if ($invoice_id == 1) {
            if (empty($invoice_info['name'])) {
                die(json_encode([
                    'status' => 'error',
                    'msg' => '未填写普通发票信息'
                ]));
            }

            $invoice_array = [
                'name' => $invoice_info['name'],
                'tax_id' => $invoice_info['tax_id']
            ];
        } else if ($invoice_id == 2) {
            if (empty($invoice_info['company_name'])) {
                die(json_encode([
                    'status' => 'error',
                    'msg' => '未填写增值税发票信息'
                ]));
            }

            $invoice_array = [
                'company_name' => $invoice_info['company_name'],
                'tax_id' => $invoice_info['vat_tax_id'],
                'bank_name' => $invoice_info['bank_name'],
                'account_number' => $invoice_info['account_number'],
                'registered_address' => $invoice_info['registered_address'],
                'registration_call' => $invoice_info['registration_call']
            ];
        }

        $invoice_type = $invoice_id;
        $invoice_json = json_encode($invoice_array, JSON_UNESCAPED_UNICODE);

        //创建订单
        $order_model = new order_model();
        $order=array();

        foreach ($cart['list'] as $k=>$v){
            $data = array
            (
                'order_id' => $order_model->create_order_id(),
                'merchant_id'=>$v['user_id'],
                'user_id' => $user_id,
                'shipping_method' => $shipping_id,
                'invoice_type' => $invoice_type,
                'invoice_json' => $invoice_json,
                'payment_method' => $payment_id,
                'goods_amount' => $v['amount'],
                'shipping_amount' => $shipping_amount,
                'order_amount' => $v['amount'] + $shipping_amount,
                'memos' => trim(strip_tags(request('memos', ''))),
                'created_date' => $_SERVER['REQUEST_TIME'],
                'order_status' => 1,
            );
            $order[$k]=$data['order_id'];
            $order_model->create($data);
            $order_goods_model = new order_goods_model();
            $order_goods_model->add_records($data['order_id'], $v['commodity']);
            $order_consignee_model = new order_consignee_model();
            $order_consignee_model->add_records($data['order_id'], $consignee);
            setcookie('CARTS', null, $_SERVER['REQUEST_TIME'] - 3600, '/');
        }
        $order=implode(",", $order);

        if($order)
        {
            $res = array('status' => 'success', 'order_id' => (string)$order);
        }
        else
        {
            $res = array('status' => 'error', 'msg' => '创建订单失败, 请稍后重试');
        }
        echo json_encode($res);
    }

    public function action_payment()
    {
        $user_id = $this->is_logined();
        $order_id = bigintstr(request('order_id'));
        $order_model = new order_model();
        if($order = $order_model->find(array('order_id' => $order_id, 'order_status' => 1)))
        {
            $payment_id = (int)request('payment_id');
            $payment_map = vcache::instance()->payment_method_model('indexed_list');
            if(isset($payment_map[$payment_id]))
            {
                $order_model->update(array('order_id' => $order_id), array('payment_method' => $payment_id));
                $order['payment_method'] = $payment_id;
                $plugin = plugin::instance('payment', $payment_map[$payment_id]['pcode'], array($payment_map[$payment_id]['params']));
                echo $plugin->create_pay_url($order);
            }
        }
    }

    public function action_pending()
    {
        $user_id = $this->is_logined();
        $sql = "SELECT COUNT(1) AS count FROM {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}order WHERE user_id = {$user_id}";
        switch(request('pending'))
        {
            case 'pay': $sql .= " AND order_status = 1 AND payment_method <> 2"; break;

            case 'ship': $sql .= " AND (order_status = 2 OR (order_status = 1 AND payment_method = 2))"; break;

            case 'sign': $sql .= " AND order_status = 3"; break;

            case 'review': $sql .= " AND order_status = 4 AND order_id in (SELECT order_id FROM {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}order_goods WHERE is_reviewed = 0)"; break;
        }
        $order_model = new order_model();
        $res = $order_model->query($sql);
        echo json_encode(array('status' => 'success', 'count' => $res[0]['count']));
    }

    public function action_list()
    {
        $user_id = $this->is_logined();
        $sql = "SELECT COUNT(1) AS count FROM {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}order";
        $where = "WHERE user_id = {$user_id}";
        switch(request('pending'))
        {
            case 'pay': $where .= " AND order_status = 1 AND payment_method <> 2"; break;

            case 'ship': $where .= " AND (order_status = 2 OR (order_status = 1 AND payment_method = 2))"; break;

            case 'sign': $where .= " AND order_status = 3"; break;

            case 'review': $where .= " AND order_status = 4 AND order_id in (SELECT order_id FROM {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}order_goods WHERE is_reviewed = 0)"; break;
        }

        $res = array('status' => 'nodata');
        $order_model = new order_model();
        $total = $order_model->query("{$sql} {$where}");
        if($total[0]['count'] > 0)
        {
            $limit = $order_model->set_limit(array(request('page', 1), request('pernum', 10)), $total[0]['count']);
            $sql = "SELECT * FROM {$GLOBALS['mysql']['MYSQL_DB_TABLE_PRE']}order {$where} ORDER BY id DESC {$limit}";
            if($list = $order_model->query($sql))
            {
                $order_goods_model = new order_goods_model();
                foreach($list as &$v)
                {
                    $progress = $order_model->get_user_order_progress($v['order_status'], $v['payment_method']);
                    $v['progress'] = array_pop($progress);
                    $v['goods_list'] = $order_goods_model->get_goods_list($v['order_id']);
                    $v['created_date'] = date('Y-m-d H:i:s', $v['created_date']);
                }
                foreach ($list as $k=>$v){
                    $merchant_id=(new merchant_model())->find(array('user_id'=>$v['merchant_id']),'','user_id,abbreviation,logo');
                    $list[$k]['abbreviation']=$merchant_id['abbreviation'];
                    $list[$k]['logo']=$merchant_id['logo'];
                }
                $res = array('status' => 'success', 'list' => $list, 'paging' => $order_model->page);
            }
        }
        echo json_encode($res);
    }
}
