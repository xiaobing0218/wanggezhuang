<?php
class stats_controller extends general_controller
{
    public function action_order()
    {
        $admin_info = $this->get_admin_info();
        $merchant_id = $admin_info['user_id'];
        if(request('step') == 'search')
        {
            $start_year = request('start_year', '');
            if(empty($start_year)) $start_year = date('Y'); 
            $start_timestamp = strtotime($start_year.'0101');
            $next_timestamp = strtotime($start_year + 1 .'0101');
            
            $order_model = new order_model();
            $sql = "SELECT COUNT(*) AS num, FROM_UNIXTIME(created_date, '%m') AS month 
                    FROM {$order_model->table_name}
                    WHERE merchant_id = $merchant_id and created_date >= {$start_timestamp} && created_date < {$next_timestamp}
                    GROUP BY month
                   ";
            if($stats_data = $order_model->query($sql))
            {
                $results = array('status' => 'success', 'data' => $stats_data);
            }
            else
            {
                $results = array('status' => 'nodata');
            }
            echo json_encode($results);
        }
        else
        {
            include(VIEW_DIR.DS.'function'.DS.'html_date_options.php');
            $today_stamp = strtotime('today');
            $yesterday_stamp = strtotime('yesterday');
            $this_month_stamp = strtotime(date('Ym').'01');
            $order_model = new order_model();
            $sql = "SELECT COUNT(*) AS total, 
                    SUM(CASE WHEN order_status = 2 then 1 else 0 end) AS paid,
                    SUM(CASE WHEN order_status = 1 then 1 else 0 end) AS nonpay,
                    SUM(CASE WHEN order_status = 0 then 1 else 0 end) AS canceled
                    FROM {$order_model->table_name}
                    WHERE merchant_id = $merchant_id and
                   ";
            $today = $order_model->query($sql." created_date >= {$today_stamp}");
            $yesterday = $order_model->query($sql." created_date >= {$yesterday_stamp} && created_date < {$today_stamp}");
            $this_month = $order_model->query($sql." created_date >= {$this_month_stamp} && created_date < {$today_stamp}");
            $this->latest = array
            (
                '今日' => $today[0],
                '昨日' => $yesterday[0],
                '本月' => $this_month[0],
            );
            $this->def_year = date('Y');
            $this->compiler('operation/stats_order.html');
        }
    }
    
    public function action_revenue()
    {
        $admin_info = $this->get_admin_info();
        $merchant_id = $admin_info['user_id'];
        if(request('step') == 'search')
        {
            $start_year = request('start_year', '');
            if(empty($start_year)) $start_year = date('Y'); 
            $start_timestamp = strtotime($start_year.'0101');
            $next_timestamp = strtotime($start_year + 1 .'0101');
            
            $order_model = new order_model();
            $sql = "SELECT SUM(order_amount) AS revenue, FROM_UNIXTIME(created_date, '%m') AS month 
                    FROM {$order_model->table_name}
                    WHERE merchant_id = $merchant_id and order_status >= 2 AND created_date >= {$start_timestamp} AND created_date < {$next_timestamp}
                    GROUP BY month
                   ";
            if($stats_data = $order_model->query($sql))
            {
                $results = array('status' => 'success', 'data' => $stats_data);
            }
            else
            {
                $results = array('status' => 'nodata');
            }
            echo json_encode($results);
        }
        else
        {
            include(VIEW_DIR.DS.'function'.DS.'html_date_options.php');
            $today_stamp = strtotime('today');
            $yesterday_stamp = strtotime('yesterday');
            $this_month_stamp = strtotime(date('Ym').'01');
            $order_model = new order_model();
            $sql = "SELECT SUM(order_amount) AS revenue
                    FROM {$order_model->table_name}
                    WHERE merchant_id = $merchant_id and order_status >= 2
                   ";
            $today = $order_model->query($sql." AND created_date >= {$today_stamp}");
            $yesterday = $order_model->query($sql." AND created_date >= {$yesterday_stamp} AND created_date < {$today_stamp}");
            $this_month = $order_model->query($sql." AND created_date >= {$this_month_stamp} AND created_date < {$today_stamp}");
            $this->latest = array
            (
                '今日' => $today[0],
                '昨日' => $yesterday[0],
                '本月' => $this_month[0],
            );
            $this->def_year = date('Y');
            $this->compiler('operation/stats_revenue.html');
        }
    }
    
}
