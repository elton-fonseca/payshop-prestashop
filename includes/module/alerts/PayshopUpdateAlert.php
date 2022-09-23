<?php
/**
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
 *
 * Don't forget to prefix your containers with your own identifier
 * to avoid any conflicts with others containers.
 */

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

        $smart = $this->module->context->smarty;
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
     * @return boolean
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
     * @return string
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
     * @return void
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
        return $this->module->context->link->getModuleLink(
            $this->module->name,
            'UpdateAlertClose'
        );
    }
}