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

<script crossorigin="" src="https://applepay.cdn-apple.com/jsapi/1.latest/apple-pay-sdk.js"></script>


<div>
    <div class="overlap-wallets" id="applepay-payshop">
        <div class="overlap-wallets-content">
          <button class="close-btn-wallets" onclick="document.getElementById('references-popup').style.display='none'">X</button>

          <h2>Realize o seu pagamento através do Apple Pay</h2>
          <apple-pay-button id="applePayButton" buttonstyle="white-outline" type="pay" locale="pt-PT"></apple-pay-button>
          <img src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/img/payshop-logo.png" alt="Payshop" width="150">
        </div>
    </div>

    <div class="flow-button-wallets">
      <img src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/img/applepay.png" alt="Google Pay" width="70px">
    </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function() {
    setTimeout(() => document.getElementById('applepay-payshop').style.display = 'block', 2000);

    document.querySelector('.flow-button-wallets').addEventListener('click', () => {
      document.getElementById('applepay-payshop').style.display = 'block';
    });

    document.querySelector('.close-btn-wallets').addEventListener('click', () => {
      document.getElementById('applepay-payshop').style.display = 'none';
    });

    // Check if Apple Pay is available
    if (window.ApplePaySession && ApplePaySession.canMakePayments()) {
        const applePayButton = document.getElementById('applePayButton');

        // Add click event to the button
        applePayButton.addEventListener('click', async () => {
          document.getElementById('applepay-payshop').style.display = 'none';
          
          const paymentRequest = {
            countryCode: 'PT',
            currencyCode: 'EUR',
            merchantCapabilities: ['supports3DS'],
            supportedNetworks: ['visa', 'masterCard', 'amex'],
            total: {
              label: '{$storeName|escape:'htmlall':'UTF-8'}',
              amount: '{$total|escape:'htmlall':'UTF-8'}',
            },
          };

          // Start the Apple Pay session
          const session = new ApplePaySession(3, paymentRequest);

          // Merchant validation
          session.onvalidatemerchant = async (event) => {
            try {
              const url = "{$applepayMerchantValidation nofilter}";

              const validationData = await fetch(url, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                },
                body: JSON.stringify({ validationURL: event.validationURL }),
              }).then(res => res.json());

              session.completeMerchantValidation(validationData);
            } catch (error) {
              console.error('Merchant validation failed:', error);
              session.abort();
            }
          };

          // Payment authorized
          session.onpaymentauthorized = async (event) => {
            try {
              const paymentType = 'APPLEPAY';
              const url = "{$processWalletPayment nofilter}";
              const orderId = "{$orderId|escape:'htmlall':'UTF-8'}";
              const paymentData = event.payment.token;

              // Send payment data to the server
              const response = await fetch(url, {
                method: 'POST',
                headers: {
                  'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                  order_id: orderId,
                  payment_type: paymentType,
                  payload: paymentData
                })
              });

              if (response.ok) {
                let data = await response.json();

                if (data.success) {
                  session.completePayment(ApplePaySession.STATUS_SUCCESS);
                } else {
                  session.completePayment(ApplePaySession.STATUS_FAILURE);
                }

                window.location.reload();
              } else {
                session.completePayment(ApplePaySession.STATUS_FAILURE);
                alert('Ocorreu um erro ao processar o pagamento. Por favor, tente novamente mais tarde.');
              }
            } catch (error) {
              console.error('Payment processing failed:', error);
              session.completePayment(ApplePaySession.STATUS_FAILURE);
            }
          };

          // Start the session
          session.begin();
        });
      } else {
        console.log('Apple Pay is not available on this device/browser.');
      }
  });
</script>