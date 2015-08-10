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

use \Exception;
use \PDOException;
use MerciKI\Config;
use MerciKI\Body\Router;
use MerciKI\Body\ModelsManager;
use MerciKI\Network\Response;
use MerciKI\Network\Request;
use MerciKI\Factory\PDOFactory;
use MerciKI\Exception\MerciKIException;
use MerciKI\Exception\DatabaseError;

class Application {
	
	/**
	 * The request received
	 * @var Request
	 */
	private $request;
	
	/**
	 * The response to send.
	 * @var Response
	 */
	private $response;

	/**
	 * Default constructor of the application
	 */
	public function __construct() {
		$this->initialize();
	}
	
	/**
	 * Initialize the application
	 */
	protected function initialize() {
		$this->request = new Request();
		$this->response = new Response();
	}
	
	/**
	 * Launch the application
	 */
	public function execute() {
		try {
			$routeur = new Router($this->request, $this->response);
			$routeur->execute();
		} catch(PDOException $e) {
			$f = new DatabaseError('Error during the connection : ' . $e->getMessage());
			$this->_catchException($f);
		} catch(MerciKIException $e) {
			$this->_catchException($e);
		}

		echo $this->response->send();
	}

    /** 
     * Return an object PDO
     * @return PDO
     */
	protected function _getDatabase() {
		$pdo = PDOFactory::getMysqlConnection('default');
		if(!$pdo) throw new DatabaseError('Can\'t create the PDO object.');
		return $pdo;
	}

    /** 
     * Change the response using a thrown exception.
     * @param MerciKIException e The thrown exception.
     */
	protected function _catchException(MerciKIException $e) {
		$e->getResponse($this->response);
	}
}