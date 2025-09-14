<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class PayshopConfigurationPage extends Payshop
{
    /**
     * Generate the form for the configuration page
     *
     * @return string
     */
    public function getContent()
    {
        $this->context->smarty->assign('module_dir', $this->_path);

        $this->loadSettingsFiles();

        $credentials = new PayshopCredentialsSettings();
        $payments = new PayshopPaymentsSettings();

        $credentialsForm = $this->renderSettingsForm($credentials->submit, $credentials->values, $credentials->form);

        $paymentsForm = '';
        if ($this->areThereActivePayments()) {
            $paymentsForm = $this->renderSettingsForm($payments->submit, $payments->values, $payments->form);
        }

        // variables for admin configuration
        $api_key = Configuration::get('PAYSHOP_API_KEY');
        $signature = Configuration::get('PAYSHOP_SIGNATURE');
        $sandbox_api_key = Configuration::get('PAYSHOP_SANDBOX_API_KEY');
        $sandbox_signature = Configuration::get('PAYSHOP_SANDBOX_SIGNATURE');

        // $output = $this->context->smarty->assign(
        //     [
        //         // module requirements
        //         'alert' => Payshop::$form_alert,
        //         'message' => Payshop::$form_message,
        //         'payshop_version' => PAYSHOP_VERSION,
        //         'url_base' => __PS_BASE_URI__,
        //         'log' => PayshopLog::getLogUrl(),
        //         // credentials
        //         'api_key' => $api_key,
        //         'signature' => $signature,
        //         'sandbox_api_key' => $sandbox_api_key,
        //         'sandbox_signature' => $sandbox_signature,
        //         // forms
        //         'credentialsForm' => $credentialsForm,
        //         'paymentsForm' => $paymentsForm,
        //         // currencies
        //         'currency' => $this->context->currency->iso_code,
        //     ]
        // )->fetch($this->local_path . 'views/templates/admin/configurations.tpl');

        $this->context->smarty->assign(
            [
                // module requirements
                'alert' => Payshop::$form_alert,
                'message' => Payshop::$form_message,
                'payshop_version' => PAYSHOP_VERSION,
                'url_base' => __PS_BASE_URI__,
                'log' => PayshopLog::getLogUrl(),
                // credentials
                'api_key' => $api_key,
                'signature' => $signature,
                'sandbox_api_key' => $sandbox_api_key,
                'sandbox_signature' => $sandbox_signature,
                // forms
                'credentialsForm' => $credentialsForm,
                'paymentsForm' => $paymentsForm,
                // currencies
                'currency' => $this->context->currency->iso_code,
            ]
        );
        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configurations.tpl');

        return $output;
    }

    /**
     * Load settings class files
     *
     * @return void
     */
    private function loadSettingsFiles()
    {
        // include_once PAYSHOP_ROOT_URL . '/includes/module/settings/PayshopCredentialsSettings.php';
        // include_once PAYSHOP_ROOT_URL . '/includes/module/settings/PayshopPaymentsSettings.php';
        include_once $this->local_path . 'includes/module/settings/PayshopCredentialsSettings.php';
        include_once $this->local_path . 'includes/module/settings/PayshopPaymentsSettings.php';
    }

    /**
     * Render forms
     *
     * @param $submit
     * @param $values
     * @param $form
     *
     * @return string
     */
    private function renderSettingsForm($submit, $values, $form)
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->submit_action = $submit;
        $helper->identifier = $this->identifier;
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $values,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$form]);
    }

    /**
     * Are there active payments?
     *
     * @return bool
     */
    private function areThereActivePayments()
    {
        // return
        //     Configuration::get('PAYSHOP_CARD_SERVICE_UUID', false)
        //     || Configuration::get('PAYSHOP_MBWAY_SERVICE_UUID', false)
        //     || Configuration::get('PAYSHOP_MULTIBANCO_REFERENCE_SERVICE_UUID', false)
        //     || Configuration::get('PAYSHOP_REFERENCE_SERVICE_UUID', false)
        //     || Configuration::get('PAYSHOP_GOOGLEPAY_SERVICE_UUID', false)
        //     || Configuration::get('PAYSHOP_APPLEPAY_SERVICE_UUID', false)
        //     || Configuration::get('PAYSHOP_PAYPAL_SERVICE_UUID', false)
        //     || Configuration::get('PAYSHOP_CLICKTOPAY_SERVICE_UUID', false)
        // ;
        return
            Configuration::get('PAYSHOP_CARD_SERVICE_UUID')
            || Configuration::get('PAYSHOP_MBWAY_SERVICE_UUID')
            || Configuration::get('PAYSHOP_MULTIBANCO_REFERENCE_SERVICE_UUID')
            || Configuration::get('PAYSHOP_REFERENCE_SERVICE_UUID')
            || Configuration::get('PAYSHOP_GOOGLEPAY_SERVICE_UUID')
            || Configuration::get('PAYSHOP_APPLEPAY_SERVICE_UUID')
            || Configuration::get('PAYSHOP_PAYPAL_SERVICE_UUID')
            || Configuration::get('PAYSHOP_CLICKTOPAY_SERVICE_UUID')
        ;
    }
}
