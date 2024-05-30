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
 * MBWay Scope
 */
  (function () {
    /**
     * Get form
     */
    function getMbwayForm() {
      return document.querySelector('#payshop_mbway');
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
      if (document.forms.payshop_mbway !== undefined) {
        document.forms.payshop_mbway.onsubmit = function () {
          if (!validateInputs()) {
            uncheckConditionTerms();
            disableFinishOrderButton();
            return false;
          }

          return true;
        };
      }
    });
  })();
})();