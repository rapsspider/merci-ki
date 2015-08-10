<?php

require_once 'app/Config.php';
use MerciKI\Config;

if(!defined('__ROOT_DIR__')) {
	define('__ROOT_DIR__', __DIR__);
}
if(!defined('DS')) {
	define('DS', DIRECTORY_SEPARATOR);
}

spl_autoload_register(function ($class_name) {
	$file = substr(str_replace('\\', DS, $class_name), 8);

	if(file_exists(__ROOT_DIR__ . DS . 'libs' . DS . $file . '.php')) {
        include __ROOT_DIR__ . DS . 'libs' . DS . $file . '.php';
	}
	if(file_exists( __ROOT_DIR__ . DS . Config::$app_dir . DS . $file . '.php')) {
        include __ROOT_DIR__ . DS . Config::$app_dir . DS . $file . '.php';
	}
});

?>