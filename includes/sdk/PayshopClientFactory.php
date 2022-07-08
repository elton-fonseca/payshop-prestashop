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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2022 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *
 * Don't forget to prefix your containers with your own identifier
 * to avoid any conflicts with others containers.
 */

require_once 'PayshopClient.php';

class PayshopClientFactory
{
    public function __construct()
    {
    }

    static $payshopClient = null;

    /**
     * Instanciete the class
     *
     * @return PayshopClient
     */
    public static function getInstance()
    {
        if (null === self::$payshopClient) {
            self::$payshopClient = new PayshopClient(
                self::getPublicKey(),
                self::getSecretKey(),
                self::getAccountId(),
                self::isProduction()
            );
        }

        return self::$payshopClient;
    }

    /**
     * Check if the module is in production mode
     *
     * @return boolean
     */
    private static function isProduction()
    {
        return !! Configuration::get('PAYSHOP_PROD_STATUS');
    }

    /**
     * Get public key based on the environment
     *
     * @return string
     */
    private static function getPublicKey()
    {
        if (self::isProduction() == true) {
            return Configuration::get('PAYSHOP_PUBLIC_KEY');
        }

        return Configuration::get('PAYSHOP_SANDBOX_PUBLIC_KEY');
    }

    /**
     * Get secret key based on the environment
     *
     * @return string
     */
    private static function getSecretKey()
    {
        if (self::isProduction() == true) {
            return Configuration::get('PAYSHOP_SECRET_KEY');
        }

        return Configuration::get('PAYSHOP_SANDBOX_SECRET_KEY');
    }

    /**
     * Get account ID based on the environment
     *
     * @return string
     */
    private static function getAccountId()
    {
        return Configuration::get('PAYSHOP_ACCOUNT_ID');
    }
}