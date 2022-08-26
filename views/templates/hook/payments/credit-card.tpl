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

<form id="payshop_card" class="payshop-checkout-form" method="post" action="{$formAction|escape:'htmlall':'UTF-8'}">
    <div class="row payshop-frame-checkout-custom-seven">

        <!-- Title enter your card details -->
        <div id="payshop-form" class="col-xs-12 col-md-12 col-12">
            <h3 class="payshop-title-custom-checkout payshop-pt-10">{l s='Enter your card details' mod='payshop'}</h3>

            <!-- Input Card number -->
            <div class="form-group">
                <div class="col-md-12 col-12 payshop-pb-10 payshop-px-0 payshop-m-col">
                    <label for="id-card-number" class="payshop-pb-5">
                        {l s='Card number' mod='payshop'}
                        <em class="payshop-required">*</em>
                    </label>
                    <input
                        required
                        id="id-card-number"
                        name="card-number"
                        class="form-control payshop-form-control"
                        type="text"
                        maxlength="19"
                        autocopayshoplete="off"
                        data-checkout="cardNumber"
                        onkeyup="payshop_maskInput(this, payshop_cc);"
                    />
                    <small id="payshop-error-card-number" class="payshop-erro-form" data-main="#id-card-number" style="display: none; color: #e81814;">
                        {l s='Invalid card number' mod='payshop'}
                    </small>
                </div>
            </div>

            <!-- Input Name and Surname -->
            <div id="payshop-card-holder-div" class="form-group">
                <div class="col-md-12 col-12 payshop-pb-10 payshop-px-0 payshop-m-col">
                    <label for="id-card-holder-name" class="payshop-pb-5">
                        {l s='Name and surname of the cardholder' mod='payshop'}
                        <em class="payshop-required">*</em>
                    </label>
                    <input
                        required
                        id="id-card-holder-name"
                        name="card-holder-name"
                        class="form-control payshop-form-control"
                        type="text"
                        autocopayshoplete="off"
                        data-checkout="cardholderName"
                    />
                    <small id="payshop-error-card-holder-name" class="payshop-erro-form" data-main="#id-card-holder-name" style="display: none; color: #e81814;">
                        {l s='Invalid card holder name' mod='payshop'}
                    </small>
                </div>
            </div>

            <div class="form-group">
                <!-- Input expiration date -->
                <div class="col-md-6 col-6 payshop-m-pb-20 payshop-pl-0 payshop-m-col">
                    <label for="id-card-expiration" class="payshop-pb-5">
                        {l s='Expiration date' mod='payshop'}
                        <em class="payshop-required">*</em>
                    </label>
                    <input
                        required
                        id="id-card-expiration"
                        name="card-expiration"
                        class="form-control payshop-form-control"
                        type="text"
                        autocopayshoplete="off"
                        placeholder="MM/AAAA"
                        maxlength="7"
                        data-checkout="cardExpiration"
                        onkeyup="payshop_maskInput(this, payshop_date);"
                    />

                    <small id="payshop-error-card-expiration" class="payshop-erro-form" data-main="#id-card-expiration" style="display: none; color: #e81814;">
                        {l s='Invalid card expiration date' mod='payshop'}
                    </small>
                </div>

                <!-- Input Security Code -->
                <div class="col-md-6 col-6 payshop-m-pb-20 payshop-pr-0 payshop-m-col">
                    <label for="id-security-code" class="payshop-pb-5">
                        {l s='Security code' mod='payshop'}
                        <em class="payshop-required">*</em>
                    </label>
                    <input
                        required
                        id="id-security-code"
                        name="card-security-code"
                        class="form-control payshop-form-control"
                        type="text"
                        autocopayshoplete="off"
                        placeholder="{l s='CVV' mod='payshop'}"
                        maxlength="3"
                        data-checkout="securityCode"
                        onkeyup="payshop_maskInput(this, payshop_minteger);"
                    />
                    <small class="payshop-small payshop-pt-5">
                        {l s='last 3 numbers on the back of your card' mod='payshop'}
                    </small>

                    <small id="payshop-error-security-code" class="payshop-erro-form payshop-pt-0" data-main="#id-security-code" style="display: none; color: #e81814;">
                        {l s='Invalid Security code' mod='payshop'}
                    </small>
                </div>
            </div>

            <div class="col-md-12 col-xs-12 col-12 payshop-px-0 payshop-m-col">
                <p class="payshop-all-required" style="color: red">
                    <em class="payshop-required text-bold">*</em> {l s='Obligatory field' mod='payshop'}
                </p>
            </div>
        </div>
    </div>
</form>

<script type="text/javascript" src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/js/jquery-1.11.0.min.js"></script>
<script type="text/javascript" src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/js/payshop.js?v=1"></script>
