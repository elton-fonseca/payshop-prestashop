<?php

class PayshopEvent
{
    private function __construct(){}

    /**
     * Check the event data.
     *
     * @param array $orderData
     * @param string $signature
     * @return bool
     * @throws Exception
     */
    static public function checkEventSignature($orderData, $signature)
    {
        $array['order'] = $orderData['order'];
        $array['client'] = $orderData['client'];

        if ($orderData['extra_data'] !== null) {
            $array['extra_data'] = $orderData['extra_data'];
        }

        $data = json_encode($array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $validationHash = hash('sha256', $data . $signature);

        if (! $validationHash) {
            throw new Exception('Invalid Hash');
        }

        if ($validationHash != $orderData['validation_hash']) {
            throw new Exception('Invalid Signature');
        }

        return true;
    }

    /**
     * Create a file log with the request data.
     *
     * @return void
     * @throws Exception
     */
    static private function generateFileLog()
    {
        $file = fopen('event-' . date('Y-m-d-H-i-s') . '.txt', 'a');

        if (!$file) {
            throw new Exception('File log could not be created');
        }

        fwrite($file, self::getRequestInformations());
        fclose($file);
    }

    /**
     * Get the request informations.
     *
     * @return string
     */
    static private function getRequestInformations()
    {
        $requestInformations = 'server: ' . var_export($_SERVER, true) . PHP_EOL;
        $requestInformations .= 'post: ' .  var_export($_POST, true) . PHP_EOL;
        $requestInformations .= 'get: ' . var_export($_GET, true) . PHP_EOL;
        $requestInformations .= 'files: ' . var_export($_FILES, true) . PHP_EOL;
        $requestInformations .= 'request: ' . var_export($_REQUEST, true) . PHP_EOL;
        $requestInformations .= 'body: ' . var_export(file_get_contents('php://input'), true) . PHP_EOL;

        return $requestInformations;
    }
}