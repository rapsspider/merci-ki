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

namespace MerciKI\Factory;

use \PDO;
use MerciKI\Config;

class PDOFactory {
    
    protected static $_instances = [];
    
    /**
     * Return an object PDO which use a MySQL connection.
     * 
     * @param String $database Name of the database to use.
     * @return PDO If the database exists, then an object PDO will be returned.
     *             Null otherwise.
     * @see MerciKI\Config::databases
     */
    public static function getMysqlConnection($database) {    
        
        if(isset(self::$_instances[$database])) return self::$_instances[$database];
        
        if(!isset(Config::$databases[$database])) return null;
        
        $config =& Config::$databases[$database];
        $db = new PDO(
            'mysql:host=' . $config['host'] . ';dbname=' . $config['database'],
            $config['user'],
            $config['pass'],
            [
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8"
            ]
        );
        
        self::$_instances[$database] = $db;
        return $db;
    }
}