<?php
/**
 * Copyright (c) 2015  Jason BOURLARD
 *                     Quentin DOUZIECH
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 *
 * @author      Jason BOURLARD<jason.bourlard@gmail.com>
 *
 * @copyright   Copyright (c) 2015  Jason BOURLARD, Quentin DOUZIECH
 * @since       1.0.0
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace MerciKI\Body;

use \JsonSerializable;
use MerciKI\Factory\PDOFactory;
use MerciKI\Exception\MerciKIException;
use MerciKI\Database\IDatabaseSerializable;

class ModelsManager {

	protected static $_instances = [];

	public static function getModel($DAO, $model) {
		if(!isset(self::$_instances[$model])) {
			$class = "MerciKI\\Models\\DAO\\" . $model . "Table" . $DAO;
			if(class_exists($class)) {
				self::$_instances[$model] = self::instanceModel($DAO, $class);
			} else {
				throw new MerciKIException('Table Model "' . $model . 'Table' . $DAO . '" inexistant !');
			}
		}

		return self::$_instances[$model];
	}
    
    protected static function instanceModel($DAO, $class) {
        $dao = self::getDAO($DAO, $class::$database);
	    return new $class($dao);
    }
    
    protected static function getDAO($DAO, $arg) {
        if($DAO == 'PDO') {
            return PDOFactory::getMysqlConnection($arg);
        }
    }
}