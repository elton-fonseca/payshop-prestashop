{*
* 2007-2025 PrestaShop
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
*  @copyright 2007-2025 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<!-- Alert -->
{if $message != ''}
    <div class='alert {$alert|escape:'html':'UTF-8'} alert-dismissible'>
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
        {$message|escape:'html':'UTF-8'}
    </div>
{/if}

{if $currency != 'EUR'}
    <div class='alert alert-warning alert-dismissible'>
        <button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
        {l s='Your Default currency is ' mod='payshop'}
        {$currency|escape:'html':'UTF-8'}.
        {l s=' Payshop works only with EUR!' mod='payshop'}
    </div>
{/if}

<!-- Nav tabs -->
<ul class="nav nav-tabs" role="tablist">
    <li class="active"><a href="#template_1" role="tab" data-toggle="tab" id="a_template_1">{l s='Set Up Payshop' mod='payshop'}</a></li>
    <li><a href="#template_3" role="tab" data-toggle="tab">{l s='Plugin Log' mod='payshop'}</a></li>
    <li class="mp-plugin-version"><a>{l s='Current version:' mod='payshop'} <span>v{$payshop_version|escape:'html':'UTF-8'}</span></a></li>
</ul>

<!-- Tab panes -->
<div class="tab-content">
    <div class="tab-pane active" id="template_1">
        <div class="panel mp-panel-payshop">
            <div class="row header-payshop">
                <div class="mp-left-details">
                    <h2 class="mp-title-checkout-header">{l s='Design the best payment experience for your customers' mod='payshop'}</h2>
                </div>
                <div class="mp-right-details">
                    <img src="{$module_dir|escape:'html':'UTF-8'}views/img/payshop-logo.png" class="img-fluid header-mp-logo" id="payment-logo" />
                </div>
            </div>

            <hr />

            <div class="payshop-content">
                <div class="row">
                    <div class="col-md-8">
                        <p class="text-branded lists-how-configure">
                            {l s='Credentials are the keys we provide you to integrate quickly and securely.' mod='payshop'}
                            {l s='You must have an account in Payshop to collect on your website.' mod='payshop'}
                            {l s='You don`t need to know how to design or program to activate us in your store. ' mod='payshop'}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <!-- forms rendered via class from payshop -->
        {html_entity_decode($credentialsForm|escape:'html':'UTF-8')}
        {html_entity_decode($paymentsForm|escape:'html':'UTF-8')}
    </div>
    <div class="tab-pane" id="template_3">
        <div class="panel">
            <div class="panel-heading">
                <i class="icon-cogs"></i> {l s='Logging' mod='payshop'}
            </div>

            <div class="payshop-content">
                <div class="row">
                    <div class="col-md-12">
                        <h4 class="mp-title-checkout-body">{l s='Here you can see Payshop\'s log.' mod='payshop'}</h4>
                    </div>
                </div>

                <div class="row mp-pt-15">
                    <div class="col-md-12">
                        <p class="mp-text-credenciais">
                            {l s='Only for support reasons. Does not share this with unauthorized people!' mod='payshop'}
                        </p>
                    </div>
                </div>

                <div class="row mp-pt-25">
                    <div class="col-xs-12">
                        <a href="{$log|escape:'html':'UTF-8'}" target="_blank" class="btn btn-default mp-btn-credenciais">
                            {l s='See log' mod='payshop'}
                        </a>
                    </div>
                </div>
            </div>
        </div>    
    </div>
</div>

<!-- JavaScript -->
<script type="text/javascript">
    window.onload = function() {
        var element = document.querySelectorAll("#module_form");
        for (var i=0; i < element.length; i++) {
            element[i].id = "module_form_" + i;
        }

        // ----- credentials form ----- //
        var form_credentials_prepend = document.createElement("div");
        var form_credentials = document.querySelector("#module_form_0 .panel .form-wrapper");
        var form_credentials_inputs = document.querySelectorAll("#module_form_0 .panel .form-wrapper .form-group");

        form_credentials_prepend.innerHTML = "<div class='row'>\
            <div class='col-md-12'>\
                <h4 class='mp-title-checkout-body'>{l s='Enter your credentials and choose how to operate' mod='payshop'}</h4>\
            </div>\
        </div>\
        <div class='row mp-pt-5 mp-pb-30'>\
            <div class='col-md-12'>\
                <p class='mp-text-credenciais'><b>{l s='Test Mode' mod='payshop'}</b></p>\
                <p class='mp-text-credenciais'>{l s='By default, we leave the test environment (Sandbox) active for you to test before you start selling.' mod='payshop'}</p>\
                <p class='mp-text-credenciais mp-pt-15'><b>{l s='Production Mode' mod='payshop'}</b></p>\
                <p class='mp-text-credenciais'>{l s='When you see that everything is going well, disable Sandbox to go to Production and make way for your online sales.' mod='payshop'}</p>\
            </div>\
        </div>";
        form_credentials.insertBefore(form_credentials_prepend, form_credentials.firstChild);

        var form_credentials_pruebas_append = "<div class='row mp-pt-20 mp-mb-15'>\
            <div class='col-md-12'>\
                <p class='mp-title-credenciais'>{l s='Test Credentials' mod='payshop'}</p>\
                <p class='mp-text-credenciais mp-pt-5 mp-pb-10'>{l s='With these keys you can do the tests you want' mod='payshop'}</p>\
            </div>\
        </div>";

        var form_credentials_produccion_append = "<div class='row mp-pt-20 mp-mb-15'>\
            <div class='col-md-12'>\
                <p class='mp-title-credenciais'>{l s='Production Credentials' mod='payshop'}</p>\
                <p class='mp-text-credenciais mp-pt-5 mp-pb-10'>{l s='With these keys you can receive real payments from your customers.' mod='payshop'}</p>\
            </div>\
        </div>";

        for (var i=0; i < form_credentials_inputs.length; i++) {
            if(i == 1){
                form_credentials_inputs[i].insertAdjacentHTML('afterend', form_credentials_produccion_append);
            }
            else if(i == 4){
                form_credentials_inputs[i].insertAdjacentHTML('afterend', form_credentials_pruebas_append);
            }
        }

    }

  
</script>
