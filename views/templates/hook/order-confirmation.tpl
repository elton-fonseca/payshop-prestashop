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

<h1 class="payshop-d-none payshop-payment-success" id="accepted-mbway-alert">
    {l s='Payment Confirmed on MBWay' mod='payshop'}
</h1>
<h1 class="payshop-d-none payshop-payment-declined" id="declined-mbway-alert">
    {l s='Payment Declined on MBWay' mod='payshop'}
</h1>

{if ($paymentSuccess == true)}
    <h1 class="payshop-payment-success">{l s='Payment Confirmed on MBWay' mod='payshop'}</h1>
{/if}

{if ($paymentDeclined == true)}
    <h1 class="payshop-payment-declined">{l s='Payment Declined on MBWay' mod='payshop'}</h1>
{/if}

{if (!$paymentSuccess && !$paymentDeclined)}
    <div>
        <div class="overlap" id="waiting-mbway">
            <div class="overlap-content">
                <h5>{l s='Waiting MBWay payment confirmation' mod='payshop'}</h5>
                <img width="250" src="{$moduleUrl|escape:'htmlall':'UTF-8'}views/img/loading.gif" />
                <p>
                    {l s='You can confirm the payment after close this page. ' mod='payshop'}
                    {l s='In this case, you will receive a message with confirmation' mod='payshop'}
                </p>
            </div>
        </div>

        <div class="overlap" id="accepted-mbway">
            <div class="overlap-content">
                <h4 class="payshop-error-color">
                    {l s='Payment Confirmed' mod='payshop'}
                </h4>
                <p>{l s='Payment Confirmed on MBWay' mod='payshop'}</p>
                    <button id="accepted-mbway-button">
                    {l s='Check details' mod='payshop'}
                </button>
            </div>
        </div>

        <div class="overlap" id="declined-mbway">
            <div class="overlap-content">
                <h4 class="payshop-error-color">
                    {l s='Declined Payment' mod='payshop'}
                </h4>
                <p>{l s='Payment Declined on MBWay' mod='payshop'}</p>
                    <button id="declined-mbway-button">
                    {l s='Check details' mod='payshop'}
                </button>
            </div>
        </div>
    </div>

    <style>
        .overlap {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 99999;
            display: none;
        }

        .overlap-content {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            width: 300px;
            background: #fff;
            z-index: 999999;
            padding: 20px;
            border-radius: 5px;
            text-align: center;

            display: flex;
            flex-direction: column;
            justify-content: space-between;
            gap: 32px;
        }

        .overlap-content p {
            margin-bottom: 0;
            margin-top: 10px;
        }
    </style>

    <form action="{$mBWayPaidCheckUrl|escape:'htmlall':'UTF-8'}" id="payshop_mbway_url"></form>

    <script>
        (function () {
            function handlePopup(waitPopup, interval, popupId) {
                waitPopup.style.display = 'none';
                clearInterval(interval);

                document.getElementById(popupId + '-alert').style.display = 'block';

                let popup = document.getElementById(popupId);

                document.getElementById(popupId + '-button').addEventListener('click', function () {
                    popup.style.display = 'none';
                });

                popup.style.display = 'block';
            }

            function checkMBWayPayment() {
                let waitPopup = document.getElementById('waiting-mbway');
                waitPopup.style.display = 'block';

                var interval = setInterval(function () {
                $.ajax({
                    url: document.forms.payshop_mbway_url.action,
                    type: 'POST',
                    data: {
                        "prestashop-order-id": '{$prestashopOrderId|escape:'htmlall':'UTF-8'}'
                    },
                    success: function (data) {
                        if (data.status == 'paid') {
                            handlePopup(waitPopup, interval, 'accepted-mbway');
                        }

                        if (data.status == 'declined') {
                            handlePopup(waitPopup, interval, 'declined-mbway');
                        }
                    },
                    error: function (error) {
                        alert(error);
                    }
                });
                }, 3000);
            }

            setTimeout(function () {
                checkMBWayPayment();
            }, 3000);
        })();
    </script>
{/if}

<style>
    .payshop-d-none {
        display: none;
    }

    .payshop-payment-success {
        color: green;
        background: #cbfccf;
    }

    .payshop-payment-declined {
        color: red;
        background: #f9d6d6;
    }

    .payshop-payment-success, .payshop-payment-declined {
        padding: 20px;
        font-size: 1.1rem;
        font-weight: 500;
    }
</style>