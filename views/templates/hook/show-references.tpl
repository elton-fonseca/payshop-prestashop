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

<style>
    .overlap {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.9);
        z-index: 99999;
    }

    .overlap-content {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        width: 400px;
        height: 90%;
        background: #fff;
        z-index: 999999;
        border-radius: 5px;
        display: flex;
        justify-content: center;
        align-items: center;
    }

    .close-btn {
        position: absolute;
        top: 10px;
        right: 10px;
        background: transparent;
        border: none;
        font-size: 20px;
        cursor: pointer;
        z-index: 1000000;
    }

    #references-popup {
        display: none;
    }
</style>

<div>
    <div class="overlap" id="references-popup">
        <div class="overlap-content">
            <button class="close-btn" onclick="document.getElementById('references-popup').style.display='none'">X</button>
            <iframe srcdoc="{$iframeContent|escape:'htmlall':'UTF-8'}" frameborder="0" style="height: 90%; width: 400px"></iframe>
        </div>
    </div>
</div>

<script>
    (function() {
        setTimeout(() => document.getElementById('references-popup').style.display = 'block', 3000);
    })();
</script>
