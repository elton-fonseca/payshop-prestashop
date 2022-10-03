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

<form id="payshop_wbway" class="payshop-checkout-form" method="post" action="{$formAction|escape:'htmlall':'UTF-8'}">
    <div class="row payshop-frame-checkout-custom-seven">

        <!-- Title enter your MBWay details -->
        <div id="payshop-form" class="col-xs-12 col-md-12 col-12">
            <h3 class="payshop-title-custom-checkout payshop-pt-10">{l s='Enter your MBWay account details' mod='payshop'}</h3>

            <!-- Input MBWay Phone Number -->
            <div class="form-group">
                <div class="col-md-12 col-12 payshop-pb-10 payshop-px-0 payshop-m-col">
                    <label for="id-phone-number" class="payshop-pb-5">
                        {l s='Phone number' mod='payshop'}
                        <em class="payshop-required">*</em>
                    </label>
                    <input
                        required
                        id="id-phone-number"
                        name="phone-number"
                        class="form-control payshop-form-control"
                        type="text"
                        maxlength="19"
                        autocopayshoplete="off"
                        data-checkout="wbwayNumber"
                        {* onkeyup="payshop_maskInput(this, payshop_cc);" *}
                    />
                    <small id="payshop-error-phone-number" class="payshop-erro-form" data-main="#id-phone-number" style="display: none; color: #e81814;">
                        {l s='Invalid MBWay Phone Number' mod='payshop'}
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
<script type="text/javascript" src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/js/payshop.js?v=2"></script>
