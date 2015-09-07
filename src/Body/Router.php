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

use \Exception;

use MerciKI\Body\View;
use MerciKI\Body\Controller;
use MerciKI\Config;
use MerciKI\Exception\PageNotExist;
use MerciKI\Exception\MerciKIException;
use MerciKI\Exception\ActionNotExist;
use MerciKI\Exception\EntityNotExist;
use MerciKI\Exception\ControllerNotExist;
use MerciKI\Exception\ModelNotExist;
use MerciKI\Network\GlobalResponse;

use Zend\Diactoros\Request;
use Zend\Diactoros\Response;

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
     * Default constructor.
     */
    public function __construct() {
    }

    /**
     * Instantiate the controller, execute it and return a response.
     * @param $request Request the request.
     * @return  $response Response to send.
     * @throws ControllerNotExist The controller doesn't exist.
     * @throws PageNotExist No route found for the request uri.
     */
    public function execute(Request $request) {
        $method = $request->getMethod();
        $argPos = strpos($request->getUri(), '?');

        if($argPos !== false && $argPos >= 0) {
            $uri    = substr($request->getUri(), 0, $argPos);
        } else {
            $uri    = $request->getUri();
        }

        $route = self::getRoute($method, $uri);
        
        if(!$route) throw new PageNotExist('No route found');
        
        $path = explode('@', $route['path']);
        $controller_name = $path[0];
        $action    = $path[1];
        $args = $route['args'];

        // Try to instantiate the controller
        $controller = Config::$app['namespace'] . 'Controllers\\' . $controller_name;
        if(class_exists($controller)) {
            $controller = new $controller($request);
        } else {
            throw new ControllerNotExist('Controller "' . $controller . '" doesn\'t exist !');
        }

        return $controller->execute($action, $args);
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
     * @param $method String the method of the Request.
     * @param $uri    String the request uri.
     * @return Route
     */
    public static function getRoute($method = null, $uri) {
        $route   = false;
        $url     = false;
        $matches = null;

        if($method == null) {
            return $route;
        }
        
        foreach(self::$_routes[$method] as $url => $path) {
            //if(preg_match('%' . $route . '%', $request->link, $matches)) {
            if($url == $uri) {
                $route = [
                    'path' => $path,
                    'args' => []
                ];
                break;
            }
            
            $url_regex = preg_replace('%{([a-z_A-Z]+)}%', '(?P<$1>\d+)', $url, -1, $count);
            if($count > 0 && preg_match('%^' . $url_regex . '$%', $uri, $matches)) {

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