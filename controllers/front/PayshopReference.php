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

class PayshopPayshopReferenceModuleFrontController extends ModuleFrontController
{
    /**
     * @var CreateOrder
     */
    private $createOrder;

    /**
     * @var SendOrderToPayshop
     */
    private $sendOrderToPayshop;

    /**
     * @var UpdateOrder
     */
    private $updateOrder;

    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->ajax = true;
        $this->createOrder = new CreateOrder($this->module);
        $this->sendOrderToPayshop = new SendOrderToPayshop($this->module);
        $this->updateOrder = new UpdateOrder($this->module);
    }

    /**
     * Payment process with credit card
     *
     * @return void
     */
    public function postProcess()
    {
        header('Content-Type: application/json');

        try {
            $paymentMethod = 'payshop_reference';

            $instrumentResponse = $this->sendOrderToPayshop->execute(
                $paymentMethod,
                $this->getReferenceExpiration()
            );

            $prestashopOrderId = $this->createOrder->execute($paymentMethod);

            $this->updateOrder->execute(
                $paymentMethod,
                $prestashopOrderId,
                'PAYSHOP_ORDER_STATUS_WAITING_PAYMENT',
                $instrumentResponse['charge']['id'],
                $instrumentResponse['id']
            );

            echo $this->processReferenceInformation($instrumentResponse);
        } catch (\Exception $e) {
            echo json_encode(['error' => true, 'message' => $e->getMessage()]);
            
            http_response_code(400);
        }
    }

    /**
     * Get reference expiration by configuration
     *
     * @return array
     */
    private function getReferenceExpiration()
    {
        return [
            'end_date' => "2022-07-26"
        ];
    }

    private function processReferenceInformation($instrumentResponse)
    {   
        $referenceData = [];

        foreach ($instrumentResponse['reference']['fields'] as $key => $item) {
            if ($item['field'] == 'end_date') {
                $date = new DateTime($item['value']);
                $referenceData[$item['field']] = $date->format('d/m/Y');
                continue;
            }

            $referenceData[$item['field']] = $item['value'];
        }

        return json_encode($referenceData);
    }

}
