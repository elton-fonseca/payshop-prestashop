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

<div class='alert alert-danger alert-dismissible'>
    <button type='button' class='close' data-dismiss='alert' aria-label='Close' id="alert-update-close">
        <span aria-hidden='true'>&times;</span>
    </button>
    {l s='A new version of the Payshop module is available. You can download the latest version from here:' mod='payshop'}
    <a href='#' class='alert-link' style="padding: 0" target="_blank">{l s='Download' mod='payshop'}</a>
</div>

<script>
    $('#alert-update-close').click(function () {
        $.ajax({
            url: '{$updateAlertCloseLink}',
            type: 'GET',
            success: function (data) {}
        });
    });
</script>
