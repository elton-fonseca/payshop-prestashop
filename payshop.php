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

define('PAYSHOP_VERSION', '2.0.2');
define('PAYSHOP_ROOT_URL', dirname(__FILE__));

if (!defined('_PS_VERSION_')) {
    exit;
}

class Payshop extends PaymentModule
{
    public $tab;
    public $name;
    public $path;
    public $pathDir;
    public $author;
    public $version;
    public $context;
    public $bootstrap;
    public $displayName;
    public $description;
    public $need_instance;
    public $assets_ext_min;
    public $confirmUninstall;
    public $ps_versions_compliancy;
    public $ps_version;

    public $payshopPaymentMethods;
    public $payshopOrderStatuses;

    public static $form_alert;
    public static $form_message;

    public function __construct()
    {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        $this->loadFiles();

        $this->name = 'payshop';
        $this->tab = 'payments_gateways';
        $this->author = 'payshop';
        $this->need_instance = 1;
        $this->bootstrap = true;

        $this->version = PAYSHOP_VERSION;
        $this->ps_versions_compliancy = array('min' => '1.7.0', 'max' => _PS_VERSION_);

        parent::__construct();

        $this->displayName = $this->l('Payshop Online Payments', 'payshop');
        $this->description = $this->l('Customize the payment experience of your customers in your online store.', 'payshop');
        $this->confirmUninstall = $this->l('Are you sure you want to uninstall the module?', 'payshop');

        $this->ps_version = _PS_VERSION_;
        $this->path = $this->_path;
        $this->pathDir = str_replace('\\', '/', __DIR__);

        $this->payshopPaymentMethods = new PayshopPaymentMethods($this);
        $this->payshopOrderStatuses = new PayshopOrderStatuses();
    }

    /**
     * Load module files
     *
     * @return void
     */
    public function loadFiles()
    {
        include_once PAYSHOP_ROOT_URL . '/includes/PayshopLog.php';
        include_once PAYSHOP_ROOT_URL . '/includes/PayshopHelpers.php';

        include_once PAYSHOP_ROOT_URL . '/includes/module/settings/PayshopConfigurationPage.php';
        include_once PAYSHOP_ROOT_URL . '/includes/module/settings/PayshopPaymentServiceUUID.php';

        include_once PAYSHOP_ROOT_URL . '/includes/module/alerts/PayshopUpdateAlert.php';
        include_once PAYSHOP_ROOT_URL . '/controllers/front/UpdateAlertClose.php';

        include_once PAYSHOP_ROOT_URL . '/includes/module/payments/PayshopPaymentMethods.php';
        include_once PAYSHOP_ROOT_URL . '/includes/module/payments/PayshopCreditCard.php';
        include_once PAYSHOP_ROOT_URL . '/includes/module/payments/PayshopMBWay.php';
        include_once PAYSHOP_ROOT_URL . '/includes/module/payments/PayshopReference.php';
        include_once PAYSHOP_ROOT_URL . '/includes/module/payments/PayshopMultibanco.php';

        include_once PAYSHOP_ROOT_URL . '/includes/module/hooks/PayshopShowReferencesOrderConfirmation.php';

        include_once PAYSHOP_ROOT_URL . '/includes/module/statuses/PayshopOrderStatuses.php';

        include_once PAYSHOP_ROOT_URL . '/includes/module/models/PayshopTransaction.php';

        include_once PAYSHOP_ROOT_URL . '/includes/module/actions/PayshopCreatePaymentOrder.php';
        include_once PAYSHOP_ROOT_URL . '/includes/module/actions/PayshopCreateOrder.php';
        include_once PAYSHOP_ROOT_URL . '/includes/module/actions/PayshopCreatePrestashopOrder.php';
        include_once PAYSHOP_ROOT_URL . '/includes/module/actions/PayshopUpdateOrder.php';

        include_once PAYSHOP_ROOT_URL . '/includes/sdk/PayshopClientFactory.php';

        include_once PAYSHOP_ROOT_URL . '/includes/sdk/PayshopEvent.php';
        include_once PAYSHOP_ROOT_URL . '/includes/module/events/PayshopProcessEvent.php';
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

        include PAYSHOP_ROOT_URL . '/database/install.php';
        $this->payshopOrderStatuses->register();

        //install hooks and dependencies
        return parent::install() &&
            $this->registerHook('payment') &&
            $this->registerHook('paymentReturn') &&
            $this->registerHook('displayAdminAfterHeader') &&
            $this->registerHook('displayWrapperTop') &&
            $this->registerHook('orderConfirmation') &&
            $this->registerHook('paymentOptions') &&
            $this->registerHook('ActionFrontControllerSetMedia') &&
            $this->registerHook('sendMailAlterTemplateVars');
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
    public function getContent()
    {
        $configurationPage = new PayshopConfigurationPage();

        return $configurationPage->getContent();
    }

    /**
     * Show update module alert
     *
     * @return string
     */
    public function hookDisplayAdminAfterHeader()
    {
        $updateAlert = new PayshopUpdateAlert($this, $this->local_path);

        return $updateAlert->execute();
    }

    /**
     * Show payment options
     *
     * @param  $params
     * @return array|string|void
     */
    public function hookPaymentOptions($params)
    {
        return $this->payshopPaymentMethods->getPaymentOptions($params);
    }

    /**
     * Show payment information on order confirmation page
     *
     * @param  $params
     * @return array|string|void
     */
    public function hookOrderConfirmation($params)
    {
        $order = $params['order'];

        $showReferencesOrderConfirmation = new PayshopShowReferencesOrderConfirmation($this);
        return $showReferencesOrderConfirmation->execute($order);
    }

    /**
     * Register css used to payments form
     *
     * @return void
     */
    public function hookActionFrontControllerSetMedia()
    {
        if ('order' === $this->context->controller->php_self) {
            $this->context->controller->addCSS(
                $this->_path . 'views/css/form-styles.css',
                false
            );
        }
    }

    /**
     * Display payment failure
     *
     * @return string
     */
    public function hookDisplayWrapperTop()
    {
        if ('order' !== $this->context->controller->php_self) {
            return;
        }

        $messageError = '';

        $cookie = $this->context->cookie;
        if ($cookie->__isset('redirect_message')) {
            $messageError = $cookie->__get('redirect_message');
            $cookie->__unset('redirect_message');
        }

        $this->context->smarty->assign([
            'hasMessage' => Tools::getValue('typeReturn') == 'failure',
            'message' => $messageError,
            'moduleUrl' => $this->path,
        ]);

        return $this->display(__FILE__, 'views/templates/hook/order-wrapper-top.tpl');
    }
}

function ddpayshop(...$asd)
{
    echo "<pre>";
    print_r($asd);
    echo "</pre>";
    exit;
}