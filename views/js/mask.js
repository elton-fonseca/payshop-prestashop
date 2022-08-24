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

    var payshop_vObj, payshop_vFun;

    // input mask
    // eslint-disable-next-line no-unused-vars
    function payshop_maskInput (o, f) {
    payshop_vObj = o;
    payshop_vFun = f;
    setTimeout(payshop_execmascara(), 1);
    }

    // eslint-disable-next-line no-unused-vars
    function payshop_execmascara () {
    payshop_vObj.value = payshop_vFun(payshop_vObj.value);
    }

    // eslint-disable-next-line no-unused-vars
    function payshop_date (v) {
    v = v.replace(/\D/g, '');
    v = v.replace(/(\d{2})(\d)/, '$1/$2');
    v = v.replace(/(\d{2})(\d{2})$/, '$1$2');
    return v;
    }

    // eslint-disable-next-line no-unused-vars
    function payshop_minteger (v) {
    return v.replace(/\D/g, '');
    }

    // eslint-disable-next-line no-unused-vars
    function payshop_cc (v) {
    v = v.replace(/\D/g, '');
    v = v.replace(/^(\d{4})(\d)/g, '$1 $2');
    v = v.replace(/^(\d{4})\s(\d{4})(\d)/g, '$1 $2 $3');
    v = v.replace(/^(\d{4})\s(\d{4})\s(\d{4})(\d)/g, '$1 $2 $3 $4');
    return v;
    }