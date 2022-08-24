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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2022 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

<!-- Alert -->
{if ($hasMessage)}
<div class="container">
    <div class="row">
        <div class="col-md-12">
            <div class='alert alert-danger alert-dismissible'>
                <button type='button' class='close' data-dismiss='alert' aria-label='Close'><span aria-hidden='true'>&times;</span></button>
                {if !empty($message)}
                    {$message|escape:'htmlall':'UTF-8'}
                {else}
                    {l s='Oops! There was an error in payment, attempted ...' mod='payshop'}
                {/if}
            </div>
        </div>
    </div>
</div>
{/if}

<!-- popup references -->
<div class="overlap" id="payshop-reference">
    <div class="overlap-content">
            <h3>{l s='Confirmed Order' mod='payshop'}</h3>
        <div>
            <h5>{l s='Reference Informations' mod='payshop'}</h5>
            <p id="multibanco-entity">
                {l s='Entity: ' mod='payshop'}
                <strong id="payshop-reference-entity"></strong>
            </p>
            <p>
                {l s='Reference: ' mod='payshop'}
                <strong id="payshop-reference-reference"></strong>
            </p>
            <p>
                {l s='Value: ' mod='payshop'}
                <strong id="payshop-reference-value"></strong>
            </p>
            <p>
                {l s='End Date: ' mod='payshop'}
                <strong id="payshop-reference-end-date"></strong>
            </p> 
        </div>
        <button id="payshop-reference-view-order">
            {l s='View Order Details' mod='payshop'}
        </button>
    </div>
</div>

<!-- popup loading -->
<div class="overlap" id="payshop-loading">
    <div class="overlap-content">
        <img width="250" src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/img/loading.gif" />
    </div>
</div>

<style>
    #payshop-loading div {
        height: 293px;
    }

    .payshop-form-control-error {
        border: 2px solid #eb5a5a !important;
    }

    .payshop-error-color {
        color: #eb5a5a;
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
        background: #fff;
        z-index: 999999;
        padding: 20px;
        border-radius: 5px;
        text-align: center;

        display: flex;
        flex-direction: column;
        justify-content: space-between;
        gap: 32px;
    }

    .overlap-content p {
        margin-bottom: 0;
        margin-top: 10px;
    }

    #multibanco-entity {
        display: none;
    }
</style>
