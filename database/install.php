<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

/**
 * 2007-2025 PrestaShop
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
 *  @copyright 2007-2025 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *
 * Don't forget to prefix your containers with your own identifier
 * to avoid any conflicts with others containers.
 */
$sql = [];

// transactions table
$sql[] = 'CREATE TABLE IF NOT EXISTS `' . _DB_PREFIX_ . 'payshop_transactions` (
      `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
      `cart_id` INT(10) UNSIGNED NOT NULL,
      `order_id` INT(10) UNSIGNED NULL,
      `customer_id` INT(11) UNSIGNED NOT NULL,
      `total` DECIMAL(15,2) NULL,

      `charge_id` VARCHAR(100) NULL,
      `instrument_id` VARCHAR(100) NULL,
      `payment_id` VARCHAR(100) NULL,
      `payment_method` VARCHAR(100) NOT NULL,
      `payment_status` VARCHAR(100) NOT NULL,

      `is_payment_test` TINYINT(1) NULL,
      `created_at` DATETIME NOT NULL,
      `updated_at` DATETIME NULL,
      PRIMARY KEY (`id`)
    ) ENGINE = ' . _MYSQL_ENGINE_ . 'DEFAULT CHARSET=utf8';

// Create tables
foreach ($sql as $query) {
    if (Db::getInstance()->execute($query) == false) {
        PayshopLog::generate('PayshopInstall: Failed to execute query: ' . Db::getInstance()->getMsgError(), 'error');

        return false;
    }
}
