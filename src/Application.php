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
use MerciKI\Body\View;
use MerciKI\Body\ModelsManager;
use MerciKI\Factory\PDOFactory;
use MerciKI\Network\GlobalResponse;
use MerciKI\Exception\MerciKIException;
use MerciKI\Exception\DatabaseError;

use Zend\Diactoros\Request;
use Zend\Diactoros\Response;
use Zend\Diactoros\Response\RedirectResponse;
use Zend\Diactoros\Response\HtmlResponse;

class Application {
    
    /**
     * The request received
     * @var Request
     */
    private $request;

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
        $this->request = new Request($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD'], 'php://input');
    }
    
    /**
     * Launch the application
     */
    public function execute() {
        $responseToSend = null;

        try {
            $router = new Router();
            $responseToSend = $router->execute($this->request);
        } catch(MerciKIException $e) {
            $responseToSend = $this->_catchException($e);
        }

        if($responseToSend instanceof Response) {
            header('HTTP/' . $responseToSend->getProtocolVersion() . ' '
                . $responseToSend->getStatusCode() . ' '
                . $responseToSend->getReasonPhrase());
            foreach ($responseToSend->getHeaders() as $header => $values) {
                header($header . ':' . implode(', ', $values));
            }

            if (!$responseToSend instanceof RedirectResponse) {
                echo $responseToSend->getBody();
            }
        }
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
     * Return a response to send using an exception.
     *
     * @param MerciKIException e The thrown exception.
     * @return The response to send.
     */
    protected function _catchException(MerciKIException $e) {

        $code = $e->getCode();

        if($code == null) {
            $code = 500;
        }

        try {
            $view = new View();
            $view->addVars([
                'message' => $e->getMessage(),
                'code'    => $code
            ]);
            $content = $view->content('Views' . DS . 'Exception' . DS .  $e->getViewFileName() . '.php', 'content');

            if($e->getLayoutFileName()) {
                $content = $view->content('Views' . DS . 'Layout' . DS .  $e->getLayoutFileName() . '.php');
            }
        } catch(ViewNotExist $v) {
            $content = '<!DOCTYPE html><html><head><title>ERROR</title></head><body>'
                 .'No view found for this exception : ' . $code . ' - ' . $e->getMessage() . '</body></html>';
        } catch(MerciKIException $m) {
            return $this->_catchException($m);
        }

        return new HtmlResponse($content, $code);
    }
}