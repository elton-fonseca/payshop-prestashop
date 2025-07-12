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

class PayshopProcessFailedRedirectModuleFrontController extends ModuleFrontController
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->ajax = true;
    }

    /**
     * When a payment is failed, the customer is redirected to this page.
     * Then create a new cart, delete the old order and redirect to the payment page
     *
     * @return void
     */
    public function postProcess()
    {
        try {
            $orderId = Tools::getValue('prestashop_order_id');
            PayshopLog::generate('Executou via KO a exclusao da ordem: ' . $orderId);
            
            $order = new Order($orderId);

            if (Validate::isLoadedObject($order)) {
                $id_cart = $order->id_cart;
                $cart = new Cart($id_cart);
                $new_cart = $cart->duplicate();

                if ($new_cart['success']) {
                    $this->context->cart = $new_cart['cart'];
                    $this->context->cookie->id_cart = (int)$new_cart['cart']->id;
                    $this->context->cookie->write();

                    $this->restoreStock($order);
                    $this->deleteOrder($order);

                    Tools::redirect(
                        PayshopHelpers::errorResponse(
                            $this->module->l('Ckeck your payment information and try again.', 'ProcessFailedRedirect')
                        )
                    );
                }
            } else {
                throw new Exception();
            }
        } catch (\Throwable $th) {
            Tools::redirect('index.php?controller=order&step=1');
        }
    }

    /**
     * Restore the stock to the product
     * 
     * @param mixed $order 
     * @return void 
     */
    private function restoreStock($order)
    {
        foreach ($order->getProducts() as $product) {
            $productId = (int)$product['product_id'];
            $productAttributeId = (int)$product['product_attribute_id'];
            $quantity = (int)$product['product_quantity'];

            StockAvailable::updateQuantity($productId, $productAttributeId, $quantity);
        }
    }

    /**
     * Delete the order
     * 
     * @param Order $order
     * @return bool
     */
    private function deleteOrder($order)
    {
        // Excluir o pedido
        $orderDeleted = $order->delete();

        if (!$orderDeleted) {
            return false;
        }

        Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'order_detail WHERE id_order = ' . (int)$order->id);
        Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'order_carrier WHERE id_order = ' . (int)$order->id);
        Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'order_history WHERE id_order = ' . (int)$order->id);

        return true;
    }


}
