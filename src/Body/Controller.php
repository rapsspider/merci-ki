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
 * @author      Quentin DOUZIECH<quentin.douziech@gmail.com>
 *
 * @copyright   Copyright (c) 2015  Jason BOURLARD, Quentin DOUZIECH
 * @since       1.0.0
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace MerciKI\Body;
use MerciKI\Config;
use MerciKI\Network\Request;
use MerciKI\Network\Response;
use MerciKI\Exception\ActionNotExist;

/**
 * This class represent a controller.
 */
abstract class Controller {
    
    /**
     * Class manager of the connection
     */
    protected $auth = null;

	/**
	 * The HTTP request
	 * @var Request
	 */
	protected $request = null;

	/**
	 * The HTTP response
	 * @var Response
	 */
	protected $response = null;

	/**
	 * The action to execute
	 * @var String
	 */
    protected $action = null;

	/**
	 * The layout to use.
	 * @var String
	 */
	public $layout = "default";

    /**
     * @var View
     */
    public $_view;

	/**
	 * Table of models to use.
	 * @var Array
	 */
	public $models = [];

    /**
     * Default Constructor.
	 * @param Request  request  HTTP Request.
	 * @param Response response HTTP Response.
     */
    public function __construct(Request &$request, Response &$response) {
    	$this->request = &$request;
    	$this->response = &$response;
    	$this->initialize();
    }

    /**
     * Initialise les vars de la classe.
     */
    public function initialize() {
    	$this->_view = new View();

        if(isset(Config::$auth_model['DAO']) and isset(Config::$auth_model['model'])) {
        	$class = ModelsManager::getModel(
                Config::$auth_model['DAO'], 
                Config::$auth_model['model']
            );
    	    $this->auth = new Authentification($class);

    	    if($this->auth->isConnected()) {
                $this->addVar('user', $this->auth->getUser());
    	    }
        }
        
        $this->_instanceModels();
    }


	/**
	 * Instance tous les tableaux de modèles inscrit dans le tableau content
	 * dans le paramètre model du controller.
	 */
	protected function _instanceModels() {
		foreach($this->models as $model => $DAO) {
			$this->instanceModel($DAO, $model);
		}
	}

    /**
     * Instance le modèle en fonction du $DAO spécifié.
     * @param String $DAO Dao à utiliser
     * @param String $model Modèle à instancier
     */
	protected function instanceModel($DAO, $model) {
		$this->$model = ModelsManager::getModel($DAO, $model);
	}

    /**
     * Execute the controller.
     * @param @action a executer
     */
    public function execute($action, $args = []) {
        $this->action = $action;

        // On récupère le nom de la classe 
        $myClassName = get_real_class($this);
        if($i = strpos($myClassName, 'Controller')) {
        	$myClassName = substr($myClassName, 0, $i);
        }

    	// Par défaut, le nom de la view est le même
    	// que le nom de l'action et dans le répertoire
    	// dont le nom correspond à celui du controller.
        $this->view = $myClassName . DS . $action;

    	/**
    	 * On ne peut executer que les fonctions écrites dans les sous classes
    	 * du controller. Par exemple beforeAction n'est pas une action
    	 */
		if(!method_exists($this, $action) 
			&& !in_array($action, get_class_methods('MerciKI\Body\Controller'))) {
			throw new ActionNotExist('Action "' . $action . '" inexistante !');
		}

        $this->beforeAction();

        // No redirect is required.
        if($this->response->statusCode() == 302) {
            return;
        }
        
        $view = call_user_func_array(array($this, $action), $args);

        if(is_array($view) || $view instanceof \JsonSerializable) {
            $this->response->header('Content-Type: application/json');
            $view = json_encode($view);
        }
        
        $this->response->body($view);
    }

	/**
	 * Method to call before executing the action.
	 * @return void
	 */
	public function beforeAction() {

	}

	/**
	 * Add a var to the view object.
	 * @param String var   Name of the var.
	 * @param Object value Value of the var.
	 * @return void
	 */
	public function addVar($var, $value) {
		$this->_view->addVar($var,$value);
	}
    
    /**
     * Méthode permettant de faire une redirection vers une autre addresse url
     * @param String url URl de l'adresse à atteindre. Cette addresse peut aussi
     * bien être une adresse relative ou une adresse absolue
     * @return void
     */
    public function redirect($url) {
        if ($url !== null) {
            $this->response->location(Router::url($url));
        }
    }

    /**
     * Retourne le contenu de la vue et ajoute le layout
     * si celui-ci a été défini.
     * @param String $file Représente la vue à utiliser.
     * @return String
     */
    public function view($file) {
        $file = str_replace('.', DS, $file);
        
        // Si ce n'est pas une redirection
        if ($this->response->statusCode() != 302) {
            // Récupération du content de la view
            $this->_view->file = 'Views' . DS . $file . '.php';
            
            if(!$this->layout) return $this->_view->content();
            
            // Récupération du layout
            $this->_view->addVar('content', $this->_view->content());
            $this->_view->file = 'Views' . DS . 'Layout' . DS . $this->layout . '.php';
            return $this->_view->content();
        }
        return '';
    }
}