<?php

/**
 * 2007-2022 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2022 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *
 * Don't forget to prefix your containers with your own identifier
 * to avoid any conflicts with others containers.
 */

 class PayshopCreateOrder
 {
    /**
     * @var PayshopCreatePaymentOrder
     */
    private $payshopCreatePaymentOrder;

    /**
     * @var PayshopCreatePrestashopOrder
     */
    private $payshopCreatePrestashopOrder;    

    /**
     * @var PayshopUpdateOrder
     */
    private $payshopUpdateOrder;    

    /**
     * Class constructor
     *
     * @param Module $module
     */
    public function __construct($module)
    {
        $this->payshopCreatePaymentOrder = new PayshopCreatePaymentOrder($module);
        $this->payshopCreatePrestashopOrder = new PayshopCreatePrestashopOrder($module);
        $this->payshopUpdateOrder = new PayshopUpdateOrder($module);
    }

    /**
     * Send instrument to payshop
     *
     * @param string $paymentMethod
     * @return int
     * @throws Exception
     */
    public function execute($paymentMethod)
    {
        $prestashopOrderId = $this->payshopCreatePrestashopOrder->execute($paymentMethod);

        $paymentOrder = $this->payshopCreatePaymentOrder->execute(
            $paymentMethod, $prestashopOrderId
        );

        $this->payshopUpdateOrder->execute(
            $paymentMethod,
            $prestashopOrderId,
            'PAYSHOP_ORDER_STATUS_WAITING_PAYMENT',
            $paymentOrder['uuid']
        );

        return $paymentOrder;
    }
}