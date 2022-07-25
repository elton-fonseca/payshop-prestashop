{*
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
* @author PrestaShop SA <contact@prestashop.com>
* @copyright 2007-2022 PrestaShop SA
* @license http://opensource.org/licenses/afl-3.0.php Academic Free License (AFL 3.0)
* International Registered Trademark & Property of PrestaShop SA
*}

<form id="payshop_dynamic_forms" method="post" action="{$formAction|escape:'htmlall':'UTF-8'}">
    <div class="alert alert-danger" style="display: none" id="payment-information-error">
        {l s='Payment information error' mod='payshop'}
    </div>
    <div id="dynamic-forms-container"></div>
</form>
<div>
{include file=$mbwayView}
{include file=$referencesView}
</div>

<script src="https://cdn.switchpayments.com/libs/switch-5.stable.min.js"></script>

<script>
    let baseUrl = document.getElementById('payshop_dynamic_forms').action;

    let CreateChargeUrl = baseUrl.replace('ControlerName', 'CreateCharge');

    let formContainer = document.getElementById("dynamic-forms-container");
    let formOptions = {
        chargesUrl: CreateChargeUrl,
        merchantTransactionId: "{$cartId|escape:'htmlall':'UTF-8'}",
        iframe: false,
        chargeTypes: {$enablePaymentMethods nofilter},
    };

    let isProduction = {$isProduction|escape:'htmlall':'UTF-8'};

    let switchJs = new SwitchJs(
        isProduction ? SwitchJs.environments.LIVE : SwitchJs.environments.TEST, 
        "{$publicKey|escape:'htmlall':'UTF-8'}"
    );

    let form = switchJs.dynamicForms(formContainer, formOptions);

    let buttonError = "{l s='You need use Payshop CONFIRM button to pay' mod='payshop'}";
</script>

<style>

</style>

<script type="text/javascript" src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/js/jquery-1.11.0.min.js"></script>
<script type="text/javascript" src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/js/payshop.js?v=1"></script>
