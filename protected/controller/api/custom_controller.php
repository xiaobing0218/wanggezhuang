<?php

class custom_controller extends general_controller
{
    public function action_list()
    {
        $user_id = $this->is_logined();
        $custom_model = new user_custom_model();
        $page_id = request('page', 1);
        if ($custom_list = $custom_model->find_all(array('user_id' => $user_id), 'created_date DESC', '*', array($page_id, 10))) {
            foreach ($custom_list as $key => $v) {
                $status_title = '';
                switch ($v['processing_status']) {
                    case 0:
                        $status_title = '待处理';
                        break;
                    case 1:
                        $status_title = '处理中';
                        break;
                    case 2:
                        $status_title = '已处理';
                        break;
                }

                $custom_list[$key]['status_title'] = $status_title;
                $custom_list[$key]['created_date'] = date('Y年m月d日 H:i:s',$v['created_date']);
            }
        }


        if ($custom_list) {
            $res = array('status' => 'success', 'list' => $custom_list, 'paging' => $custom_model->page);
        } else {
            $res = array('status' => 'nodata');
        }
        echo json_encode($res);

    }

    public function action_add_ajax()
    {
        $user_id = $this->is_logined();
        if (request('type') === 'submit') {
            $data = array
            (
                'product_list' => trim(request('content', '')),
                'full_name' => trim(request('name', '')),
                'telephone' => trim(request('tel', '')),
                'created_date' => $_SERVER['REQUEST_TIME'],
            );

            $custom_model = new user_custom_model();
            $verifier = $custom_model->verifier($data);
            if ($verifier === TRUE) {
                $data['user_id'] = $user_id;
                if ($id = $custom_model->create($data)) {
                    die(json_encode([
                        'errcode' => 0,
                        'errmsg' => '提交成功',
                        'url' => url('mobile/custom', 'list')
                    ]));
                }
                die(json_encode([
                    'errcode' => 0,
                    'errmsg' => '提交失败'
                ]));
            } else {
                $this->prompt('error', $verifier);
            }
        } else {
            die(json_encode([
                'errcode' => -1,
                'errmsg' => '提交失败'
            ]));
        }
    }


}
