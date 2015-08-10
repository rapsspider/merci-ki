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
	protected $code = 501;

	/**
	 * Message to set in the body of the response.
	 */
	protected $message;

	/**
	 * Exception's layout.
	 */
	protected $layout = "exception";

	/**
	 * Exception's view.
	 */
	protected $view = 'index';

	/**
	 * Init the response.
     * Insert the status code and the message into the response.
	 * 
     * @param Response $response Response to update.
	 */
	public function getResponse(Response &$response) {
		$response->body($this->message); // Ajouter les vues TODO
		$response->statusCode($this->code);

		$vars = [
		    'message' => $this->message,
		    'code'    => $this->code,
		    'code_message' => $response->httpCodes($this->code)
		];

		$this->_getBody($vars);
		$response->body($this->_getLayout($vars));
	}

	/**
     * Return the view.
	 *
	 * @param Array vars List of vars to set in the view.
	 * @return String The view.
	 */
	protected function _getBody(&$vars) {
		try {
			$view = new View();
			$view->file = 'Views' . DS . 'Exception' . DS .  $this->view . '.php';
			$view->vars = &$vars;
			$vars['content'] = $view->content();
		} catch(ViewNotExist $e) {
			$vars['content'] = 'Views' . DS . 'Exception' . DS .  $this->view . '.php not exist !';
		}

		return $vars['content'];
	}

	/**
	 * Retourne le body de la page à générer.
	 * @param Array vars Liste de variable à ajouter à la vue.
	 * @return String Body de la page.
	 */
	protected function _getLayout(&$vars) {
		try {
			$view = new View();
			$view->file = 'Views' . DS . 'Layout' . DS . $this->layout . '.php';
			$view->vars = &$vars;
			$body = $view->content();
		} catch(ViewNotExist $e) {
			$body = 'Views' . DS . 'Layout' . DS . $this->layout . '.php not exist !';
		}
		return $body;
	}
}


?>