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

  var cvvLength = null;
  var submitted = false;

  /**
   * Initialise vars to use on JS custom-card.js
   *
   * @param {object} mpCustom
   */
  window.initializeCustom = function (mpCustom) {
    setChangeEventOnCardNumber();
  };


  /**
   * Set cvv length
   *
   * @param {number} length
   */
  function setCvvLength(length) {
    cvvLength = length;
  }

  /**
   * Get Amount end calculate discount for hide inputs
   */
  function getAmount() {
    return document.getElementById('amount').value;
  }

  /**
   * Set if the form has been submitted
   */
     function setFormSubmit() {
      submitted = true;
  }

 
 
  /**
   * Clear Inputs
   */
  function clearInputs() {
    hideErrors();
    clearTax();
    document.getElementById('id-card-number').style.background = 'no-repeat #fff';
    document.getElementById('id-card-expiration').value = '';
    document.getElementById('id-doc-number').value = '';
    document.getElementById('id-security-code').value = '';
    document.getElementById('id-card-holder-name').value = '';
  }


  /**
   * Clears card number input on keyup when there's less than 4 digits
   *
   */
  function setChangeEventOnCardNumber() {
    document.getElementById('id-card-number').addEventListener('keyup', function (e) {
      if (e.target.value.length <= 4) {
        clearInputs();
      }
    });
  }

  /**
   * Show errors
   *
   * @param  {object}  error
   */
  function showErrors(error) {
    var form = getCardForm();
    var serializedError = error.cause || error;

    for (var x = 0; x < serializedError.length; x++) {
      var code = serializedError[x].code;
      var span = undefined;

      if (code === '208' || code === '209' || code === '325' || code === '326') {
        span = form.querySelector('#mp-error-208');
      } else {
        span = form.querySelector('#mp-error-' + code);
      }

      if (span !== undefined) {
        span.style.display = 'block';
        form.querySelector(span.getAttribute('data-main')).classList.add('mp-form-control-error');
      }
    }

    focusInputError();
    getConditionTerms();
  }

  /**
   * Focus input with error
   *
   * @return bool
   */
  function focusInputError() {
    if (document.querySelectorAll('.mp-form-control-error') !== undefined) {
      var formInputs = document.querySelectorAll('.mp-form-control-error');
      formInputs[0].focus();
    }
  }

  /**
   * Disable error spans
   */
  function hideErrors() {
    for (var x = 0; x < document.querySelectorAll('[data-checkout]').length; x++) {
      var field = document.querySelectorAll('[data-checkout]')[x];
      field.classList.remove('mp-form-control-error');
    }

    for (var y = 0; y < document.querySelectorAll('.mp-erro-form').length; y++) {
      var small = document.querySelectorAll('.mp-erro-form')[y];
      small.style.display = 'none';
    }
  }

  /**
   * Get condition terms input on PS17
   */
  function getConditionTerms() {
    var terms = document.getElementById('conditions_to_approve[terms-and-conditions]');
    if (typeof terms === 'object' && terms !== null) {
      terms.checked = false;
      return terms.checked;
    }
  }

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

    var fixedInputs = validateFixedInputs();

    if (fixedInputs) {
      focusInputError();
      return false;
    }

    return true;
  }

  /**
   * Validate CVV length
   *
   * @returns {boolean}
   */
     function validateCvv() {
      var span = getCardForm().querySelectorAll('small[data-main="#id-security-code"]');
      var cvvInput = document.getElementById('id-security-code');
      var cvvValidation = cvvLength === cvvInput.value.length;

      if (!cvvValidation) {
        span[0].style.display = 'block';
        cvvInput.classList.add('mp-form-control-error');
        cvvInput.focus();
        getConditionTerms();
      }

      return cvvValidation;
    }

  /**
   * Validate fixed Inputs is empty
   *
   * @return bool
   */
  function validateFixedInputs() {
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

          element.classList.add('mp-form-control-error');
          emptyInputs = true;
        }
      }
    }

    return emptyInputs;
  }

 

  /**
   * Disable finish order button
   */
  function disableFinishOrderButton() {
      var sevenButton = document.getElementById('payment-confirmation').childNodes[1].childNodes[1];
      sevenButton.setAttribute('disabled', 'disabled');
  }

  

  /**
   * Handle submit from form
   */
  jQuery(function () {
    if (document.forms.payshop_card !== undefined) {
      document.forms.payshop_card.onsubmit = function () {
        alert('teste')
        if (validateInputs()) {
          return false;
        }

        // getConditionTerms();

        disableFinishOrderButton();
        return false;
      };
    }
  });
})();
