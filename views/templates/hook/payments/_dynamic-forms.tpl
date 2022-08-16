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
<div class="overlap" id="payshop-df">
    <div class="overlap-content">
        <div class="alert alert-danger" style="display: none" id="payment-information-error">
            {l s='Oops! There was an error in payment, attempted ...' mod='payshop'}
        </div>
        <div id="dynamic-forms-container"></div>
    </div>
</div>

<style>
    #payshop-df {
        display: none;
    }

    #payshop-df .overlap-content {
        width: 80%;
        min-width: 320px;
        justify-content: center;
        height: auto;
    }
</style>
