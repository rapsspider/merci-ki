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

namespace MerciKI\Exception;

use \Exception;
use MerciKI\Body\View;
use MerciKI\Network\Response;

/**
 * It's the main exception of the MerciKI application.
 */
class MerciKIException extends Exception {

    /**
     * HTTP status code to return.
     */
    protected $statusCode = 501;

    /**
     * The layout file name of this exception.
     */
    protected $layout = "exception";

    /**
     * The view file name of this exception.
     */
    protected $view = 'index';

    /**
     * Getter on status code
     */
    public function getStatusCode() {
        return $this->code;
    }

    /**
     * Getter on status code
     */
    public function getLayoutFileName() {
        return $this->layout;
    }

    /**
     * Getter on status code
     */
    public function getViewFileName() {
        return $this->view;
    }
}


?>