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
class PayshopLog
{
    const LOG_SEVERITY_INFORMATIVE = 1;
    const LOG_SEVERITY_WARNING = 2;
    const LOG_SEVERITY_ERROR = 3;

    /**
     * Get url for adminto view the logs
     *
     * @return string
     */
    public static function getLogUrl()
    {
        $ps_link = new Link();

        return $ps_link->getAdminLink('AdminLogs', true);
    }

    /**
     * Generate plugin logs
     *
     * @param string $message
     * @param int|string $severity
     *
     * @return void
     */
    public static function generate($message, $severity = 1)
    {
        switch ($severity) {
            case 'warning':
                $severity_log = self::LOG_SEVERITY_WARNING;
                break;

            case 'error':
                $severity_log = self::LOG_SEVERITY_ERROR;
                break;

            default:
                $severity_log = self::LOG_SEVERITY_INFORMATIVE;
        }

        $object_id = (int) str_replace('.', '', PAYSHOP_VERSION);
        $object_type = 'Payshop';

        PrestaShopLogger::addLog($message, $severity_log, null, $object_type, $object_id, true, null);
    }
}
