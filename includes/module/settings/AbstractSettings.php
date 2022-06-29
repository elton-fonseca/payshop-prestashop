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

class AbstractSettings
{
    public $form;
    public $module;
    public $values;
    public $submit;
    public $process;
    protected $validate;

    public function __construct()
    {
        $this->module = Module::getInstanceByName('payshop');
    }

    /**
     * Build Config Form
     *
     * @return void
     */
    public function buildForm($title, $fields)
    {
        return array(
            'form' => array(
                'legend' => array(
                    'title' => $title,
                    'icon' => 'icon-cogs',
                ),
                'class' => 'credentials',
                'input' => $fields,
                'submit' => array(
                    'title' => $this->module->l('Save', 'AbstractSettings')
                ),
            ),
        );
    }

    /**
     * Verify form submit
     *
     * @return void
     */
    public function verifyPostProcess()
    {
        if (((bool) Tools::isSubmit($this->submit)) == true) {
            return $this->postFormProcess();
        }
    }

    /**
     * Save form data
     *
     * @return void
     */
    public function postFormProcess()
    {
        $form_alert = false;

        foreach (array_keys($this->values) as $key) {
            $value = htmlentities(strip_tags(Tools::getValue($key)), ENT_QUOTES, 'UTF-8');

            if (!$this->validateInput($key, $value)) {
                $form_alert = true;
                continue;
            }

            $this->values[$key] = $value;
            Configuration::updateValue($key, $value);
        }

        if ($form_alert == false) {
            Payshop::$form_alert = 'alert-success';
            Payshop::$form_message = $this->module->l('Settings saved successfully.', 'AbstractSettings');
        }
    }

    /**
     * Validate input for submit
     *
     * @param mixed $input
     * @return void
     */
    public function validateInput($input, $value)
    {
        if ($this->validate != null && array_key_exists($input, $this->validate)) {
            switch ($this->validate[$input]) {
                case "account_id":
                    if ($value == '') {
                        Payshop::$form_alert = 'alert-danger';
                        Payshop::$form_message = $this->module->l('Account ID is not filled. ', 'AbstractSettings');
                        PayshopLog::generate('Invalid ' . $input . ' submitted', 'warning');
                        return false;
                    }
                    break;
                    
                case "public_key":
                    if ($value == '') {
                        Payshop::$form_alert = 'alert-danger';
                        Payshop::$form_message = $this->module->l('Credentials can not be empty and must be valid. ', 'AbstractSettings') .
                        $this->module->l('Please complete your credentials to enable the module.', 'AbstractSettings');
                        PayshopLog::generate('Invalid ' . $input . ' submitted', 'warning');
                        return false;
                    }
                    break;

                case "secret_key":
                    if ($value == '') {
                        Payshop::$form_alert = 'alert-danger';
                        Payshop::$form_message = $this->module->l('Credentials can not be empty and must be valid. ', 'AbstractSettings') .
                        $this->module->l('Please complete your credentials to enable the module.', 'AbstractSettings');
                        PayshopLog::generate('Invalid ' . $input . ' submitted', 'warning');
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
