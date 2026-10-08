<?php

if (!defined('_PS_VERSION_')) {
    define('_PS_VERSION_', '1.7.6.0');
}

$orderStateImgDir = sys_get_temp_dir() . '/ps_cod_orderstate_' . getmypid() . '/';
if (!is_dir($orderStateImgDir)) {
    mkdir($orderStateImgDir, 0777, true);
}

if (!defined('_PS_ORDER_STATE_IMG_DIR_')) {
    define('_PS_ORDER_STATE_IMG_DIR_', $orderStateImgDir);
}

require_once __DIR__ . '/Stubs/PaymentOption.php';
require_once __DIR__ . '/Stubs/WidgetInterface.php';
require_once __DIR__ . '/Stubs/PrestaShopDouble.php';
