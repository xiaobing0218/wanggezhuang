<?php
class invoice_controller extends general_controller
{
    public function action_index()
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
            }else if($cate == 2){
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
                        $this->prompt('success', '修改成功', url('invoice', 'index'));
                        return;
                    }
                } else {
                    $user_invoice_model->update(array('id' => $invoice_info['id']), $data);
                    $this->prompt('success', '修改成功', url('invoice', 'index'));
                    return;
                }
                $this->prompt('error', '提交失败');
            } else {
                $this->prompt('error', $verifier);
            }
        }
        $this->invoice_info = $invoice_info;
        $this->compiler('user_invoice.html');
    }

    public function action_get_invoice_ajax(){
        $invoice_id = trim(request('invoice_id'));
        if(empty($invoice_id)){
            die(json_encode([
                'errcode' => '-1',
                'errmsg' => '网络原因，发票信息验证失败！'
            ]));
        }

        $user_id = $this->is_logined();
        $user_invoice_model = new user_invoice_model();
        $invoice_info = $user_invoice_model->find(array('user_id' => $user_id));
        if($invoice_id == 1){
            if(empty($invoice_info['name'])){
                die(json_encode([
                    'errcode' => '-1',
                    'errmsg' => '请填写普通发票信息！'
                ]));
            }
        }else if($invoice_id == 2){
            if(empty($invoice_info['company_name'])){
                die(json_encode([
                    'errcode' => '-1',
                    'errmsg' => '请填写增值税发票信息！'
                ]));
            }
        }
    }
}
