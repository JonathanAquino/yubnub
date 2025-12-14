<?php
define('SERVER_ROOT', realpath('..'));
require_once SERVER_ROOT . '/config/Config.php';
require_once SERVER_ROOT . '/config/MyConfig.php';
require_once SERVER_ROOT . '/app/helpers/functions.php';
require_once SERVER_ROOT . '/app/helpers/Autoloader.php';
require_once SERVER_ROOT . '/lib/Mustache/Autoloader.php';
Mustache_Autoloader::register();

$config = new MyConfig();
error_reporting($config->getErrorReportingLevel());
ini_set('display_errors', $config->shouldDisplayErrors());
spl_autoload_register(array(new Autoloader(), 'load'));

$dispatcher = new Dispatcher($config);
$dispatcher->dispatch($_SERVER['REQUEST_URI']);
