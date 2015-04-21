<?php

// the entry script URL (without host info) for functional and acceptance tests
// PLEASE ADJUST IT TO THE ACTUAL ENTRY SCRIPT URL
defined('TEST_ENTRY_URL') or define('TEST_ENTRY_URL', '/basic/web/index-test.php');

// the entry script file path for functional and acceptance tests
defined('TEST_ENTRY_FILE') or define('TEST_ENTRY_FILE', dirname(__DIR__) . '/web/index-test.php');

defined('YII_DEBUG') or define('YII_DEBUG', true);

defined('YII_ENV') or define('YII_ENV', 'test');

$vendorDir = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR;

if (!file_exists($vendorDir)) {
	$vendorDir = dirname(dirname(dirname(dirname($vendorDir)))) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR;
	if (!file_exists($vendorDir)) {
		throw new Exception('vendor directory not found.');
	}
}

require_once($vendorDir . 'autoload.php');

require_once($vendorDir . 'yiisoft/yii2/Yii.php');

// set correct script paths
$_SERVER['SCRIPT_FILENAME'] = TEST_ENTRY_FILE;
$_SERVER['SCRIPT_NAME'] = TEST_ENTRY_URL;
$_SERVER['SERVER_NAME'] = 'localhost';

Yii::setAlias('@tests', __DIR__);
