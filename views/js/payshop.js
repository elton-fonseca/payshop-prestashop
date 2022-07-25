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

/**
 * Handle submit from form
 */
jQuery(function () {
  if (document.forms.payshop_dynamic_forms !== undefined) {
    document.forms.payshop_dynamic_forms.onsubmit = function () {
        alert(buttonError);

        return false;
    };
  }
});

form.on('instrument-pending', (instrument) => {
  let processInstrumentUrl = baseUrl.replace('ControlerName', 'ProcessInstrument');

  $.ajax({
    url: processInstrumentUrl,
    type: 'POST',
    data: JSON.stringify(instrument),
    success: function (data) {
      processMBWay(instrument, data);
      processPaymentWithRecerence(instrument, data);
    },
    error: function (data) {
      window.location.href = data.errorRedirectUrl;
    }
  });

  function processMBWay(instrument, data) {
    if (instrument.charge.charge_type === 'mbway') {
      waitingPayment();

      checkPayment(data.prestashopOrderId, data.successRedirectUrl);
    }
  }

  function checkPayment(prestashopOrderId, sucessRedirectUrl) {
    let MBWayPaidCheckUrl = baseUrl.replace('ControlerName', 'MBWayPaidCheck');

    var interval = setInterval(function () {
      $.ajax({
        url: MBWayPaidCheckUrl,
        type: 'POST',
        data: {
          "prestashop-order-id": prestashopOrderId
        },
        success: function (data) {
          console.log(data);
          if (data.status == 'paid') {
            clearInterval(interval);
            window.location.href = sucessRedirectUrl;
          }

          if (data.status == 'declined') {
            waitingPayment('none');
            
            let declinedMBWay = document.getElementById('declined-mbway');
            declinedMBWay.style.display = 'block';
          }
        }
      });
    }, 3000);
  }

  function waitingPayment(display = 'block') {
    let waitingPaymentPopup = document.getElementById('waiting-mbway');
    waitingPaymentPopup.style.display = display;
  }

  function processPaymentWithRecerence(instrument, data) {
    if (instrument.charge.charge_type === 'multibanco') {
      document.getElementById('multibanco-entity').style.display = 'block';
      document.getElementById('payshop-reference-entity').innerHTML = data.entity;
    }

    if (instrument.charge.charge_type === 'multibanco' || instrument.charge.charge_type === 'payshop_reference') {
      document.getElementById('payshop-reference-reference').innerHTML = data.reference;
      document.getElementById('payshop-reference-value').innerHTML = '€' + data.value;
      document.getElementById('payshop-reference-end-date').innerHTML = data.end_date;

      document.getElementById('payshop-reference-view-order').addEventListener('click', function () {
          window.location.href = data.successRedirectUrl;
      });

      let PayshopReferencePopup = document.getElementById('payshop-reference');
      PayshopReferencePopup.style.display = 'block';
    }
  }
});

form.on('instrument-invalid', (instrument) => {
  document.getElementById('payment-information-error').style.display = 'block';

  setTimeout(() => {
    document.getElementById('payment-information-error').style.display = 'none';
  }, 10000);
});

form.on('submit', (e) => {
  let acepted = document.getElementById('conditions_to_approve[terms-and-conditions]').checked;

  if (!acepted) {
    alert('You must accept the terms and conditions');
    //preciso parar o evento de submit
  }
});

