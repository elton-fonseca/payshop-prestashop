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

require_once PAYSHOP_ROOT_URL . '/includes/module/settings/AbstractSettings.php';

class PaymentsSettings extends AbstractSettings
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
        $title = $this->module->l('Payments', 'PaymentsSettings');
        $fields = array(
            array(
                'col' => 4,
                'type' => 'switch',
                'label' => $this->module->l('Credit Card', 'PaymentsSettings'),
                'name' => 'PAYSHOP_CREDIT_CARD',
                'is_bool' => true,
                'desc' => $this->module->l('Active de payment method on Checkout ', 'PaymentsSettings'),
                'values' => array(
                    array(
                        'id' => 'PAYSHOP_CREDIT_CARD_ON',
                        'value' => true,
                        'label' => $this->module->l('Enable', 'PaymentsSettings')
                    ),
                    array(
                        'id' => 'PAYSHOP_CREDIT_CARD_OFF',
                        'value' => false,
                        'label' => $this->module->l('Disable', 'PaymentsSettings')
                    )
                )
            ),
            array(
                'col' => 4,
                'type' => 'switch',
                'label' => $this->module->l('Multibanco Reference', 'PaymentsSettings'),
                'name' => 'PAYSHOP_MULTIBANCO_REFERENCE',
                'is_bool' => true,
                'desc' => $this->module->l('Active de payment method on Checkout ', 'PaymentsSettings'),
                'values' => array(
                    array(
                        'id' => 'PAYSHOP_MULTIBANCO_REFERENCE_ON',
                        'value' => true,
                        'label' => $this->module->l('Enable', 'PaymentsSettings')
                    ),
                    array(
                        'id' => 'PAYSHOP_MULTIBANCO_REFERENCE_OFF',
                        'value' => false,
                        'label' => $this->module->l('Disable', 'PaymentsSettings')
                    )
                )
            ),
            array(
                'col' => 8,
                'type' => 'text',
                'desc' => $this->module->l('Number of days for Multibanco reference to expire', 'PaymentsSettings'),
                'name' => 'PAYSHOP_MULTIBANCO_REFERENCE_EXPIRATION_DAYS',
                'label' => $this->module->l('Days to expire', 'PaymentsSettings'),
                'required' => true
            ),    
            array(
                'col' => 4,
                'type' => 'switch',
                'label' => $this->module->l('Payshop Reference', 'PaymentsSettings'),
                'name' => 'PAYSHOP_PAYSHOP_REFERENCE',
                'is_bool' => true,
                'desc' => $this->module->l('Active de payment method on Checkout ', 'PaymentsSettings'),
                'values' => array(
                    array(
                        'id' => 'PAYSHOP_PAYSHOP_REFERENCE_ON',
                        'value' => true,
                        'label' => $this->module->l('Enable', 'PaymentsSettings')
                    ),
                    array(
                        'id' => 'PAYSHOP_PAYSHOP_REFERENCE_OFF',
                        'value' => false,
                        'label' => $this->module->l('Disable', 'PaymentsSettings')
                    )
                )
            ),
            array(
                'col' => 8,
                'type' => 'text',
                'desc' => $this->module->l('Number of days for Payshop reference to expire', 'PaymentsSettings'),
                'name' => 'PAYSHOP_PAYSHOP_REFERENCE_EXPIRATION_DAYS',
                'label' => $this->module->l('Days to expire', 'PaymentsSettings'), 
                'required' => true
            ),   
            array(
                'col' => 4,
                'type' => 'switch',
                'label' => $this->module->l('MBWay', 'PaymentsSettings'),
                'name' => 'PAYSHOP_CREDIT_MBWAY',
                'is_bool' => true,
                'desc' => $this->module->l('Active de payment method on Checkout ', 'PaymentsSettings'),
                'values' => array(
                    array(
                        'id' => 'PAYSHOP_CREDIT_MBWAY_ON',
                        'value' => true,
                        'label' => $this->module->l('Enable', 'PaymentsSettings')
                    ),
                    array(
                        'id' => 'PAYSHOP_CREDIT_MBWAY_OFF',
                        'value' => false,
                        'label' => $this->module->l('Disable', 'PaymentsSettings')
                    )
                )
            )

        );

        return $this->buildForm($title, $fields);
    }

    /**
     * Save form data
     *
     * @return void
     */
    public function postFormProcess()
    {
        $this->validate = ([]);

        parent::postFormProcess();

        if (Payshop::$form_alert != 'alert-danger') {
            Payshop::$form_message = $this->module->l('Payment Methods saved successfully.', 'PaymentsSettings');
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
            'PAYSHOP_MULTIBANCO_REFERENCE_EXPIRATION_DAYS' => Configuration::get('PAYSHOP_MULTIBANCO_REFERENCE_EXPIRATION_DAYS'),
            'PAYSHOP_PAYSHOP_REFERENCE' => Configuration::get('PAYSHOP_PAYSHOP_REFERENCE'),
            'PAYSHOP_PAYSHOP_REFERENCE_EXPIRATION_DAYS' => Configuration::get('PAYSHOP_PAYSHOP_REFERENCE_EXPIRATION_DAYS'),
            'PAYSHOP_CREDIT_MBWAY' => Configuration::get('PAYSHOP_CREDIT_MBWAY'),
        );
    }


}
