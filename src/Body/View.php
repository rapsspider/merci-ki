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
	 * Name of the view file
     * @var String
	 */
	public $file = null;

    /**
     * Vars to set on the view
     * @var Array
     */
	public $vars = [];

	/**
	 * Get the content of the this view.
     * @return String
	 */
	public function content() {
        $viewFile = __ROOT_DIR__ . DS . Config::$app['directory'] . DS . $this->file;
		if(!file_exists($viewFile)) {
			throw new ViewNotExist('View  "' . $viewFile . '" don\'t exist !');
		}

		extract($this->vars);
		ob_start();
		include $viewFile;
		return ob_get_clean();
	}

	/**
	 * Set a new variable to the view.
	 * @param String var Name of the var.
	 * @param Object value Content of the var.
	 * @return void
	 */
	public function addVar($var, $value) {
		$this->vars[$var] = $value;
	}
    
    /**
     * Return a list of vars used by the view.
     * @return Array
     */
    public function getVar() {
        return $this->vars;
    }
}