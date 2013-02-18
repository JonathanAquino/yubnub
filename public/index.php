<?php
require_once '../config/config.php';
require_once '../app/helpers/Autoloader.php';

spl_autoload_register(array(new Autoloader(), 'load'));
$dispatcher = new Dispatcher();
$dispatcher->dispatch($_SERVER['REQUEST_URI']);
