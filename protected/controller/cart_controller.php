<?php
class cart_controller extends general_controller
{
    /**
     * 购物车页面
     */
    public function action_index()
    {
        $this->compiler('cart.html');
    }
}