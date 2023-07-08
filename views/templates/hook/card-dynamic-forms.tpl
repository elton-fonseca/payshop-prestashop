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

{if ($isFailed)}
    <div>
        <h3 style="color: rgba(255, 0, 0, 0.666);">
            {l s='There was an error in the payment attempt. Please check your card limit or add another card.' mod='payshop'}
            
        </h3>
    </div>
{/if}

<div>
    <h3>
        {l s='Please, fill in your credit card details to complete the purchase.' mod='payshop'}
    </h3>
    <div id="dynamic-forms-container"></div>
</div>

<form action="{$createChargeURL|escape:'htmlall':'UTF-8'}" id="payshop_create_charge_url"></form>

<script src="https://cdn.switchpayments.com/libs/switch-5.stable.min.js"></script>

<script>
    let confirmationOrderPageURL = encodeURIComponent(window.location.href);
    let createChargeURL = new URL(document.forms.payshop_create_charge_url.action);
    createChargeURL.searchParams.set('confirmationOrderPageURL', confirmationOrderPageURL);

    let orderId = {$orderId|escape:'htmlall':'UTF-8'};

    let formContainer = document.getElementById("dynamic-forms-container");
    let formOptions = {
        chargesUrl: createChargeURL.toString(),
        merchantTransactionId: orderId,
        iframe: true,
        language: 'pt',
        chargeTypes: ['card'],
    };

    let isProduction = {$isProduction|escape:'htmlall':'UTF-8'};
    let publicKey = '{$publicKey|escape:'htmlall':'UTF-8'}';
    let switchJs = new SwitchJs(
        isProduction ? SwitchJs.environments.LIVE : SwitchJs.environments.TEST,
        publicKey
    );
    let form = switchJs.dynamicForms(formContainer, formOptions);

    form.on('instrument-invalid', (instrument) => {
        alert('{l s="Please check your card information and try again." mod="payshop"}');
    });
</script>
