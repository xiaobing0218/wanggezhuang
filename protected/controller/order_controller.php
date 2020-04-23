<?php

/**
 * Class order_controller
 * 订单页面
 */
class order_controller extends general_controller
{

    public function action_confirm()
    {
        $user_id = $this->is_logined();
        $cart = json_decode(stripslashes(request('CARTS', null, 'cookie')), TRUE);

        if($cart)
        {
            $goods_model = new goods_model();
            $user_model = new user_model();
            $user_invoice_model = new user_invoice_model();
            $this->cart = $goods_model->get_cart_items($cart);
            $consignee_model = new user_consignee_model();
            $this->user_info = $user_model->find(['user_id'=>$user_id]);
            $this->consignee_list = $consignee_model->get_user_consignee_list($user_id);
            $this->shipping_method_list = vcache::instance()->shipping_method_model('indexed_list');
            $this->payment_method_list = vcache::instance()->payment_method_model('indexed_list');
            $this->invoice_info = $user_invoice_model->find(['user_id' => $user_id]);
            $this->compiler('order_confirm.html');
        }
        else
        {
            jump(url('cart', 'index'));
        }
    }
    
    public function action_submit()
    {
        $user_id = $this->is_logined();
        //检查购物车信息
        $cart = json_decode(stripslashes(request('CARTS', null, 'cookie')), TRUE);
        if(!$cart) $this->prompt('error', '无法获取购物车数据');
        $goods_model = new goods_model();
        if(!$cart = $goods_model->get_cart_items($cart)) $this->prompt('error', '购物车商品数据不正确');
        //检查收件人信息
        $csn_id = (int)request('csn_id', 0);
        $consignee_model = new user_consignee_model();
        if(!$consignee = $consignee_model->find(array('id' => $csn_id, 'user_id' => $user_id))) $this->prompt('error', '无法获取收件人地址信息');
        //检查配送方式
        $shipping_id = (int)request('shipping_id', 0);
        $shipping_map = vcache::instance()->shipping_method_model('indexed_list');
        if(!isset($shipping_map[$shipping_id])) $this->prompt('error', '配送方式不存在');
        //检查运费
        $shipping_model = new shipping_method_model();
        $shipping_amount = $shipping_model->check_freight($user_id, $shipping_id, $consignee['province'], $cart);
        if(FALSE === $shipping_amount) $this->prompt('error', '计算运费失败');
        //检查付款方式
        $payment_id = (int)request('payment_id', 0);
        $payment_map = vcache::instance()->payment_method_model('indexed_list');
        if(!isset($payment_map[$payment_id]))
        {
            $payment_id = current($payment_map);
            $payment_id = $payment_id['id'];
        }

        //检查发票信息
        $invoice_id = ((int)request('invoice_id', 1)) - 1;
        $user_invoice_model = new user_invoice_model();
        $invoice_info = $user_invoice_model->find(array('user_id' => $user_id));
        $invoice_array = [];
        if ($invoice_id == 1) {
            if (empty($invoice_info['name'])) {
                $this->prompt('error', '未填写普通发票信息');
            }

            $invoice_array = [
                'name' => $invoice_info['name'],
                'tax_id' => $invoice_info['tax_id']
            ];
        } else if ($invoice_id == 2) {
            if (empty($invoice_info['company_name'])) {
                $this->prompt('error', '未填写增值税发票信息');
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
                'payment_method' => $payment_id,
                'invoice_type' => $invoice_type,
                'invoice_json' => $invoice_json,
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
        jump(url('pay', 'index', array('order_id' => $order)));

    }
    
    public function action_list()
    {
        $user_id = $this->is_logined();
        $order_model = new order_model();
        $merchant_model=new merchant_model();
        $page_id = request('page', 1);
        $payment_map = vcache::instance()->payment_method_model('indexed_list');
        if($order_list = $order_model->find_all(array('user_id' => $user_id), 'created_date DESC', '*', array($page_id, 10)))
        {
            $order_goods_model = new order_goods_model();
            foreach($order_list as &$v) $v['goods_list'] = $order_goods_model->get_goods_list($v['order_id']);
            foreach($order_list as &$v) $v['merchant_id'] = $merchant_model->find(array('user_id'=>$v['merchant_id']));

            foreach ($order_list as $key => $v) {
                $order_list[$key]['payment_method_name'] = empty($payment_map[$v['payment_method']]) ? '' : $payment_map[$v['payment_method']]['name'];
            }
        }

        $order_list = array('rows' => $order_list, 'paging' => $order_model->page);

        $this->order_list=$order_list;
        $this->compiler('user_order_list.html');
    }
    
    public function action_view()
    {
        $user_id = $this->is_logined();
        $order_id = bigintstr(request('id'));
        $order_model = new order_model();
        if($order = $order_model->find(array('order_id' => $order_id, 'user_id' => $user_id)))
        {
            $vcache = vcache::instance();
            $payment_map = $vcache->payment_method_model('indexed_list');
            $shipping_map = $vcache->shipping_method_model('indexed_list');
            $order['payment_method_name'] = empty($payment_map[$order['payment_method']]) ? '' : $payment_map[$order['payment_method']]['name'];
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
            
            $condition = array('order_id' => $order_id);
            $consignee_model = new order_consignee_model();
            $this->consignee = $consignee_model->find($condition);
            
            $order_goods_model = new order_goods_model();
            $this->goods_list = $order_goods_model->get_goods_list($order_id);
            
            $this->progress = $order_model->get_user_order_progress($order['order_status'], $order['payment_method']);
            $this->status_map = $order_model->status_map;
            
            if($order['order_status'] == 1 && $order['payment_method'] != 2)
            {
                if(!$this->countdown = $order_model->is_overdue($order_id, $order['created_date'])) $order['order_status'] = 0;
            }
            elseif($order['order_status'] == 3)
            {
                $shipping_model = new order_shipping_model();
                if($shipping = $shipping_model->find($condition, 'dateline DESC'))
                {
                    $this->countdown = intval($shipping['dateline'] + $GLOBALS['cfg']['order_delivery_expires'] * 86400 - $_SERVER['REQUEST_TIME']);
                    if(!$this->countdown) $order_model->update($condition, array('order_status' => 4));
                    $this->shipping = $shipping;
                    $carrier_map = $vcache->shipping_carrier_model('indexed_list');
                    $this->carrier = $carrier_map[$shipping['carrier_id']];
                }
            }
                $order['merchant_id']=(new merchant_model())->find(array('user_id'=>$order['merchant_id']));
            $this->order = $order;

            $this->compiler('user_order_details.html');
        }
        else
        {
            jump(url('main', '404'));
        }
    }
    
    public function action_cancel()
    {
        $user_id = $this->is_logined();
        $order_id =  bigintstr(request('id'));

        $order_model = new order_model();
        if($order = $order_model->find(array('order_id' => $order_id, 'user_id' => $user_id)))
        {
            if($order['order_status'] == 1)
            {
                $order_model->update(array('order_id' => $order_id), array('order_status' => 0));
                $order_goods_model = new order_goods_model();
                $order_goods_model->restocking($order_id);
                $this->prompt('success', '取消订单成功', url('order', 'view', array('id' => $order_id)));
            }
            else
            {
                $this->prompt('error', '参数非法');
            }
        }
        else
        {
            jump(url('main', '404'));
        }
    }
    
    public function action_delivered()
    {
        $user_id = $this->is_logined();
        $order_id = bigintstr(request('id'));
        $order_model = new order_model();
        if($order = $order_model->find(array('order_id' => $order_id, 'user_id' => $user_id)))
        {
            if($order['order_status'] == 3)
            {
                $order_model->update(array('order_id' => $order_id), array('order_status' => 4));
                $this->prompt('success', '签收成功，感谢您的购买！如有任何售后问题请及时与客服联系', url('order', 'view', array('id' => $order_id)), 5);
            }
            else
            {
                $this->prompt('error', '参数非法');
            }
        }
        
        jump(url('main', '404'));
    }
    
    public function action_rebuy()
    {
        $user_id = $this->is_logined();
        $order_id = bigintstr(request('id'));
        $order_model = new order_model();
        if($order_model->find(array('order_id' => $order_id, 'user_id' => $user_id)))
        {
            if($cart = request('CARTS', array(), 'cookie')) $cart = json_decode($cart, TRUE);
            
            $order_goods_model = new order_goods_model();
            $opts_model = new order_goods_optional_model();
            $goods_list = $order_goods_model->find_all(array('order_id' => $order_id), null, 'id, goods_id, goods_qty');
            foreach($goods_list as $v)
            {
                $key = $v['goods_id'];
                $opt_ids = null;
                if($opts = $opts_model->find_all(array('map_id' => $v['id'])))
                {
                    $opts = array_column($opts, 'opt_id');
                    $key .= implode('_', $opts);
                }
                $cart[$key] = array('id' => $v['goods_id'], 'qty' => $v['goods_qty'], 'opts' => $opts);
            }
            setcookie('CARTS', json_encode($cart), $_SERVER['REQUEST_TIME'] + 604800, '/');
            jump(url('cart', 'index'));
        }
        
        jump(url('main', '404'));
    }
}
