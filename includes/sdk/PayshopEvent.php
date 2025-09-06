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

class PayshopEvent
{
    private function __construct()
    {
    }

    /**
     * Check the event data.
     *
     * @param array $orderData
     * @param string $signature
     *
     * @return bool
     *
     * @throws Exception
     */
    public static function checkEventSignature($orderData, $signature)
    {
        $array['order'] = $orderData['order'];
        $array['client'] = $orderData['client'];

        if (isset($orderData['extra_data']) && $orderData['extra_data'] !== null) {
            $array['extra_data'] = $orderData['extra_data'];
        }

        $data = json_encode($array, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $validationHash = hash('sha256', $data . $signature);

        if (!$validationHash) {
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
     *
     * @throws Exception
     */
    private static function generateFileLog()
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
    private static function getRequestInformations()
    {
        $requestInformations = 'server: ' . var_export($_SERVER, true) . PHP_EOL;
        $requestInformations .= 'post: ' . var_export($_POST, true) . PHP_EOL;
        $requestInformations .= 'get: ' . var_export($_GET, true) . PHP_EOL;
        $requestInformations .= 'files: ' . var_export($_FILES, true) . PHP_EOL;
        $requestInformations .= 'request: ' . var_export($_REQUEST, true) . PHP_EOL;
        $requestInformations .= 'body: ' . var_export(file_get_contents('php://input'), true) . PHP_EOL;

        return $requestInformations;
    }
}
