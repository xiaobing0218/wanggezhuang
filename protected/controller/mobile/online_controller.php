<?php
class online_controller extends general_controller
{
    public function action_index()
    {
        $user_id = $this->is_logined();
        $this->compiler('user_online_index.html');
    }
}
