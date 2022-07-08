<?php

require_once('Paid.php');
require_once('Refunded.php');
require_once('WaitingPayment.php');
require_once('PaymentError.php');

class OrderStatus
{
    public function register()
    {
        Paid::register();
        WaitingPayment::register();
        PaymentError::register();
        Refunded::register();
    }
}
