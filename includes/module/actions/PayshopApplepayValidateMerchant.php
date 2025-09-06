<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to https://devdocs.prestashop.com/ for more information.
 *
 * @author    PrestaShop SA and Contributors <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class PayshopApplepayValidateMerchant
{
    // https://developer.apple.com/documentation/apple_pay_on_the_web/apple_pay_js_api/requesting_an_apple_pay_payment_session
    public function execute($validationURL)
    {
        $response = $this->sendAppleRequest($validationURL, $this->getConfiguration());

        if ($response['status'] != 200) {
            throw new Exception('Merchant validation failed: ' . $response['body']);
        }

        return json_decode($response['body'], true);
    }

    private function getConfiguration(): array
    {
        $fields = [
            'merchantIdentifier' => 'PAYSHOP_APPLEPAY_MERCHANT_ID',
            'displayName' => 'PAYSHOP_APPLEPAY_MERCHANT_NAME',
            'initiativeContext' => 'PAYSHOP_APPLEPAY_DOMAIN_NAME',
            'certificate' => 'PAYSHOP_APPLEPAY_CERTIFICATE_PATH',
            'privateKey' => 'PAYSHOP_APPLEPAY_PRIVATE_KEY_PATH',
        ];

        $config = ['initiative' => 'web'];
        foreach ($fields as $key => $field) {
            $applepaySetting = Configuration::get($field);

            if (empty($applepaySetting)) {
                PayshopLog::generate("PayshopApplepayValidateMerchant: Configuration for {$field} is missing or empty", PayshopLog::LOG_SEVERITY_ERROR);
                $config[$key] = '';
            } else {
                $config[$key] = $applepaySetting;
            }
        }

        return $config;
    }

    private function sendAppleRequest($url, $data)
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);

        curl_setopt($ch, CURLOPT_SSLCERT, $data['certificate']);
        curl_setopt($ch, CURLOPT_SSLKEY, $data['privateKey']);
        unset($data['certificate']);
        unset($data['privateKey']);

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
        ]);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $httpCode,
            'body' => $response,
        ];
    }
}
