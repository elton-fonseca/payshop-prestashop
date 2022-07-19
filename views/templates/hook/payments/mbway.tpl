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
            <h3 class="payshop-title-custom-checkout payshop-pt-20">{l s='Enter your MBWay account details' mod='payshop'}</h3>

            <!-- Input MBWay Phone Number -->
            <div class="form-group">
                <div class="col-md-12 col-12 mb-1">
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

<div class="overlap" id="waiting-mbway">
    <div class="overlap-content">
        <h5>Aguardando confirmação de pagamento no WBWay</h5>
        <img width="250" src="https://www.ingresso-minsa.ao/cminsa_saude/public/assets/admin/images/loading.gif" />
        <p>Você pode confirmar o pagamento mesmo se fechar essa página. Nesse caso receberá a confirmação de pagamento por email</p>
    </div>
</div>

<style>
    .payshop-form-control-error {
        border: 2px solid #eb5a5a !important;
    }

    .overlap {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 99999;
        display: none;
    }

    .overlap-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 300px;
        height: 423px;
        background: #fff;
        z-index: 999999;
        padding: 20px;
        border-radius: 5px;
        text-align: center;

        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .overlap-content p {
        margin-bottom: 0;
        margin-top: 10px;
    }

</style>

<script type="text/javascript" src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/js/jquery-1.11.0.min.js"></script>
<script type="text/javascript" src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/js/mbway.js?v=23"></script>
