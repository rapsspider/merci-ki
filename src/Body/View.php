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

use MerciKI\Config;
use MerciKI\Exception\ViewNotExist;

/**
 * View class contain data and a file to parse.
 */
class View {

    /**
     * Vars to set on the view
     * @var Array
     */
    private $vars = [];

    /**
     * Get the content of the this view.
     * @param file String The path to the file to use.
     * @param  varName String the name of the variable where must be add (or append) the result.
     * @return String The result.
     * @throw ViewNotExist if the file doesn't exist.
     */
    public function content($file = null, $varName = null) {
        if ($file == null) {
            return "";
        }

        $viewFile = __ROOT_DIR__ . DS . Config::$app['directory'] . DS . $file;

        if(!file_exists($viewFile)) {
            throw new ViewNotExist('View  "' . $viewFile . '" don\'t exist !');
        }

        extract($this->vars);
        ob_start();
        include $viewFile;

        if($varName == null) {
            return ob_get_clean();
        } // else

        return $this->vars[$varName] = ob_get_clean();
    }

    /**
     * Set a new variable to the view.
     * @param var String Name of the var.
     * @param value Object Content of the var.
     * @return void
     */
    public function addVar($var, $value) {
        $this->vars[$var] = $value;
    }

    /**
     * Add a map to this current variable map.
     * @param $vars Array MAP of variable name and value.
     */
    public function addVars($vars) {
        $this->vars += $vars;
    }
    
    /**
     * Return a list of vars used by the view.
     * @return Array
     */
    public function getVar() {
        return $this->vars;
    }
}