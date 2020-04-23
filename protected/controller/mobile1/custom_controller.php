<?php
class custom_controller extends general_controller
{
    public function action_list()
    {
        $user_id = $this->is_logined();
        $this->compiler('user_custom_list.html');
    }

    public function action_add()
    {
        $this->compiler('user_custom_add.html');
    }
}
