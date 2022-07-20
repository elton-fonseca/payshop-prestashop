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

/* global Mercadopago, Option, jQuery, $ */
/* eslint no-return-assign: 0 */

(function () {
  /**
   * Handle submit from form
   */
  jQuery(function () {
    if (document.forms.payshop_payshop_reference !== undefined) {
      document.forms.payshop_payshop_reference.onsubmit = function () {
        createTransaction();

        return false;
      };
    }
  });

  function createTransaction(){

    var mbWayURL = document.getElementById('payshop_payshop_reference').action;

    $.ajax({
        url: mbWayURL,
        type: 'POST',
        success: function (data) {
          document.getElementById('payshop-reference-reference').innerHTML = data.reference;
          document.getElementById('payshop-reference-value').innerHTML = '€' + data.value;
          document.getElementById('payshop-reference-end-date').innerHTML = data.end_date;
          showPayshopReference();
        },
        error: function (data) {
          showPayshopReferenceError();
        }
    });
  }

  function showPayshopReference(display = 'block'){
    let PayshopReferencePopup = document.getElementById('payshop-reference');
    PayshopReferencePopup.style.display = display;
  }

  function showPayshopReferenceError() {
    let PayshopReferenceErrorPopup = document.getElementById('payshop-reference-error');
    PayshopReferenceErrorPopup.style.display = 'block';
  }

})();
