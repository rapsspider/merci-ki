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
namespace MerciKI;

class Config {

    /**
     * Active ou non le debug de l'application
     */
	public static $debug = false;

	/**
	 * Information de connexion à la base de données.
	 */
    public static $databases = [];

    /**
     * Encode type of the page.
     */
    public static $encoding = 'utf8';

    /**
     * Caractère de séparation des vars de type argument
     */
    public static $arg_separateur = '=';
    
    /**
     * Configuration of the application
     */
    public static $app = [
        /**
         * Directory of the application
         */
        'directory' => 'src',
    
        /**
         * Namespace of the application
         */
        'namespace' => 'MerciKI\\App\\'
    ];

}