<?php

if (!defined('_PS_VERSION_')) {
    exit;
}
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
        $fields = [
            [
                'col' => 4,
                'type' => 'switch',
                'label' => $this->module->l('Production', 'PayshopCredentialsSettings'),
                'name' => 'PAYSHOP_PROD_STATUS',
                'is_bool' => true,
                'desc' => $this->module->l('Select "YES" only when you are ready to sell. ', 'PayshopCredentialsSettings') .
                    $this->module->l('Change to NO to activate the Sandbox ', 'PayshopCredentialsSettings') .
                    $this->module->l('test environment.', 'PayshopCredentialsSettings'),
                'values' => [
                    [
                        'id' => 'PAYSHOP_PROD_STATUS_ON',
                        'value' => true,
                        'label' => $this->module->l('Yes', 'PayshopCredentialsSettings'),
                    ],
                    [
                        'id' => 'PAYSHOP_PROD_STATUS_OFF',
                        'value' => false,
                        'label' => $this->module->l('No', 'PayshopCredentialsSettings'),
                    ],
                ],
            ],
            [
                'col' => 8,
                'type' => 'html',
                'name' => '',
                'desc' => '',
                'label' => $this->module->l('Load credentials', 'PayshopCredentialsSettings'),
                'html_content' => '<a href="https://popbackoffice.payshop.pt/settings/developers"' .
                'target="_blank" class="btn btn-default mp-btn-credenciais">'
                . $this->module->l('Search my credentials', 'PayshopCredentialsSettings') . '</a>',
            ],
            [
                'col' => 8,
                'type' => 'text',
                'desc' => '',
                'name' => 'PAYSHOP_API_KEY',
                'label' => $this->module->l('Api Key', 'PayshopCredentialsSettings'),
                'required' => true,
            ],
            [
                'col' => 8,
                'type' => 'text',
                'desc' => ' ',
                'name' => 'PAYSHOP_SIGNATURE',
                'label' => $this->module->l('Signature', 'PayshopCredentialsSettings'),
                'required' => true,
            ],
            [
                'col' => 8,
                'type' => 'text',
                'desc' => ' ',
                'name' => 'PAYSHOP_CLIENT_UUID',
                'label' => $this->module->l('Client UUID', 'PayshopCredentialsSettings'),
                'required' => true,
            ],
            [
                'col' => 8,
                'type' => 'text',
                'desc' => '',
                'name' => 'PAYSHOP_SANDBOX_API_KEY',
                'label' => $this->module->l('Api Key', 'PayshopCredentialsSettings'),
                'required' => true,
            ],
            [
                'col' => 8,
                'type' => 'text',
                'desc' => '',
                'name' => 'PAYSHOP_SANDBOX_SIGNATURE',
                'label' => $this->module->l('Signature', 'PayshopCredentialsSettings'),
                'required' => true,
            ],
            [
                'col' => 8,
                'type' => 'text',
                'desc' => ' ',
                'name' => 'PAYSHOP_SANDBOX_CLIENT_UUID',
                'label' => $this->module->l('Client UUID', 'PayshopCredentialsSettings'),
                'required' => true,
            ],
        ];

        return $this->buildForm($title, $fields);
    }

    /**
     * Save form data
     *
     * @return void
     */
    public function postFormProcess()
    {
        $this->validate = [
            'PAYSHOP_API_KEY' => 'api_key',
            'PAYSHOP_SIGNATURE' => 'signature',
            'PAYSHOP_CLIENT_UUID' => 'client_uuid',
            'PAYSHOP_SANDBOX_API_KEY' => 'api_key',
            'PAYSHOP_SANDBOX_SIGNATURE' => 'signature',
            'PAYSHOP_SANDBOX_CLIENT_UUID' => 'client_uuid',
        ];

        parent::postFormProcess();

        if (Payshop::$form_alert != 'alert-danger') {
            $message = $this->module->l('Settings saved successfully.', 'PayshopCredentialsSettings');
            Payshop::$form_message = $message;
            PayshopLog::generate('PayshopCredentialsSettings: ' . $message);
        }
    }

    /**
     * Set values for the form inputs
     *
     * @return array
     */
    public function getFormValues()
    {
        return [
            'PAYSHOP_PROD_STATUS' => Configuration::get('PAYSHOP_PROD_STATUS'),
            'PAYSHOP_API_KEY' => Configuration::get('PAYSHOP_API_KEY'),
            'PAYSHOP_SIGNATURE' => Configuration::get('PAYSHOP_SIGNATURE'),
            'PAYSHOP_CLIENT_UUID' => Configuration::get('PAYSHOP_CLIENT_UUID'),
            'PAYSHOP_SANDBOX_API_KEY' => Configuration::get('PAYSHOP_SANDBOX_API_KEY'),
            'PAYSHOP_SANDBOX_SIGNATURE' => Configuration::get('PAYSHOP_SANDBOX_SIGNATURE'),
            'PAYSHOP_SANDBOX_CLIENT_UUID' => Configuration::get('PAYSHOP_SANDBOX_CLIENT_UUID'),
        ];
    }
}
