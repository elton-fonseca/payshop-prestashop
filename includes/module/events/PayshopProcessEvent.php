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

require_once 'PayshopPaymentSuccess.php';
require_once 'PayshopPaymentRefused.php';

class PayshopProcessEvent
{
    /**
     * @var PayshopClient
     */
    private $payshopSDK;

    /**
     * @var PayshopPaymentSuccess
     */
    private $payshopPaymentSuccess;

    /**
     * @var PayshopPaymentRefused
     */
    private $payshopPaymentRefused;

    /**
     * Class constructor
     *
     * @param Module $module
     */
    public function __construct($module)
    {
        $this->payshopSDK = PayshopClientFactory::getInstance();
        $this->payshopPaymentSuccess = new PayshopPaymentSuccess($module);
        $this->payshopPaymentRefused = new PayshopPaymentRefused($module);
    }

    /**
     * Process webhook events
     *
     * @return mixed
     */
    public function execute($paymentOrder)
    {
        $this->addMetadataAppName($paymentOrder);

        if ($paymentOrder['status'] == 'SUCCESS' && $paymentOrder['paid'] == true) {
            return $this->payshopPaymentSuccess->process($paymentOrder);
        }

        return $this->payshopPaymentRefused->process($paymentOrder);
    }

    /**
     * Add metadata app name to instruction
     *
     * @param array $paymentOrder
     *
     * @return void
     */
    private function addMetadataAppName(array $paymentOrder): void
    {
        $transactions = $paymentOrder['transactions'] ?? [];

        if (!$transactions) {
            return;
        }

        $transaction = $transactions[count($transactions) - 1];

        if (!isset($transaction['uuid'])) {
            return;
        }

        $this->payshopSDK->createTransationMetadata(
            $transaction['uuid'],
            [
                'metadata' => [
                    'source_application' => 'prestaShop_plugin',
                ],
            ]
        );
    }
}
