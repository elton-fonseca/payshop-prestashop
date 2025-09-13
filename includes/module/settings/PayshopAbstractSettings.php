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
class PayshopAbstractSettings
{
    public $form;
    public $module;
    public $values;
    public $submit;
    public $process;
    protected $validate;

    private $payshopPaymentServiceUUID;

    public function __construct()
    {
        $this->module = Module::getInstanceByName('payshop');
        $this->payshopPaymentServiceUUID = new PayshopPaymentServiceUUID();
    }

    /**
     * Build Config Form
     *
     * @return array
     */
    public function buildForm($title, $fields)
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $title,
                    'icon' => 'icon-cogs',
                ],
                'class' => 'credentials',
                'input' => $fields,
                'submit' => [
                    'title' => $this->module->l('Save', 'PayshopAbstractSettings'),
                ],
            ],
        ];
    }

    /**
     * Verify form submit
     *
     * @return bool|null
     */
    public function verifyPostProcess()
    {
        if (((bool) Tools::isSubmit($this->submit)) == true) {
            return $this->postFormProcess();
        }

        return null;
    }

    /**
     * Save form data
     *
     * @return bool|void
     */
    public function postFormProcess()
    {
        $isValid = true;

        foreach (array_keys($this->values) as $key) {
            $value = htmlentities(strip_tags(Tools::getValue($key)), ENT_QUOTES, 'UTF-8');

            if (!$this->validateInput($key, $value)) {
                $isValid = false;
                continue;
            }

            $this->values[$key] = $value;
            Configuration::updateValue($key, $value);
        }

        if ($isValid) {
            try {
                $this->payshopPaymentServiceUUID->execute();
            } catch (Exception $e) {
                Payshop::$form_alert = 'alert-danger';
                Payshop::$form_message = $this->module->l('Click on the save button again or check your credentials', 'PayshopAbstractSettings');

                Configuration::updateValue('PAYSHOP_CREDIT_CARD', null);
                Configuration::updateValue('PAYSHOP_CARD_SERVICE_UUID', null);
                Configuration::updateValue('PAYSHOP_MBWAY', null);
                Configuration::updateValue('PAYSHOP_MBWAY_SERVICE_UUID', null);
                Configuration::updateValue('PAYSHOP_PAYSHOP_REFERENCE', null);
                Configuration::updateValue('PAYSHOP_REFERENCE_SERVICE_UUID', null);
                Configuration::updateValue('PAYSHOP_MULTIBANCO_REFERENCE', null);
                Configuration::updateValue('PAYSHOP_MULTIBANCO_REFERENCE_SERVICE_UUID', null);
                Configuration::updateValue('PAYSHOP_GOOGLEPAY', null);
                Configuration::updateValue('PAYSHOP_GOOGLEPAY_SERVICE_UUID', null);
                Configuration::updateValue('PAYSHOP_APPLEPAY', null);
                Configuration::updateValue('PAYSHOP_APPLEPAY_SERVICE_UUID', null);
                Configuration::updateValue('PAYSHOP_PAYPAL', null);
                Configuration::updateValue('PAYSHOP_PAYPAL_SERVICE_UUID', null);
                Configuration::updateValue('PAYSHOP_CLICKTOPAY', null);
                Configuration::updateValue('PAYSHOP_CLICKTOPAY_SERVICE_UUID', null);

                return false;
            }

            Payshop::$form_alert = 'alert-success';
            Payshop::$form_message = $this->module->l('Settings saved successfully! Active your payment methods now.', 'PayshopAbstractSettings');
        }
    }

    /**
     * Validate input for submit
     *
     * @param mixed $input
     * @param mixed $value
     *
     * @return bool
     */
    public function validateInput($input, $value)
    {
        if ($this->validate != null && array_key_exists($input, $this->validate)) {
            switch ($this->validate[$input]) {
                case 'api_key':
                    if ($value == '') {
                        Payshop::$form_alert = 'alert-danger';
                        Payshop::$form_message = $this->module->l('Credentials can not be empty and must be valid. ', 'PayshopAbstractSettings') .
                        $this->module->l('Please complete your credentials to enable the module.', 'PayshopAbstractSettings');

                        return false;
                    }
                    break;

                case 'signature':
                    if ($value == '') {
                        Payshop::$form_alert = 'alert-danger';
                        Payshop::$form_message = $this->module->l('Credentials can not be empty and must be valid. ', 'PayshopAbstractSettings') .
                        $this->module->l('Please complete your credentials to enable the module.', 'PayshopAbstractSettings');

                        return false;
                    }
                    break;
                case 'client_uuid':
                    if ($value == '') {
                        Payshop::$form_alert = 'alert-danger';
                        Payshop::$form_message = $this->module->l('Credentials can not be empty and must be valid. ', 'PayshopAbstractSettings') .
                        $this->module->l('Please complete your credentials to enable the module.', 'PayshopAbstractSettings');

                        return false;
                    }
                    break;
                default:
                    return true;
            }
        }

        return true;
    }
}
