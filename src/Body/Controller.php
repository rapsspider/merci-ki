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
 * @author      Jason BOURLARD<quentin.douziech@gmail.com>
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
 * Classe représentant un controller
 */
abstract class Controller {

	/**
	 * Contient la request HTTP
	 * @var Request
	 */
	protected $request = null;

	/**
	 * Contient la response HTTP
	 * @var Response
	 */
	protected $response = null;

	/**
	 * Contient l'action a executer
	 * @var String
	 */
    protected $action = null;

	/**
	 * Layout par défaut à utiliser
	 * @var String
	 */
	public $layout = "default";

	/**
	 * @var file de view
	 */
    public $view = 'index';

    /**
     * @var View
     */
    public $_view;

	/**
	 * Tableau de modèles à utiliser.
	 * @var Array
	 */
	public $models = [];

    /**
     * Constructeur par défaut.
	 * @param Request request Request de l'utilisateur
	 * @param Reposne response Response à envoyer à l'utilisateur.
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
     * Execute le controller.
     * @param @action a executer
     */
    public function execute($action, $args = []) {
        session_start();
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
		$view = call_user_func_array(array($this, $action), $args);
        
        if(is_array($view) || $view instanceof \JsonSerializable) {
            $this->response->header('Content-Type: application/json');
            $view = json_encode($view);
        }
        
        $this->response->body($view);
    }

	/**
	 * Méthode à appeler avant d'executer l'action
	 * @return void
	 */
	public function beforeAction() {

	}

	/**
	 * Méthode permettant d'ajouter une var à la view.
	 * @param String var Nom de la var à ajouter.
	 * @param Object value Content de la var.
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
        if ($this->response->codeStatus() != 302) {
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