<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

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
        $payshopOrderStatus = Configuration::get('PAYSHOP_ORDER_STATUS_WAITING_PAYSHOP');
        $this->setStatusName('Payshop', $payshopOrderStatus);

        $multibancoOrderStatus = Configuration::get('PAYSHOP_ORDER_STATUS_WAITING_MULTIBANCO');
        $this->setStatusName('Multibanco', $multibancoOrderStatus);

        PayshopPaid::register();
        PayshopWaitingPayment::register();
        PayshopWaitingMultibanco::register();
        PayshopWaitingPayshop::register();
        PayshopPaymentError::register();
    }

    /**
     * Set order state name in the corresponding language
     * 
     * @param string $paymentName
     * @param int $orderStatusId
     * @return void
     */
    private function setStatusName($paymentName, $orderStatusId = null)
    {
        if ($orderStatusId) {
            foreach (Language::getLanguages() as $language) {
                $description = 'Waiting payment ' . $paymentName;

                if (Tools::strtolower($language['iso_code']) == 'pt') {
                    $description = 'Aguardando pagamento ' . $paymentName;
                }
                
                $sql = "UPDATE " . _DB_PREFIX_ ."order_state_lang SET name = '{$description}' WHERE id_order_state = {$orderStatusId} and id_lang = {$language['id_lang']}";

                DB::getInstance()->execute($sql);
            }
        }
    }
}