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

require_once('PaymentSuccess.php');
require_once('MBWayDeclined.php');

 class ProcessEvent
 {
    /**
     * @var Modulo
     */
    private $module;

    /**
     * @var PayshopSuccess
     */
    private $paymentSuccess;

    /**
     * @var MBWayDeclined
     */
    private $mbWayDeclined;

    /**
     * Class constructor
     *
     * @param Module $module
     */
    public function __construct($module)
    {
        $this->module = $module;
        $this->paymentSuccess = new PaymentSuccess($module);
        $this->mbWayDeclined = new MBWayDeclined($module);
    }

    /**
     * Process webhook events
     *
     * @return void
     */
    public function execute($eventBasicInformation)
    {
        $eventReponse = $this->getApiEvent($eventBasicInformation['event']);

        $event = $eventReponse['response'];

        if ($event['type'] == 'payment.success') {
            $this->paymentSuccess->process($event);
        }

        if ($this->isWBWay($event) || $this->isDeclined($event)) {
            $this->mbWayDeclined->process($event);
        }
    }

    /**
     * Get the event from the API
     *
     * @param string $eventId
     * @return array
     */
    private function getApiEvent($eventId)
    {
        $payshopSDK = PayshopClientFactory::getInstance();
        $eventReponse = $payshopSDK->getEvent($eventId);

        $this->checkEventReponse($eventReponse);

        return $eventReponse;
    }

    /**
     * Check the event response
     *
     * @param array $response
     * @return void
     * @throws Exception
     */
    private function checkEventReponse($response)
    {
        if ($response['status'] != '200') {
            throw new Exception("Get event on payshop api error");
        }
    }

    /**
     * Check if event is  WBWay
     *
     * @param array $event
     * @return bool
     */
    private function isWBWay($event)
    {
        return $event['charge']['charge_type'] == "mbway";
    }

    /**
     * Check if event is  declined
     *
     * @param array $event
     * @return bool
     */
    public function isDeclined($event)
    {
        return $event['instrument']['failure_code'] == "03.00.0000";
    }
 }