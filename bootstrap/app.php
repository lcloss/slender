<?php
error_reporting(E_ALL);
ini_set('display_errors', true);
session_start();

define('SITE_FOLDER', str_replace( 'bootstrap', '', __DIR__));
define('APP_FOLDER', SITE_FOLDER . 'app' . DIRECTORY_SEPARATOR);
define('ROUTES_FOLDER', SITE_FOLDER . 'routes' . DIRECTORY_SEPARATOR);
define('BOOTSTRAP_FOLDER', SITE_FOLDER . 'bootstrap' . DIRECTORY_SEPARATOR);
define('INCLUDES_FOLDER', SITE_FOLDER . 'includes' . DIRECTORY_SEPARATOR);
define('RESOURCES_FOLDER', SITE_FOLDER . 'resources' . DIRECTORY_SEPARATOR);
define('VIEWS_FOLDER', RESOURCES_FOLDER . 'views' . DIRECTORY_SEPARATOR);
define('HELPERS_FOLDER', SITE_FOLDER . 'helpers' . DIRECTORY_SEPARATOR);
define('VENDOR_FOLDER', SITE_FOLDER . 'vendor' . DIRECTORY_SEPARATOR);

require_once(VENDOR_FOLDER . 'autoload.php');
require_once(BOOTSTRAP_FOLDER . 'autoload.php');
require_once(HELPERS_FOLDER . 'functions.php');
require_once(ROUTES_FOLDER . 'router.php');
