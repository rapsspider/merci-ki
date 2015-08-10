<?php
/**
 * Copyright (c) 2015  Jason BOURLARD
 *                     Quentin DOUZIECH
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @author      Jason BOURLARD<jason.bourlard@gmail.com>
 * @author      Quentin DOUZIECH
 *
 * @copyright   Copyright (c) 2015  Jason BOURLARD, Quentin DOUZIECH
 * @since       1.0.0
 * @license     http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace MerciKI\Network;
use MerciKI\Config;

/**
 * This class represent an HTTP Request.
 *
 * It gets a method (GET, POST, PUT, ...) and can contains many
 * arguments (GET Data) and many data (POST Data).
 *
 */
class Request {
    
    /**
     * Array of GET data.
     *
     * @var array
     */
    public $arguments = array();
    
    /**
     * HTTP request method
     *
     * @var string
     */
    public $_method = 'GET';
	
    /**
     * Array of POST data. Will contain form data as well as uploaded files.
     * In PUT/PATCH/DELETE requests this property will contain the form-urlencoded
     * data.
     *
     * @var array
     */
	public $data = array();

    /**
     * The URL string used for the request.
     *
     * @var string
     */
	public $link;

    /**
     * Base URL path.
     *
     * @var string
     */
	public $base = false;

    /**
     * The full address to the current request
     *
     * @var string
     */
	public $here = null;
	
    /**
     * Copy of php://input. Since this stream can only be read once in most SAPI's
     * keep a copy of it so users don't need to know about that detail.
     *
     * @var string
     */
	protected $_inputs = '';
	
    /**
     * The built in detectors used with `is()` can be modified with `addDetector()`.
     *
     * There are several ways to specify a detector, see Cake\Network\Request::addDetector() for the
     * various formats and ways to define detectors.
     *
     * @var array
     */
	protected $_detectors = array(
		'get' => array('env' => 'REQUEST_METHOD', 'value' => 'GET'),
		'post' => array('env' => 'REQUEST_METHOD', 'value' => 'POST'),
		'put' => array('env' => 'REQUEST_METHOD', 'value' => 'PUT'),
		'delete' => array('env' => 'REQUEST_METHOD', 'value' => 'DELETE'),
		'head' => array('env' => 'REQUEST_METHOD', 'value' => 'HEAD'),
		'ajax' => array('env' => 'HTTP_X_REQUESTED_WITH', 'value' => 'XMLHttpRequest')
	);
	
    /**
     * Create a new request object.
     *
     * You can supply the data as either an array or as a string.  If you use
     * a string you can only supply the URL for the request.  Using an array will
     * let you provide the following keys:
     *
     * - `post` POST data or non query string data
     * - `query` Additional data from the query string.
     * - `files` Uploaded file data formatted like $_FILES.
     * - `cookies` Cookies for this request.
     * - `environment` $_SERVER and $_ENV data.
     * - `url` The URL without the base path for the request.
     * - `base` The base URL for the request.
     * - `webroot` The webroot directory for the request.
     * - `input` The data that would come from php://input this is useful for simulating
     * - `session` An instance of a Session object
     *   requests with put, patch or delete data.
     *
     * @param string|array $config An array of request data to create a request with.
     */
	public function __construct($link = null,  $parseEnvironment = true) {
		if ($parseEnvironment) {
			$this->_processPost();
			$this->_processGet();
		}
        $this->base($link);
	}
	
    /**
     * Sets the REQUEST_METHOD environment variable based on the simulated _method
     * HTTP override value. The 'ORIGINAL_REQUEST_METHOD' is also preserved, if you
     * want the read the non-simulated HTTP method the client used.
     *
     * Set the POST data on the $data attribute and use the fonction stripslashes_deep
     * on this data if magic_quotes_gpc equals to 1.
     */
	protected function _processPost() {
		if ($_POST) {
			$this->data = $_POST;
            $this->_method = 'POST'; // Set the method by default.
		}
		
		if (ini_get('magic_quotes_gpc') === '1') {
			$this->data = stripslashes_deep($this->data);
		}
        
        // If the request has been passed using the POST data.
        if(isset($this->data['_method'])) {
            if (!empty($_SERVER)) {
                $_SERVER['REQUEST_METHOD'] = $this->data['_method'];
            } else {
                $_ENV['REQUEST_METHOD'] = $this->data['_method'];
            }
            unset($this->data['_method']);
        }

		/**
		 * Delete the data index.
		 */
		if (is_array($this->data) && isset($this->data['data'])) {
			$dataToUse = $this->data['data'];
            
            // If it exists another column different of data.
			if (count($this->data) <= 1) {
				$this->data = $dataToUse;
                
            // We merge the data.
			} else {
				unset($this->data['data']);
				$this->data = array_merge($this->data, $dataToUse);
			}
		}
	}
	

    /**
     * Process the GET parameters and move things into the object.
     *
     * Set the GET data on the $arguments attribute and use the fonction stripslashes_deep
     * on this data if magic_quotes_gpc equals to 1.
     */
	protected function _processGet() {
		if (ini_get('magic_quotes_gpc') === '1') {
			$this->arguments = stripslashes_deep($_GET);
		} else {
			$this->arguments = $_GET;
		}
	}
    
    /**
     * Check whether or not a Request is a certain type.
     *
     * @param string|array $type The type of request you want to check. If an array
     *   this method will return true if the request matches any type.
     * @return bool Whether or not the request is the type you are checking.
     */
	public function is($type) {
		return isset($this->_detectors[$type]) 
		       && $_SERVER[$this->_detectors[$type]['env']] == $this->_detectors[$type]['value'];
	}

	/**
     * @TOBETRANSLATED
     *
	 * Initialise les vars base et link.
	 * Par defaut, un link peut contenir un controller et une action et peut être
	 * suivis par un id de type entier. Ensuite, il peut suivre des arguments indéxés
	 * par une chaine de caractères suivis de sa value.
	 * Exemple :
	 *     http://127.0.0.1/index.php?_url=/controller/action/a=b
	 *     http://127.0.0.1/index.php?_url=/controller/action/un_argument_index_0
	 *     http://127.0.0.1/index.php?_url=/controller/action/68                  // id = 68
     
	 * @param $link null|string Contient la chaine de caractère représentant l'adresse
	 *                          à utiliser.
	 */
	public function base($link = null) {
        if($link != null) {
			$_url = "_url=";
			$_GET['_url'] = substr($link, strpos($link, $_url) + strlen($_url));
        }

		if(!isset($_GET['_url'])) {
			$this->link = '/';
		} else {
			$this->link = $_GET['_url'];
		}
        
        if(isset($this->data['_method'])) {
            $this->_method = $this->data['_method'];
        }
	}
    
    public function getMethod() {
        return $this->_method;
    }
}

?>