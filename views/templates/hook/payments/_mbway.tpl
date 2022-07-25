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

<div class="overlap" id="declined-mbway">
    <div class="overlap-content mbway-declined">
        <h4 class="payshop-error-color">
            {l s='Declined Payment' mod='payshop'}
        </h4>
        <p>{l s='Payment Declined on MBWay' mod='payshop'}</p>
        <button onclick="window.location.href = '{$shopUrl|escape:'htmlall':'UTF-8'}'">
            {l s='Go to home' mod='payshop'}
        </button>
    </div>
</div>

<style>
    .payshop-form-control-error {
        border: 2px solid #eb5a5a !important;
    }

    .payshop-error-color {
        color: #eb5a5a;
    }

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
        height: 423px;
        background: #fff;
        z-index: 999999;
        padding: 20px;
        border-radius: 5px;
        text-align: center;

        display: flex;
        flex-direction: column;
        justify-content: space-between;
    }

    .mbway-declined {
        height: 250px;
    }

    .overlap-content p {
        margin-bottom: 0;
        margin-top: 10px;
    }

</style>