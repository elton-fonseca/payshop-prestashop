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

(function () {
  let baseUrl = '';

  /**
   * Show or hide the elemtent by ID
   */
  function displayById(elementId, display = 'block') {
    let element = document.getElementById(elementId);
    element.style.display = display;
  }

  /**
   * create charge and show loading popup
   * function is called when the user submit the payment form
   */
  function createCharge(paymentType) {
    displayById('payshop-loading');

    let createChargeUrl = baseUrl.replace('ControlerName', 'CreateCharge');

    $.ajax({
      url: createChargeUrl,
      type: 'POST',
      data: JSON.stringify(paymentType),
      success: function (charge) {
        createInstrument(charge.id, paymentType.chargeType);
      }
    });
  }

  /**
   * Create instrument
   */
  function createInstrument(charge, paymentType) {
    let createInstrumentUrl = baseUrl.replace('ControlerName', 'CreateInstrument');
    let PaymentInformation = {
      paymentType,
      charge,
      ...getCardInformation(paymentType),
      ...getMbwayInformation(paymentType)
    }

    $.ajax({
      url: createInstrumentUrl,
      type: 'POST',
      data: JSON.stringify(PaymentInformation),
      success: function (instrument) {
        processInstrument(instrument);
      }, 
      error: function (error) {
        window.location.href = error.responseJSON.errorRedirectUrl;
      }
    });

    /**
     * Get card informations
     */
    function getCardInformation(paymentType) {
      let PaymentInformation = {};

      if (paymentType === 'card') {
        PaymentInformation["number"] = document.getElementById('id-card-number').value.replace(/\s/g, '');
        PaymentInformation["name"] = document.getElementById('id-card-holder-name').value;

        let expiry = document.getElementById('id-card-expiration').value.split('/');
        PaymentInformation["expiration_month"] = expiry[0];
        PaymentInformation["expiration_year"] = expiry[1];

        PaymentInformation["cvc"] = document.getElementById('id-security-code').value;
      }

      return PaymentInformation;
    }

    /**
     * Get mbway informations
     */
    function getMbwayInformation() {
      let PaymentInformation = {};

      if (paymentType === 'mbway') {
        PaymentInformation["phone"] = document.getElementById('id-phone-number').value;
      }

      return PaymentInformation;
    }
  }

  /**
   * Process created instrument
   */
  function processInstrument(instrument) {
    if (cardWith3ds(instrument)) {
      return;
    }

    let processInstrumentUrl = baseUrl.replace('ControlerName', 'ProcessInstrument');

    $.ajax({
      url: processInstrumentUrl,
      type: 'POST',
      data: JSON.stringify(instrument),
      success: function (data) {
        displayById('payshop-loading', 'none');

        cardWithout3ds(instrument, data);
        processMBWay(instrument, data);
        processPaymentWithRecerence(instrument, data);
      },
      error: function (error) {
        window.location.href = error.responseJSON.errorRedirectUrl;
      }
    });

    /**
    * Process Card with 3DS
    */
    function cardWith3ds(instrument) {
      let isCard = instrument.charge.charge_type === 'card';

      if (isCard) {
        let isPending = instrument.status === 'pending';
        let has3dsRedirectUrl = instrument.redirect?.url ?? false;

        if (isPending && has3dsRedirectUrl) {
          window.location.href = instrument.redirect.url
          return true;
        }
      }

      return false;
    }

   /**
   * Process Card without 3DS
   */
    function cardWithout3ds(instrument, data) {
      if (instrument.charge.charge_type === 'card') {
        window.location.href = data.successRedirectUrl;
      }
    }

    /**
     * Process MBWay's instrument
     */
    function processMBWay(instrument, data) {
      if (instrument.charge.charge_type === 'mbway') {
        displayById('waiting-mbway');

        checkPayment(data.prestashopOrderId, data.successRedirectUrl);
      }
    }

    /**
     * Check MBWay payment status on backend
     */
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
            if (data.status == 'paid') {
              clearInterval(interval);
              window.location.href = sucessRedirectUrl;
            }

            if (data.status == 'declined') {
              displayById('waiting-mbway', 'none');
              
              displayById('declined-mbway');
            }
          }
        });
      }, 3000);
    }

    /**
     * Show payment with reference
     */
    function processPaymentWithRecerence(instrument, data) {
      if (instrument.charge.charge_type === 'multibanco') {
        displayById('multibanco-entity');
        document.getElementById('payshop-reference-entity').innerHTML = data.entity;
      }

      if (instrument.charge.charge_type === 'multibanco' || instrument.charge.charge_type === 'payshop_reference') {
        displayById('payshop-reference');

        document.getElementById('payshop-reference-reference').innerHTML = data.reference;
        document.getElementById('payshop-reference-value').innerHTML = '€' + data.value;
        document.getElementById('payshop-reference-end-date').innerHTML = data.end_date;

        document.getElementById('payshop-reference-view-order').addEventListener('click', function () {
            window.location.href = data.successRedirectUrl;
        });
      }
    }
  };

  /**
   * Focus input with error
   */
  function focusInputError() {
    if (document.querySelectorAll('.payshop-form-control-error') !== undefined) {
      var formInputs = document.querySelectorAll('.payshop-form-control-error');
      formInputs[0].focus();
    }
  }

  /**
   * Disable error spans
   */
  function hideErrors() {
    for (var x = 0; x < document.querySelectorAll('[data-checkout]').length; x++) {
      var field = document.querySelectorAll('[data-checkout]')[x];
      field.classList.remove('payshop-form-control-error');
    }

    for (var y = 0; y < document.querySelectorAll('.payshop-erro-form').length; y++) {
      var small = document.querySelectorAll('.payshop-erro-form')[y];
      small.style.display = 'none';
    }
  }

  /**
   * Get condition terms input on PS17
   */
  function uncheckConditionTerms() {
    var terms = document.getElementById('conditions_to_approve[terms-and-conditions]');
    if (typeof terms === 'object' && terms !== null) {
      terms.checked = false;
      return terms.checked;
    }
  }

 /**
 * Disable finish order button
 */
  function disableFinishOrderButton() {
    var sevenButton = document.getElementById('payment-confirmation').childNodes[1].childNodes[1];
    sevenButton.setAttribute('disabled', 'disabled');
  }

  /**
   * Card Scope
   */
  (function () {
    /**
     * Get form
     */
    function getCardForm() {
      return document.querySelector('#payshop_card');
    }

    /**
     * Validate inputs
     */
    function validateInputs() {
      hideErrors();

      var inputsNotFilled = validateinputNotFilled();
      var numberIsInvalid = cardNumberIsInvalid();
      var expirationIsInvalid = expirationDateIsInvalid();
      var codeIsInvalid = cvvIsInvalid();

      if (inputsNotFilled || codeIsInvalid || expirationIsInvalid || numberIsInvalid) {
        focusInputError();
        return false;
      }

      return true;
    }

    /**
   * Validate card number length
   */
    function cardNumberIsInvalid() {
      var span = getCardForm().querySelectorAll('small[data-main="#id-card-number"]');
      var cvvInput = document.getElementById('id-card-number');
      var numberIsInvalid = cvvInput.value.length < 19;

      if (numberIsInvalid) {
        span[0].style.display = 'block';
        cvvInput.classList.add('payshop-form-control-error');
        cvvInput.focus();
      }

      return numberIsInvalid;
    }

    /**
     * Validate Expiration Date
     */
    function expirationDateIsInvalid() {
      var span = getCardForm().querySelectorAll('small[data-main="#id-card-expiration"]');
      var expirationInput = document.getElementById('id-card-expiration');

      //validate string length
      var invalidSize = expirationInput.value.length != 7;

      var expirationMonth = expirationInput.value.substring(0, 2) - 1;
      var expirationYear = expirationInput.value.substring(3, 7);

      //validate month
      var invalidMonth = expirationMonth < 0 || expirationMonth > 11;

      //validate year
      var invalidYear = expirationYear > new Date().getFullYear() + 15;

      //validate full date
      var lastDayOfPreviousMonth = new Date();
      lastDayOfPreviousMonth.setDate(0);

      var expirationDate = new Date(expirationYear, expirationMonth);

      var invalidDate = expirationDate < lastDayOfPreviousMonth;

      var invalid = invalidSize || invalidMonth || invalidYear || invalidDate;

      if (invalid) {
        span[0].style.display = 'block';
        expirationInput.classList.add('payshop-form-control-error');
        expirationInput.focus();
      }

      return invalid;
    }

    /**
     * Validate CVV length
     */
    function cvvIsInvalid() {
      var span = getCardForm().querySelectorAll('small[data-main="#id-security-code"]');
      var cvvInput = document.getElementById('id-security-code');
      var cvvIsInvalid = cvvInput.value.length < 3;

      if (cvvIsInvalid) {
        span[0].style.display = 'block';
        cvvInput.classList.add('payshop-form-control-error');
        cvvInput.focus();
      }

      return cvvIsInvalid;
    }

    /**
     * Validate fixed Inputs is empty
     */
    function validateinputNotFilled() {
      var emptyInputs = false;
      var form = getCardForm();
      var formInputs = form.querySelectorAll('[data-checkout]');
      var fixedInputs = ['cardNumber', 'cardholderName', 'cardExpiration', 'securityCode', 'installments'];

      for (var x = 0; x < formInputs.length; x++) {
        var element = formInputs[x];

        // Check is a input to create token.
        if (fixedInputs.indexOf(element.getAttribute('data-checkout')) > -1) {
          if (element.value === -1 || element.value === '') {
            var span = form.querySelectorAll('small[data-main="#' + element.id + '"]');

            if (span.length > 0) {
              span[0].style.display = 'block';
            }

            element.classList.add('payshop-form-control-error');
            emptyInputs = true;
          }
        }
      }

      return emptyInputs;
    }

    /**
     * Handle submit from credit card form
     */
    jQuery(function () {
      if (document.forms.payshop_card !== undefined) {
        document.forms.payshop_card.onsubmit = function () {
          if (!validateInputs()) {
            uncheckConditionTerms();
            disableFinishOrderButton();
            return false;
          }

          baseUrl = document.forms.payshop_card.action;
          createCharge({
            "chargeType": "card"
          });

          return false;
        };
      }
    });
  })();

/**
 * MBWay Scope
 */
  (function () {
    /**
     * Get form
     */
    function getMbwayForm() {
      return document.querySelector('#payshop_wbway');
    }

    /**
     * Validate inputs
     */
    function validateInputs() {
      hideErrors();

      var inputsNotFilled = validateinputNotFilled();

      if (inputsNotFilled) {
        focusInputError();
        return false;
      }

      return true;
    }

    /**
     * Validate fixed Inputs is empty
     */
    function validateinputNotFilled() {
      var emptyInputs = false;
      var form = getMbwayForm();
      var formInputs = form.querySelectorAll('[data-checkout]');
      var fixedInputs = ['wbwayNumber'];

      for (var x = 0; x < formInputs.length; x++) {
        var element = formInputs[x];

        // Check is a input to create token.
        if (fixedInputs.indexOf(element.getAttribute('data-checkout')) > -1) {
          if (element.value === -1 || element.value === '') {
            var span = form.querySelectorAll('small[data-main="#' + element.id + '"]');

            if (span.length > 0) {
              span[0].style.display = 'block';
            }

            element.classList.add('payshop-form-control-error');
            emptyInputs = true;
          }
        }
      }

      return emptyInputs;
    }


    /**
     * Handle submit from wbway form
     */
    jQuery(function () {
      if (document.forms.payshop_wbway !== undefined) {
        document.forms.payshop_wbway.onsubmit = function () {
          if (!validateInputs()) {
            uncheckConditionTerms();
            disableFinishOrderButton();
            return false;
          }

          baseUrl = document.forms.payshop_card.action;
          createCharge({
            "chargeType": "mbway"
          });

          return false;
        };
      }
    });
  })();

  /**
   * Payshop Scope
   */
  (function () {
    /**
     * Handle submit from payshop reference form
     */
    jQuery(function () {
      if (document.forms.payshop_payshop_reference !== undefined) {
        document.forms.payshop_payshop_reference.onsubmit = function () {

          baseUrl = document.forms.payshop_payshop_reference.action;
          createCharge({
            "chargeType": "payshop_reference"
          });

          return false;
        };
      }
    });
  })();

/**
 * Multibanco reference Scope
 */
  (function () {
    /**
     * Handle submit from multibanco reference form
     */
    jQuery(function () {
      if (document.forms.payshop_multibanco_reference !== undefined) {
        document.forms.payshop_multibanco_reference.onsubmit = function () {

          baseUrl = document.forms.payshop_multibanco_reference.action;
          createCharge({
            "chargeType": "multibanco"
          });

          return false;
        };
      }
    });
  })();
})();