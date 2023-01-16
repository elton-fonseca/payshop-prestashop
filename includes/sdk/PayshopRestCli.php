<?php

class PayshopRestCli
{
    private function __construct(){}

    /**
     * Get CURL configured with the given URL and headers
     * 
     * @param $url
     * @param $method
     * @param $headers
     * @return false|resource
     */
    private static function getConnect($url, $method, $headers)
    {
        $headers_default = [
            'Accept: application/json',
            'Content-Type: application/json',
        ];

        is_array($headers) ? $headers = array_merge($headers_default, $headers) : '';

        $connect = curl_init($url);

        $useragent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : 'webhook';
        curl_setopt($connect, CURLOPT_USERAGENT, $useragent);

        curl_setopt($connect, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($connect, CURLOPT_CUSTOMREQUEST, $method);
        curl_setopt($connect, CURLOPT_HTTPHEADER, $headers);

        return $connect;
    }

    /**
     * Set data to CURL
     *
     * @param $connect
     * @param $data
     * @param $content_type
     * @return void
     * @throws Exception
     */
    private static function setData($connect, $data, $content_type)
    {
        if ($content_type == 'application/json') {
            if (gettype($data) == 'string') {
                json_decode($data, true);
            } else {
                $data = json_encode($data);
            }

            if (function_exists('json_last_error')) {
                $json_error = json_last_error();
                if ($json_error != JSON_ERROR_NONE) {
                    throw new Exception("JSON Error [{$json_error}] - Data: {$data}");
                }
            }
        }

        curl_setopt($connect, CURLOPT_POSTFIELDS, $data);
    }

    /**
     * Execute one request
     * 
     * @param $method
     * @param $url
     * @param $data
     * @param $headers
     * @return array
     * @throws Exception
     */
    private static function exec($method, $url, $data, $headers)
    {
        $connect = self::getConnect($url, $method, $headers);

        if ($data) {
            self::setData($connect, $data, 'application/json');
        }

        $api_result = curl_exec($connect);
        $api_http_code = curl_getinfo($connect, CURLINFO_HTTP_CODE);

        $curlMessageError = curl_error($connect);
        if ($curlMessageError) {
            curl_close($connect);
            throw new Exception('CURL Error ' . $curlMessageError);
        }

        $response = [
            'status' => $api_http_code,
            'response' => json_decode($api_result, true),
        ];

        curl_close($connect);

        return $response;
    }

    /**
     * Execute one request using GET method
     * 
     * @param $url
     * @param null $headers
     * @return array
     * @throws Exception
     */
    public static function get($url, $headers = null)
    {
        return self::exec('GET', $url, null, $headers);
    }

    /**
     * Execute one request using POST method
     * 
     * @param $url
     * @param $data
     * @param null $headers
     * @return array
     * @throws Exception
     */
    public static function post($url, $data, $headers = null)
    {
        return self::exec('POST', $url, $data, $headers);
    }

    /**
     * Execute one request using PUT method
     * 
     * @param $url
     * @param $data
     * @param null $headers
     * @return array
     * @throws Exception
     */
    public static function put($url, $data, $headers = null)
    {
        return self::exec('PUT', $url, $data, $headers);
    }
}
