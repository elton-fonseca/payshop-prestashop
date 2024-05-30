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
            try {
                self::$payshopClient = new PayshopClient(
                    self::getApiKey(),
                    self::getSignature(),
                    self::isProduction()
                );
            } catch (\Exception $e) {
                $message = 'API ' . $e->getMessage();
                PayshopLog::generate($message, 'error');

                http_response_code(401);
                exit;
            }
        }

        return self::$payshopClient;
    }

    /**
     * Check if the module is in production mode
     *
     * @return boolean
     */
    public static function isProduction()
    {
        return !! Configuration::get('PAYSHOP_PROD_STATUS');
    }

    /**
     * Get public key based on the environment
     *
     * @return string
     */
    public static function getApiKey()
    {
        if (self::isProduction() == true) {
            return Configuration::get('PAYSHOP_API_KEY');
        }

        return Configuration::get('PAYSHOP_SANDBOX_API_KEY');
    }

    /**
     * Get secret key based on the environment
     *
     * @return string
     */
    private static function getSignature()
    {
        if (self::isProduction() == true) {
            return Configuration::get('PAYSHOP_SIGNATURE');
        }

        return Configuration::get('PAYSHOP_SANDBOX_SIGNATURE');
    }
}