<?php
/**
 * Craft web bootstrap file
 */
error_reporting(0);
ini_set("display_errors", 0);

// Set path constants
define('CRAFT_BASE_PATH', dirname(__DIR__));
define('CRAFT_VENDOR_PATH', CRAFT_BASE_PATH.'/vendor');

// Load Composer's autoloader
require_once CRAFT_VENDOR_PATH.'/autoload.php';

// Load dotenv?
if (class_exists('Dotenv\Dotenv') && file_exists(CRAFT_BASE_PATH.'/.env')) {
    $factory = new Dotenv\Environment\DotenvFactory([
        new Dotenv\Environment\Adapter\PutenvAdapter(),
    ]);
    Dotenv\Dotenv::create(CRAFT_BASE_PATH, null, $factory)->load();
}

// Load and run Craft
define('CRAFT_ENVIRONMENT', getenv('ENVIRONMENT') ?: 'production');
$app = require CRAFT_VENDOR_PATH.'/craftcms/cms/bootstrap/web.php';
$app->run();
