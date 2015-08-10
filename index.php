<?php
/**
 * Framework
 * @author Jason BOURLARD<jason.bourlard@gmail.com>
 */


define('__ROOT_DIR__', __DIR__);

// TypeEffort
define('WORK_TYPE_TEST', 1);
define('WORK_TYPE_TECHREVIEW', 2);
define('WORK_TYPE_QUALREVIEW', 3);

// % Budget for reading
define('BUDGET_READ_PERCENT', 0.2);

require_once 'autoload.php';

if(MerciKI\Config::$debug) {
    error_reporting(E_ERROR | E_WARNING | E_PARSE | E_NOTICE | E_ALL);
} else {
	error_reporting(0);
}

require_once 'basiques_fonctions.php';
require_once MerciKI\Config::$app_dir . '\Routes.php';

$application = new MerciKI\Application();
$application->execute();