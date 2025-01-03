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

require_once PAYSHOP_ROOT_URL . '/includes/module/settings/PayshopAbstractSettings.php';

class PayshopPaymentsSettings extends PayshopAbstractSettings
{
    public function __construct()
    {
        parent::__construct();
        $this->submit = 'submitPayshopPayments';
        $this->values = $this->getFormValues();
        $this->form = $this->generateForm();
        $this->process = $this->verifyPostProcess();
    }

    /**
     * Generate inputs form
     *
     * @return void
     */
    public function generateForm()
    {

        $title = $this->module->l('Payments', 'PayshopPaymentsSettings');

        $fields = array();

        if (Configuration::get('PAYSHOP_CARD_SERVICE_UUID', false)) {
            $fields[] = array(
                'col' => 4,
                'type' => 'switch',
                'label' => $this->module->l('Credit Card', 'PayshopPaymentsSettings'),
                'name' => 'PAYSHOP_CREDIT_CARD',
                'is_bool' => true,
                'desc' => $this->module->l('Active de payment method on Checkout ', 'PayshopPaymentsSettings'),
                'values' => array(
                    array(
                        'id' => 'PAYSHOP_CREDIT_CARD_ON',
                        'value' => true,
                        'label' => $this->module->l('Enable', 'PayshopPaymentsSettings')
                    ),
                    array(
                        'id' => 'PAYSHOP_CREDIT_CARD_OFF',
                        'value' => false,
                        'label' => $this->module->l('Disable', 'PayshopPaymentsSettings')
                    )
                )
            );
        }

        if (Configuration::get('PAYSHOP_MULTIBANCO_REFERENCE_SERVICE_UUID', false)) {
            $fields[] = array(
                'col' => 4,
                'type' => 'switch',
                'label' => $this->module->l('Multibanco Reference', 'PayshopPaymentsSettings'),
                'name' => 'PAYSHOP_MULTIBANCO_REFERENCE',
                'is_bool' => true,
                'desc' => $this->module->l('Active de payment method on Checkout ', 'PayshopPaymentsSettings'),
                'values' => array(
                    array(
                        'id' => 'PAYSHOP_MULTIBANCO_REFERENCE_ON',
                        'value' => true,
                        'label' => $this->module->l('Enable', 'PayshopPaymentsSettings')
                    ),
                    array(
                        'id' => 'PAYSHOP_MULTIBANCO_REFERENCE_OFF',
                        'value' => false,
                        'label' => $this->module->l('Disable', 'PayshopPaymentsSettings')
                    )
                )
            );
        }

        if (Configuration::get('PAYSHOP_REFERENCE_SERVICE_UUID', false)) {
            $fields[] = array(
                'col' => 4,
                'type' => 'switch',
                'label' => $this->module->l('Payshop Reference', 'PayshopPaymentsSettings'),
                'name' => 'PAYSHOP_PAYSHOP_REFERENCE',
                'is_bool' => true,
                'desc' => $this->module->l('Active de payment method on Checkout ', 'PayshopPaymentsSettings'),
                'values' => array(
                    array(
                        'id' => 'PAYSHOP_PAYSHOP_REFERENCE_ON',
                        'value' => true,
                        'label' => $this->module->l('Enable', 'PayshopPaymentsSettings')
                    ),
                    array(
                        'id' => 'PAYSHOP_PAYSHOP_REFERENCE_OFF',
                        'value' => false,
                        'label' => $this->module->l('Disable', 'PayshopPaymentsSettings')
                    )
                )
            );
        }

        if (Configuration::get('PAYSHOP_MBWAY_SERVICE_UUID', false)) {
            $fields[] = array(
                'col' => 4,
                'type' => 'switch',
                'label' => $this->module->l('MBWay', 'PayshopPaymentsSettings'),
                'name' => 'PAYSHOP_MBWAY',
                'is_bool' => true,
                'desc' => $this->module->l('Active de payment method on Checkout ', 'PayshopPaymentsSettings'),
                'values' => array(
                    array(
                        'id' => 'PAYSHOP_MBWAY_ON',
                        'value' => true,
                        'label' => $this->module->l('Enable', 'PayshopPaymentsSettings')
                    ),
                    array(
                        'id' => 'PAYSHOP_MBWAY_OFF',
                        'value' => false,
                        'label' => $this->module->l('Disable', 'PayshopPaymentsSettings')
                    )
                )
            );
        }

        if (true || Configuration::get('PAYSHOP_GOOGLEPAY_SERVICE_UUID', false)) {
            $fields[] = array(
                'col' => 4,
                'type' => 'switch',
                'label' => $this->module->l('Google Pay', 'PayshopPaymentsSettings'),
                'name' => 'PAYSHOP_GOOGLEPAY',
                'is_bool' => true,
                'desc' => $this->module->l('Activate the payment method on Checkout', 'PayshopPaymentsSettings'),
                'values' => array(
                    array(
                    'id' => 'PAYSHOP_GOOGLEPAY_ON',
                    'value' => true,
                    'label' => $this->module->l('Enable', 'PayshopPaymentsSettings')
                    ),
                    array(
                    'id' => 'PAYSHOP_GOOGLEPAY_OFF',
                    'value' => false,
                    'label' => $this->module->l('Disable', 'PayshopPaymentsSettings')
                    )
                )
            );
            
            $fields[] = array(
                'col' => 8,
                'type' => 'text',
                'desc' => 'Google Pay Merchant ID created in the Google Business Console: https://pay.google.com/business/console/',
                'name' => 'PAYSHOP_GOOGLEPAY_MERCHANT_ID',
                'label' => $this->module->l('Google Pay Merchant ID', 'PayshopPaymentsSettings'),
                'required' => true
            );
        }

        if (true || Configuration::get('PAYSHOP_APPLEPAY_SERVICE_UUID', false)) {
            $fields[] = array(
                'col' => 4,
                'type' => 'switch',
                'label' => $this->module->l('Apple Pay', 'PayshopPaymentsSettings'),
                'name' => 'PAYSHOP_APPLEPAY',
                'is_bool' => true,
                'desc' => $this->module->l('Activate the payment method on Checkout', 'PayshopPaymentsSettings'),
                'values' => array(
                    array(
                    'id' => 'PAYSHOP_APPLEPAY_ON',
                    'value' => true,
                    'label' => $this->module->l('Enable', 'PayshopPaymentsSettings')
                    ),
                    array(
                    'id' => 'PAYSHOP_APPLEPAY_OFF',
                    'value' => false,
                    'label' => $this->module->l('Disable', 'PayshopPaymentsSettings')
                    )
                )
            );

            $fields[] = array(
                'col' => 8,
                'type' => 'text',
                'desc' => 'Your Apple Pay Merchant Identifier.',
                'name' => 'PAYSHOP_APPLEPAY_MERCHANT_ID',
                'label' => $this->module->l('Merchant Identifier', 'PayshopPaymentsSettings'),
                'required' => true
            );

            $fields[] = array(
                'col' => 8,
                'type' => 'text',
                'desc' => 'The display name for your Apple Pay merchant.',
                'name' => 'PAYSHOP_APPLEPAY_MERCHANT_NAME',
                'label' => $this->module->l('Display Name', 'PayshopPaymentsSettings'),
                'required' => true
            );

            $fields[] = array(
                'col' => 8,
                'type' => 'text',
                'desc' => 'The domain name associated with your Apple Pay merchant.',
                'name' => 'PAYSHOP_APPLEPAY_DOMAIN_NAME',
                'label' => $this->module->l('Site domain', 'PayshopPaymentsSettings'),
                'required' => true
            );

            $fields[] = array(
                'col' => 8,
                'type' => 'text',
                'desc' => 'The absolute path to your Apple Pay certificate. Do not place it inside the Prestashop directory for security reasons.',
                'name' => 'PAYSHOP_APPLEPAY_CERTIFICATE_PATH',
                'label' => $this->module->l('Certificate Path', 'PayshopPaymentsSettings'),
                'required' => true
            );

            $fields[] = array(
                'col' => 8,
                'type' => 'text',
                'desc' => 'The absolute path to your Apple Pay private key. Do not place it inside the Prestashop directory for security reasons.',
                'name' => 'PAYSHOP_APPLEPAY_PRIVATE_KEY_PATH',
                'label' => $this->module->l('Private Key Path', 'PayshopPaymentsSettings'),
                'required' => true
            );
        }

        if (true || Configuration::get('PAYSHOP_PAYPAL_SERVICE_UUID', false)) {
            $fields[] = array(
                'col' => 4,
                'type' => 'switch',
                'label' => $this->module->l('PayPal', 'PayshopPaymentsSettings'),
                'name' => 'PAYSHOP_PAYPAL',
                'is_bool' => true,
                'desc' => $this->module->l('Activate the payment method on Checkout', 'PayshopPaymentsSettings'),
                'values' => array(
                    array(
                    'id' => 'PAYSHOP_PAYPAL_ON',
                    'value' => true,
                    'label' => $this->module->l('Enable', 'PayshopPaymentsSettings')
                    ),
                    array(
                    'id' => 'PAYSHOP_PAYPAL_OFF',
                    'value' => false,
                    'label' => $this->module->l('Disable', 'PayshopPaymentsSettings')
                    )
                )
            );
        }

        if (true || Configuration::get('PAYSHOP_CLICKTOPAY_SERVICE_UUID', false)) {
            $fields[] = array(
            'col' => 4,
            'type' => 'switch',
            'label' => $this->module->l('Click to Pay', 'PayshopPaymentsSettings'),
            'name' => 'PAYSHOP_CLICKTOPAY',
            'is_bool' => true,
            'desc' => $this->module->l('Activate the payment method on Checkout', 'PayshopPaymentsSettings'),
            'values' => array(
                array(
                'id' => 'PAYSHOP_CLICKTOPAY_ON',
                'value' => true,
                'label' => $this->module->l('Enable', 'PayshopPaymentsSettings')
                ),
                array(
                'id' => 'PAYSHOP_CLICKTOPAY_OFF',
                'value' => false,
                'label' => $this->module->l('Disable', 'PayshopPaymentsSettings')
                )
            )
            );
        }

        return $this->buildForm($title, $fields);
    }

    /**
     * Save form data
     *
     * @return void
     */
    public function postFormProcess()
    {
        parent::postFormProcess();

        if (Payshop::$form_alert != 'alert-danger') {
            Payshop::$form_message = $this->module->l('Payment Methods saved successfully.', 'PayshopPaymentsSettings');
            PayshopLog::generate('Payment Methods saved successfully');
        }
    }

    /**
     * Set values for the form inputs
     *
     * @return array
     */
    public function getFormValues()
    {
        return array(
            'PAYSHOP_CREDIT_CARD' => Configuration::get('PAYSHOP_CREDIT_CARD'),
            'PAYSHOP_MULTIBANCO_REFERENCE' => Configuration::get('PAYSHOP_MULTIBANCO_REFERENCE'),
            'PAYSHOP_PAYSHOP_REFERENCE' => Configuration::get('PAYSHOP_PAYSHOP_REFERENCE'),
            'PAYSHOP_MBWAY' => Configuration::get('PAYSHOP_MBWAY'),
            'PAYSHOP_GOOGLEPAY' => Configuration::get('PAYSHOP_GOOGLEPAY'),
            'PAYSHOP_GOOGLEPAY_MERCHANT_ID' => Configuration::get('PAYSHOP_GOOGLEPAY_MERCHANT_ID'),
            'PAYSHOP_APPLEPAY' => Configuration::get('PAYSHOP_APPLEPAY'),
            'PAYSHOP_APPLEPAY_MERCHANT_ID' => Configuration::get('PAYSHOP_APPLEPAY_MERCHANT_ID'),
            'PAYSHOP_APPLEPAY_MERCHANT_NAME' => Configuration::get('PAYSHOP_APPLEPAY_MERCHANT_NAME'),
            'PAYSHOP_APPLEPAY_DOMAIN_NAME' => Configuration::get('PAYSHOP_APPLEPAY_DOMAIN_NAME'),
            'PAYSHOP_APPLEPAY_CERTIFICATE_PATH' => Configuration::get('PAYSHOP_APPLEPAY_CERTIFICATE_PATH'),
            'PAYSHOP_APPLEPAY_PRIVATE_KEY_PATH' => Configuration::get('PAYSHOP_APPLEPAY_PRIVATE_KEY_PATH'),
            'PAYSHOP_PAYPAL' => Configuration::get('PAYSHOP_PAYPAL'),
            'PAYSHOP_CLICKTOPAY' => Configuration::get('PAYSHOP_CLICKTOPAY')
        );
    }
}
