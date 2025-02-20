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

<link rel="stylesheet" href="{$moduleUrl|escape:'htmlall':'UTF-8'}views/css/wallets.css">

<div>
    <div class="overlap-wallets" id="googlepay-payshop">
        <div class="overlap-wallets-content">
          <button class="close-btn-wallets" onclick="document.getElementById('references-popup').style.display='none'">X</button>

          <h2>Realize o seu pagamento através do Google Pay</h2>
          <div id="google-pay-buttom"></div>
          <img src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/img/payshop-logo.png" alt="Payshop" width="150">
        </div>
    </div>

    <div class="flow-button-wallets">
      <img src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/img/googlepay.png" alt="Google Pay" width="70px">
    </div>
</div>

<script>
(function() {
    setTimeout(() => document.getElementById('googlepay-payshop').style.display = 'block', 2000);
})();

document.addEventListener('DOMContentLoaded', function() {
  document.querySelector('.flow-button-wallets').addEventListener('click', () => {
    document.getElementById('googlepay-payshop').style.display = 'block';
  });

  document.querySelector('.close-btn-wallets').addEventListener('click', () => {
    document.getElementById('googlepay-payshop').style.display = 'none';
  });
});
</script>


<script>
  /**
   * Define the version of the Google Pay API referenced when creating your
   * configuration
   *
   * @see link https://developers.google.com/pay/api/web/reference/request-objects#PaymentDataRequest|apiVersion in PaymentDataRequest
   */
  const baseRequest = {
    apiVersion: 2,
    apiVersionMinor: 0
  };

  /**
   * Card networks supported by your site and your gateway
   *
   * @see link https://developers.google.com/pay/api/web/reference/request-objects#CardParameters|CardParameters
   * @todo confirm card networks supported by your site and gateway
   */
  const allowedCardNetworks = ["MASTERCARD", "VISA"];

  /**
   * Card authentication methods supported by your site and your gateway
   *
   * @see link https://developers.google.com/pay/api/web/reference/request-objects#CardParameters|CardParameters
   * @todo confirm your processor supports Android device tokens for your
   * supported card networks
   */
  const allowedCardAuthMethods = ["PAN_ONLY", "CRYPTOGRAM_3DS"];

  /**
   * Identify your gateway and your site's gateway merchant identifier
   *
   * The Google Pay API response will return an encrypted payment method capable
   * of being charged by a supported gateway after payer authorization
   *
   * @todo check with your gateway on the parameters to pass
   * @see link https://developers.google.com/pay/api/web/reference/request-objects#gateway|PaymentMethodTokenizationSpecification
   */
  const tokenizationSpecification = {
    type: 'PAYMENT_GATEWAY',
    parameters: {
      "gateway": "paynopain",
      'gatewayMerchantId': 'BFR+318Qe36w59SkUl2ECMRNJAfeW1e+Mv5r41/598MQjYHdZ0GfNK5CrRfSKYBCFj8eN7Uz5VG9+MgOoOSbXR8='
    }
  };

  /**
   * Describe your site's support for the CARD payment method and its required
   * fields
   *
   * @see link https://developers.google.com/pay/api/web/reference/request-objects#CardParameters|CardParameters
   */
  const baseCardPaymentMethod = {
    type: 'CARD',
    parameters: {
      allowedAuthMethods: allowedCardAuthMethods,
      allowedCardNetworks: allowedCardNetworks
    }
  };

  /**
   * Describe your site's support for the CARD payment method including optional
   * fields
   *
   * @see link https://developers.google.com/pay/api/web/reference/request-objects#CardParameters|CardParameters
   */
  const cardPaymentMethod = Object.assign(
    {},
    baseCardPaymentMethod,
    {
      tokenizationSpecification: tokenizationSpecification
    }
  );

  /**
   * An initialized google.payments.api.PaymentsClient object or null if not yet set
   *
   * @see link getGooglePaymentsClient
   */
  let paymentsClient = null;

  /**
   * Configure your site's support for payment methods supported by the Google Pay
   * API.
   *
   * Each member of allowedPaymentMethods should contain only the required fields,
   * allowing reuse of this base request when determining a viewer's ability
   * to pay and later requesting a supported payment method
   *
   * @returns object Google Pay API version, payment methods supported by the site
   */
  function getGoogleIsReadyToPayRequest() {
    return Object.assign(
        {},
        baseRequest,
        {
          allowedPaymentMethods: [baseCardPaymentMethod]
        }
    );
  }

  /**
   * Configure support for the Google Pay API
   *
   * @see link https://developers.google.com/pay/api/web/reference/request-objects#PaymentDataRequest|PaymentDataRequest
   * @returns object PaymentDataRequest fields
   */
   function getGooglePaymentDataRequest() {
    const paymentDataRequest = Object.assign({}, baseRequest);
    paymentDataRequest.allowedPaymentMethods = [cardPaymentMethod];
    paymentDataRequest.transactionInfo = getGoogleTransactionInfo();

    paymentDataRequest.merchantInfo = {};
    
    {if $environment == 'PRODUCTION'}
        paymentDataRequest.merchantInfo.merchantId = "{$googlePayMerchantId|escape:'htmlall':'UTF-8'}";
    {/if}

    paymentDataRequest.merchantInfo.merchantName = "{$storeName|escape:'htmlall':'UTF-8'}";

    return paymentDataRequest;
  }

  /**
   * Return an active PaymentsClient or initialize
   *
   * @see link https://developers.google.com/pay/api/web/reference/client#PaymentsClient|PaymentsClient constructor
   * @returns google.payments.api.PaymentsClient Google Pay API client
   */
  function getGooglePaymentsClient() {
    if ( paymentsClient === null ) {
      paymentsClient = new google.payments.api.PaymentsClient({
        environment: "{$environment|escape:'htmlall':'UTF-8'}",
      });
    }
    return paymentsClient;
  }

  /**
   * Initialize Google PaymentsClient after Google-hosted JavaScript has loaded
   *
   * Display a Google Pay payment button after confirmation of the viewer's
   * ability to pay.
   */
  function onGooglePayLoaded() {
    const paymentsClient = getGooglePaymentsClient();
    paymentsClient.isReadyToPay(getGoogleIsReadyToPayRequest())
        .then(function(response) {
          if (response.result) {
            addGooglePayButton();
            // @todo prefetch payment data to improve performance after confirming site functionality
            prefetchGooglePaymentData();
          }
        })
        .catch(function(err) {
          // show error in developer console for debugging
          console.error(err);
        });
  }

  /**
   * Add a Google Pay purchase button alongside an existing checkout button
   *
   * @see link https://developers.google.com/pay/api/web/reference/request-objects#ButtonOptions|Button options
   * @see link https://developers.google.com/pay/api/web/guides/brand-guidelines|Google Pay brand guidelines
   */
  function addGooglePayButton() {
    const paymentsClient = getGooglePaymentsClient();
    const button =
        paymentsClient.createButton({
          buttonColor: 'dark',
          buttonType: 'plain',
          buttonRadius: 4,
          buttonLocale: 'pt',
          buttonSizeMode: 'fill',
          onClick: onGooglePaymentButtonClicked,
          allowedPaymentMethods: [baseCardPaymentMethod]
        });
    document.getElementById('google-pay-buttom').appendChild(button);
  }

  /**
   * Provide Google Pay API with a payment amount, currency, and amount status
   *
   * @see link https://developers.google.com/pay/api/web/reference/request-objects#TransactionInfo|TransactionInfo
   * @returns object transaction info, suitable for use as transactionInfo property of PaymentDataRequest
   */
  function getGoogleTransactionInfo() {
    return {
      countryCode: 'PT',
      currencyCode: 'EUR',
      totalPriceStatus: 'FINAL',
      // set to cart total
      totalPrice: "{$total|escape:'htmlall':'UTF-8'}"
    };
  }

  /**
   * Prefetch payment data to improve performance
   *
   * @see link https://developers.google.com/pay/api/web/reference/client#prefetchPaymentData|prefetchPaymentData()
   */
  function prefetchGooglePaymentData() {
    const paymentDataRequest = getGooglePaymentDataRequest();
    // transactionInfo must be set but does not affect cache
    paymentDataRequest.transactionInfo = {
      totalPriceStatus: 'NOT_CURRENTLY_KNOWN',
      currencyCode: 'EUR'
    };
    const paymentsClient = getGooglePaymentsClient();
    paymentsClient.prefetchPaymentData(paymentDataRequest);
  }

  /**
   * Show Google Pay payment sheet when Google Pay payment button is clicked
   */
  function onGooglePaymentButtonClicked() {
    const paymentDataRequest = getGooglePaymentDataRequest();
    paymentDataRequest.transactionInfo = getGoogleTransactionInfo();

    const paymentsClient = getGooglePaymentsClient();
    paymentsClient.loadPaymentData(paymentDataRequest)
        .then(function(paymentData) {
          // handle the response
          processPayment(paymentData);
        })
        .catch(function(err) {
          // show error in developer console for debugging
          console.error(err);
        });
  }
  /**
   * Process payment data returned by the Google Pay API
   *
   * @param object paymentData response from Google Pay API after user approves payment
   * @see link https://developers.google.com/pay/api/web/reference/response-objects#PaymentData|PaymentData object reference
   */
  function processPayment(paymentData) {
    // show returned data in developer console for debugging
      console.log(paymentData);

      lockScreenAndShowLoading();

      const paymentType = 'GOOGLEPAY';
      const url = "{$processWalletPayment nofilter}";
      const orderId = "{$orderId|escape:'htmlall':'UTF-8'}";

      fetch(url, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify({
          order_id: orderId,
          payment_type: paymentType,
          payload: paymentData
        })
      })
      .then(response => response.json())
      .then(data => {
        console.log('Success:', data);

        if (data.success) {
            alert('Pagamento realizado com sucesso!');
        } else {
            alert('Ocorreu um erro ao processar o pagamento. Por favor, tente novamente.');
        }

        if (data.redirect) {
          window.location.href = data.redirect;
        }
      })
      .catch((error) => {
        console.error('Error:', error);
        unlockScreen();
        alert('Ocorreu um erro ao processar o pagamento. Por favor, tente novamente.');
      });
      
    // @todo pass payment token to your gateway to process payment
    // @note DO NOT save the payment credentials for future transactions,
    // unless they're used for merchant-initiated transactions with user
    // consent in place.
    paymentToken = paymentData.paymentMethodData.tokenizationData.token;
  }

  function lockScreenAndShowLoading() {
    var imagePath = '{$moduleUrl|escape:'htmlall':'UTF-8'}views/img/loading.gif';

    document.body.style.pointerEvents = 'none';
    const loadingIndicator = document.createElement('div');
    loadingIndicator.id = 'loading-indicator';
    loadingIndicator.style.position = 'fixed';
    loadingIndicator.style.top = '50%';
    loadingIndicator.style.left = '50%';
    loadingIndicator.style.transform = 'translate(-50%, -50%)';
    loadingIndicator.style.zIndex = '99999';
    loadingIndicator.innerHTML = '<img src="' + imagePath + '" alt="Loading..." width="80" height="80">';
    document.body.appendChild(loadingIndicator);
  }

  function unlockScreen() {
    document.body.style.pointerEvents = 'auto';
    const loadingIndicator = document.getElementById('loading-indicator');
    if (loadingIndicator) {
      loadingIndicator.remove();
    }
  }

</script>

  <script async
    src="https://pay.google.com/gp/p/js/pay.js"
    onload="onGooglePayLoaded()"></script>