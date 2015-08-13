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

use MerciKI\Body\View;
use MerciKI\Body\Controller;
use MerciKI\Config;
use MerciKI\Network\Request;
use MerciKI\Network\Response;
use MerciKI\Exception\PageNotExist;
use MerciKI\Exception\MerciKIException;
use MerciKI\Exception\ActionNotExist;
use MerciKI\Exception\EntityNotExist;
use MerciKI\Exception\ControllerNotExist;
use MerciKI\Exception\ModelNotExist;
use \Exception;

/**
 * @TOBETRANSLATED
 */
class Router {

    /**
     * Default available routes
     */
    protected static $_routes = [
        'GET' => [],
        'POST' => []
    ];
    
	/**
	 * The HTTP Request.
	 * @var Request
	 */
	private $request = null;

	/**
	 * The HTTP Response
	 * @var Response
	 */
	private $response = null;

	/**
	 * Default constructor.
	 * 
	 * @param Request request HTTP Request.
	 * @param Reposne response HTTP Response.
	 */
	public function __construct(Request &$request, Response &$response) {
		$this->request = &$request;
		$this->response = &$response;
	}

	/**
	 * Instance le controller à utiliser.
	 * Execute le controller.
	 */
	public function execute() {
        $route = self::getRoute($this->request);
        
        if(!$route) throw new PageNotExist('No route found');
        
        $path = explode('@', $route['path']);
		$controller_name = $path[0];
		$action	= $path[1];
        $args = $route['args'];

		// On essaye d'instancier le controller.
		$controller = Config::$app['namespace'] . 'Controllers\\' . $controller_name;
		if(class_exists($controller)) {
			$controller = new $controller($this->request, $this->response);
		} else {
			throw new ControllerNotExist('Controller "' . $controller . '" inexistant !');
		}

		$controller->execute($action, $args);
	}
    
    /**
     * Fonction permettant de formatter une url. 
     * L'url peut aussi bien être absolue que relative
     * Elle est notamment utilisée dans la redirection
     * @param string|array url L'url sous forme de tableau 
     * ou sous forme de chaine de caractère.
     * @return string l'adresse url complète 
     */
    public static function url($url = null) {
        if ($url !== null) {
            $host  = $_SERVER['HTTP_HOST'];
            $uri   = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            if (is_string($url)){
                // Addresse absolue 
                if (strcmp($url[0], 'http://') || strcmp($url[0], 'https://')) {
                    return $url;
                        
                // Addresse relative
                } else {
                    if (strcmp($url[0], '/')) {
                        return $host. $url;
                    } else {
                        return $host. '/'. $url;
                    }
                }
            }
            // TODO Gérer sous forme de tableau
            
        }
    }
    
    /**
     * Return the route associated to the request
     *
     * @param $request Request
     * @return Route
     */
    public static function getRoute(&$request) {
        $route = false;
        $url   = false;
        $matches = null;
        $method = $request->getMethod();
        echo $method;
        
        foreach(self::$_routes[$method] as $url => $path) {
            //if(preg_match('%' . $route . '%', $request->link, $matches)) {
            if($url == $request->link) {
                $route = [
                    'path' => $path,
                    'args' => []
                ];
                
                break;
            }
            
            $url_regex = preg_replace('%{([a-z_A-Z]+)}%', '(?P<$1>\d+)', $url, -1, $count);
            if($count > 0 && preg_match('%' . $url_regex . '%', $request->link, $matches)) {
                
                foreach($matches as $key => $value){
                    if(is_numeric($key)) unset($matches[$key]);
                }
                
                $route = [
                    'path' => $path,
                    'args' => $matches
                ];
            }
        }
        
        return $route;
    }
    
    public static function get($url_regex, $path) {
        if(isset(self::$_routes['GET'][$url_regex])) return;
        self::$_routes['GET'][$url_regex] = $path;
    }
    
    public static function post($url_regex, $path) {
        if(isset(self::$_routes['POST'][$url_regex])) return;
        self::$_routes['POST'][$url_regex] = $path;
    }

}