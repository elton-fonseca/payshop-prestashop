<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once 'PayshopPaid.php';
require_once 'PayshopWaitingPayment.php';
require_once 'PayshopWaitingMultibanco.php';
require_once 'PayshopWaitingPayshop.php';
require_once 'PayshopPaymentError.php';

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
     *
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

                $sql = 'UPDATE ' . _DB_PREFIX_ . "order_state_lang SET name = '{$description}' WHERE id_order_state = {$orderStatusId} and id_lang = {$language['id_lang']}";

                Db::getInstance()->execute($sql);
            }
        }
    }
}
