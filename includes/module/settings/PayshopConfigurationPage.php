<?php

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

        $credentials = new CredentialsSettings();
        $payments = new PaymentsSettings();

        $credentialsForm = $this->renderSettingsForm($credentials->submit, $credentials->values, $credentials->form);
        $paymentsForm = $this->renderSettingsForm($payments->submit, $payments->values, $payments->form);

        //variables for admin configuration
        $public_key = Configuration::get('PAYSHOP_PUBLIC_KEY');
        $access_token = Configuration::get('PAYSHOP_ACCESS_TOKEN');
        $sandbox_public_key = Configuration::get('PAYSHOP_SANDBOX_PUBLIC_KEY');
        $sandbox_access_token = Configuration::get('PAYSHOP_SANDBOX_ACCESS_TOKEN');

        $output = $this->context->smarty->assign(
            array(
                //module requirements
                'alert' => Payshop::$form_alert,
                'message' => Payshop::$form_message,
                'payshop_version' => PAYSHOP_VERSION,
                'url_base' => __PS_BASE_URI__,
                'log' => PayshopLog::getLogUrl(),
                //credentials
                'public_key' => $public_key,
                'access_token' => $access_token,
                'sandbox_public_key' => $sandbox_public_key,
                'sandbox_access_token' => $sandbox_access_token,
                //forms
                'credentialsForm' => $credentialsForm,
                'paymentsForm' => $paymentsForm,
                //currencies
                'currency' => $this->context->currency->iso_code,

                'mailTemplatesCopied' => $this->mailTemplatesCopied()
            )
        )->fetch($this->local_path . 'views/templates/admin/configurations.tpl');

        return $output;
    }

    /**
     * Load settings class files
     *
     * @return void
     */
    private function loadSettingsFiles()
    {
        include_once PAYSHOP_ROOT_URL . '/includes/module/settings/PayshopCredentialsSettings.php';
        include_once PAYSHOP_ROOT_URL . '/includes/module/settings/PayshopPaymentsSettings.php';
    }

    /**
     * Render forms
     *
     * @param  $submit
     * @param  $values
     * @param  $form
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

        $helper->tpl_vars = array(
            'fields_value' => $values,
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($form));
    }

    /**
     * Check if the mail templates are copied
     *
     * @return bool
     */
    private function mailTemplatesCopied()
    {
        $allCopied = true;

        foreach ($this->mailsLangs as $lang) {
            foreach ($this->mailsTemplate as $template) {
                $path = $this->pathDir . "/../../mails/$lang/" . $template;

                if (!file_exists($path)) {
                    $allCopied = false;
                    break;
                }
            }
        }

        return $allCopied;
    }
}