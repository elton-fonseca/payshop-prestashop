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
class PayshopUpdateAlert
{
    /**
     * @var Module
     */
    private $module;

    /**
     * @var string
     */
    private $local_path;

    /**
     * @var string
     */
    const ONLINE_VERSION_URL = 'https://apps.coolsis.pt/payshop/prestashop/version.json';

    /**
     * class constructor
     *
     * @param Module $module
     * @param string $local_path
     */
    public function __construct(Module $module, $local_path)
    {
        $this->module = $module;
        $this->local_path = $local_path;
    }

    /**
     * Return message to display if the module is not updated
     *
     * @return string
     */
    public function execute()
    {
        if ($this->alreadyShownInTheLast24hours()) {
            return '';
        }

        $onlineVersionInformations = $this->getOnlineVersion();

        if ($this->isUpdated($onlineVersionInformations['version'])) {
            return '';
        }

        $smart = $this->module->getContext()->smarty;
        $smart->assign([
            'updateAlertCloseLink' => $this->getUpdateAlertCloseControllerLink(),
            'downloadUrl' => $onlineVersionInformations['url'],
            'availableVersion' => $onlineVersionInformations['version'],
        ]);

        return $smart->fetch($this->local_path . 'views/templates/admin/update-alert.tpl');
    }

    /**
     * Check if the module is updated
     *
     * @return bool
     */
    private function isUpdated($onlineVersion)
    {
        return version_compare(
            $this->module->version,
            $onlineVersion,
            '=='
        );
    }

    /**
     * Get version from online URL
     *
     * @return array|null
     */
    private function getOnlineVersion()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, self::ONLINE_VERSION_URL);
        $data = curl_exec($ch);
        curl_close($ch);
        $current_version = $data;

        $versionInformation = json_decode($current_version, true);

        return $versionInformation;
    }

    /**
     * Check if the message was already shown in the last 24 hours
     *
     * @return bool
     */
    private function alreadyShownInTheLast24hours()
    {
        $lastclosedAlertDate = Configuration::get('PAYSHOP_LAST_CLOSED_ALERT_UPDATE_DATE');

        if ($lastclosedAlertDate == null) {
            return false;
        }

        $yesterday = date('Y-m-d H:i', strtotime('-1 days'));

        return $lastclosedAlertDate > $yesterday;
    }

    /**
     * Get link to close the alert controller
     *
     * @return string
     */
    private function getUpdateAlertCloseControllerLink()
    {
        return $this->module->getContext()->link->getModuleLink(
            $this->module->name,
            'UpdateAlertClose'
        );
    }
}
