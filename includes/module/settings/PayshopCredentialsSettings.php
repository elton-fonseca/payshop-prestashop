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

class PayshopCredentialsSettings extends PayshopAbstractSettings
{
    public function __construct()
    {
        parent::__construct();
        $this->submit = 'submitPayshopCredentials';
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
        $title = $this->module->l('Credentials', 'PayshopCredentialsSettings');
        $fields = array(
            array(
                'col' => 4,
                'type' => 'switch',
                'label' => $this->module->l('Production', 'PayshopCredentialsSettings'),
                'name' => 'PAYSHOP_PROD_STATUS',
                'is_bool' => true,
                'desc' => $this->module->l('Select "YES" only when you are ready to sell. ', 'PayshopCredentialsSettings') .
                    $this->module->l('Change to NO to activate the Sandbox ', 'PayshopCredentialsSettings') .
                    $this->module->l('test environment.', 'PayshopCredentialsSettings'),
                'values' => array(
                    array(
                        'id' => 'PAYSHOP_PROD_STATUS_ON',
                        'value' => true,
                        'label' => $this->module->l('Yes', 'PayshopCredentialsSettings')
                    ),
                    array(
                        'id' => 'PAYSHOP_PROD_STATUS_OFF',
                        'value' => false,
                        'label' => $this->module->l('No', 'PayshopCredentialsSettings')
                    )
                ),
            ),
            array(
                'col' => 8,
                'type' => 'html',
                'name' => '',
                'desc' => '',
                'label' => $this->module->l('Load credentials', 'PayshopCredentialsSettings'),
                'html_content' => '<a href="https://dashboard.switchpayments.com/"'. 
                'target="_blank" class="btn btn-default mp-btn-credenciais">'
                . $this->module->l('Search my credentials', 'PayshopCredentialsSettings') . '</a>'
            ),
            array(
                'col' => 8,
                'type' => 'text',
                'desc' => '',
                'name' => 'PAYSHOP_ACCOUNT_ID',
                'label' => $this->module->l('Account ID', 'PayshopCredentialsSettings'),
                'required' => true
            ),            
            array(
                'col' => 8,
                'type' => 'text',
                'desc' => '',
                'name' => 'PAYSHOP_PUBLIC_KEY',
                'label' => $this->module->l('Public Key', 'PayshopCredentialsSettings'),
                'required' => true
            ),
            array(
                'col' => 8,
                'type' => 'text',
                'desc' => ' ',
                'name' => 'PAYSHOP_SECRET_KEY',
                'label' => $this->module->l('Access token', 'PayshopCredentialsSettings'),
                'required' => true
            ),
            array(
                'col' => 8,
                'type' => 'text',
                'desc' => '',
                'name' => 'PAYSHOP_SANDBOX_PUBLIC_KEY',
                'label' => $this->module->l('Public Key', 'PayshopCredentialsSettings'),
                'required' => true
            ),
            array(
                'col' => 8,
                'type' => 'text',
                'desc' => '',
                'name' => 'PAYSHOP_SANDBOX_SECRET_KEY',
                'label' => $this->module->l('Access token', 'PayshopCredentialsSettings'),
                'required' => true
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
        $this->validate = ([
            'PAYSHOP_ACCOUNT_ID' => 'account_id',
            'PAYSHOP_PUBLIC_KEY' => 'public_key',
            'PAYSHOP_SECRET_KEY' => 'secret_key',
            'PAYSHOP_SANDBOX_PUBLIC_KEY' => 'public_key',
            'PAYSHOP_SANDBOX_SECRET_KEY' => 'secret_key',
        ]);

        parent::postFormProcess();

        if (Payshop::$form_alert != 'alert-danger') {
            $message = $this->module->l('Settings saved successfully.', 'PayshopCredentialsSettings');
            Payshop::$form_message = $message;
            PayshopLog::generate($message);
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
            'PAYSHOP_PROD_STATUS' => Configuration::get('PAYSHOP_PROD_STATUS'),
            'PAYSHOP_ACCOUNT_ID' => Configuration::get('PAYSHOP_ACCOUNT_ID'),
            'PAYSHOP_PUBLIC_KEY' => Configuration::get('PAYSHOP_PUBLIC_KEY'),
            'PAYSHOP_SECRET_KEY' => Configuration::get('PAYSHOP_SECRET_KEY'),
            'PAYSHOP_SANDBOX_PUBLIC_KEY' => Configuration::get('PAYSHOP_SANDBOX_PUBLIC_KEY'),
            'PAYSHOP_SANDBOX_SECRET_KEY' => Configuration::get('PAYSHOP_SANDBOX_SECRET_KEY')
        );
    }
}
