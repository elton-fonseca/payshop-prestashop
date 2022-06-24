<?php
/**
 * 2007-2022 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
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
 * @author    Payshop
 * @copyright Copyright (c) Payshop [http://www.payshop.com]
 * @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  International Registered Trademark & Property of Payshop
 */

define('PAYSHOP_VERSION', '0.0.1');
define('PAYSHOP_ROOT_URL', dirname(__FILE__));

if (!defined('_PS_VERSION_')) {
    exit;
}

class Payshop extends PaymentModule
{
    public $tab;
    public $name;
    public $path;
    public $author;
    public $version;
    public $context;
    public $mpuseful;
    public $bootstrap;
    public $module_key;
    public $displayName;
    public $description;
    public $need_instance;
    public $assets_ext_min;
    public $customCheckout;
    public $ticketCheckout;
    public $standardCheckout;
    public $pixCheckout;
    public $confirmUninstall;
    public $ps_versions_compliancy;
    public $ps_version;
    public static $form_alert;
    public static $form_message;

    const PRESTA17 = "1.7";

    public function __construct()
    {
        $this->loadFiles();

        $this->name = 'payshop';
        $this->tab = 'payments_gateways';
        $this->author = 'payshop';
        $this->need_instance = 1;
        $this->bootstrap = true;

        $this->version = '0.0.1';
        $this->ps_versions_compliancy = array('min' => '1.7.0', 'max' => _PS_VERSION_);

        parent::__construct();

        $this->displayName = $this->l('PayShop');
        $this->description = $this->l('Customize the payment experience of your customers in your online store.');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the module?');

        $this->ps_version = _PS_VERSION_;
        $this->assets_ext_min = !_PS_MODE_DEV_ ? '.min' : '';
        $this->path = $this->_path;

    }

    /**
     * Load files
     *
     * @return void
     */
    public function loadFiles()
    {
       
    }

    /**
     * Install the module
     *
     * @return bool
     * @throws PrestaShopException
     */
    public function install()
    {
        if (extension_loaded('curl') == false) {
            $this->_errors[] = $this->l('You have to enable the cURL extension ' .
            'on your server to install this module.');
            return false;
        }

        //install hooks and dependencies
        return parent::install() &&
            $this->registerHook('payment') &&
            $this->registerHook('paymentReturn');
    }

    /**
     * Uninstall the module
     *
     * @return bool
     */
    public function uninstall()
    {
        return parent::uninstall();
    }

    /**
     * Load the configuration form
     *
     * @return string
     */
    public function getContext()
    {
        return $this->context;
    }
}