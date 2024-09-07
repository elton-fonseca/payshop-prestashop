<?php

require_once('PayshopPaid.php');
require_once('PayshopWaitingPayment.php');
require_once('PayshopWaitingMultibanco.php');
require_once('PayshopWaitingPayshop.php');
require_once('PayshopPaymentError.php');

class PayshopOrderStatuses
{
    /**
     * Register order status
     *
     * @return void
     */
    public function register()
    {
        PayshopPaid::register();
        PayshopWaitingPayment::register();
        PayshopWaitingMultibanco::register();
        PayshopWaitingPayshop::register();
        PayshopPaymentError::register();
    }
}
