<?php

class PayshopApplepayValidateMerchant
{
    //https://developer.apple.com/documentation/apple_pay_on_the_web/apple_pay_js_api/requesting_an_apple_pay_payment_session
    public function execute($validationURL)
    {
        $response = $this->sendAppleRequest($validationURL, $this->getConfiguration());

        if ($response['status'] != 200) {
            throw new \Exception('Merchant validation failed: ' . $response['body']);
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
            'privateKey' => 'PAYSHOP_APPLEPAY_PRIVATE_KEY_PATH'
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
            throw new \Exception('Curl error: ' . curl_error($ch));
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'status' => $httpCode,
            'body' => $response,
        ];
    }
}
