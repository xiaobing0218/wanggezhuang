<?php

class invoice_controller extends general_controller
{
    public function action_add_ajax()
    {
        $user_id = $this->is_logined();
        $user_invoice_model = new user_invoice_model();
        $invoice_info = $user_invoice_model->find(array('user_id' => $user_id));
        if (request('type') === 'submit') {
            $cate = request('cate', '');
            $data = [];
            $verifier = FALSE;
            if ($cate == 1) {
                $data = array
                (
                    'name' => trim(request('name', ''))
                );

                $verifier = $user_invoice_model->verifier($data);
                $data['type'] = $cate;
                $data['user_id'] = $user_id;
                $data['tax_id'] = request('tax_id', '');
            } else if ($cate == 2) {
                $data = array
                (
                    'company_name' => trim(request('company_name', '')),
                    'vat_tax_id' => trim(request('vat_tax_id', '')),
                    'bank_name' => trim(request('bank_name', '')),
                    'account_number' => trim(request('account_number', '')),
                    'registered_address' => trim(request('registered_address', '')),
                    'registration_call' => trim(request('registration_call', ''))
                );

                $user_invoice_model->rules = $user_invoice_model->rules_1;
                $verifier = $user_invoice_model->verifier($data);
                $data['type'] = $cate;
                $data['user_id'] = $user_id;
            }


            if ($verifier === TRUE) {
                if (empty($invoice_info)) {
                    if ($id = $user_invoice_model->create($data)) {
                        die(json_encode([
                            'errcode' => 0,
                            'errmsg' => '提交成功',
                            'url' => url('mobile/invoice', 'index')
                        ]));
                    }
                } else {
                    $user_invoice_model->update(array('id' => $invoice_info['id']), $data);
                    die(json_encode([
                        'errcode' => 0,
                        'errmsg' => '提交成功',
                        'url' => url('mobile/invoice', 'index')
                    ]));
                }
                die(json_encode([
                    'errcode' => -1,
                    'errmsg' => '提交失败'
                ]));
            } else {
                die(json_encode([
                    'errcode' => -1,
                    'errmsg' => $verifier
                ]));
            }
        } else {
            die(json_encode([
                'errcode' => -1,
                'errmsg' => '提交失败'
            ]));
        }
    }


}
