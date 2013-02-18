<?php
require_once '../config/Config.php';
require_once '../config/MyConfig.php';
require_once '../app/helpers/Autoloader.php';
$config = new MyConfig();
error_reporting($config->getErrorReportingLevel());
ini_set('display_errors', $config->shouldDisplayErrors());
spl_autoload_register(array(new Autoloader(), 'load'));

$dispatcher = new Dispatcher($config);
$dispatcher->dispatch($_SERVER['REQUEST_URI']);
