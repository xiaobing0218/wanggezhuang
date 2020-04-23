<?php
class invoice_controller extends general_controller
{
    public function action_index()
    {
        $user_id = $this->is_logined();
        $user_invoice_model = new user_invoice_model();
        $invoice_info = $user_invoice_model->find(array('user_id' => $user_id));
        $this->invoice_info = $invoice_info;
        $this->compiler('user_invoice_index.html');
    }
}
