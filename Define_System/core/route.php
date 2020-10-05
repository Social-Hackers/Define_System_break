<?php

namespace DefineSystem;

/**
 * Define System Class for routing
 *
 */
class Route {

	private static $activated_already = false;
	private $index_file_name = null;
	private $routing_table = [];
	private $routing_table_uri = [];
	private $msg = [];

	private $request_uri = null;
	private $request_method = null;
	private $script_name = null;
	private $script_file_name = null;
	private $query_string = null;
	private $document_root = null;
	private $query_params = [];
	private $get_params = [];
	private $post_params = [];
	private $server_params = [];

	/**
	 * get instace statically
	 *
	 * @return Route
	 */
	public function activate() {
		if (self::$activated_already === false) {
		    $this->indexFileName();
			$this->requestUri();
			$this->requestMethod();
			$this->scriptName();
			$this->scriptFileNameS();
			$this->queryString();
			$this->documentRootS();
			$this->queryParams();
			$this->getParams();
			$this->postParams();
			$this->serverParams();

			self::$activated_already = true;
		}
	}

	/**
	 * routing path
	 *
	 * @return string
	 */
	public function routing() {

	    if(! $this->routingValidation()) {
	        return null;
	    }

		$route = is_null($this->query_string) ? $this->request_uri : str_replace('?'.$this->query_string, '', $this->request_uri);

		if (preg_match("/\\".DIRECTORY_SEPARATOR."$/", $route)) {
		    $route = preg_replace("/\\".DIRECTORY_SEPARATOR."$/", DIRECTORY_SEPARATOR.$this->index_file_name, $route);
		} elseif (preg_match("/\.".URI_FILE_EXTENSION."$/", $route)) {
		    $route = preg_replace("/\.".URI_FILE_EXTENSION."$/", '', $route);
		} else {
		    return null;
		}
		$route = eraseTraverse($route) . '.' . DEFAULT_FILE_EXTENSION;

		// routing
		$route = $this->routingPath($route);

		if (ROUTING_TYPE == 'path') {
		    $route = $this->document_root.$route;
		}
		$route = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $route);

		return $route;
	}

	/**
	 * routing guidance uri
	 *
	 * @return string
	 */
	public function guidanceUri() {
	    $script = $this->document_root.'/'.GUIDANCE_URI.'.'.DEFAULT_FILE_EXTENSION;
	    $script = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $script);

		return $script;
	}

	/**
	 * routing service anavailable uri
	 *
	 * @return string
	 */
	public function notificationUri() {
	    $script = $this->document_root.'/'.NOTIFICATION_URI.'.'.DEFAULT_FILE_EXTENSION;
	    $script = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $script);

	    return $script;
	}

	public function defaultLogUri() {
	    return $this->document_root.'/log';
	}

	public function defaultCacheUri() {
	    return $this->document_root.'/cache';
	}

	/**
	 * routing set
	 *
	 * @param string $baseUri
	 * @param string $route
	 */
	public function setRoute($baseUri, $route) {
		$this->routing_table[$baseUri] = $route;
	}

	public function setRouteUri($base_route, $route) {
	    $this->routing_table_uri[$base_route] = $route;
	}

	/**
	 * routing default function for current path
	 *
	 * @return array
	 */
	public function defaultFunction() {

	    $base = is_null($this->query_string) ? $this->request_uri : str_replace('?'.$this->query_string, '', $this->request_uri);
	    $base = preg_replace("/\\".DIRECTORY_SEPARATOR."$/", DIRECTORY_SEPARATOR.$this->index_file_name, $base);

	    if (empty($base)) {
	        return null;
	    }

	    $paths = [];
	    $split = explode(DIRECTORY_SEPARATOR, dirname($base));
	    for ($i=count($split); $i>=0; $i--) {
	        if ($i == count($split)) {
	            $name = empty($split[$i-1]) ? 0 : $split[$i-1];
	            $paths[$name] = basename($base, '.'.URI_FILE_EXTENSION);
	        } elseif (! empty($split[$i]) && strpos($split[$i], '..') === false) {
	            $paths[$split[$i]] = $paths;
	        }
	    }

	    return $paths;
	}

	/**
	 * index file for directory top
	 */
	private function indexFileName() {
	    $this->index_file_name = strpos(INDEX_FILE, DIRECTORY_SEPARATOR) !== false ? basename(INDEX_FILE) : INDEX_FILE;
	}

	/**
	 * read request uri from server params
	 */
	private function requestUri() {
		if (isset($_SERVER['REQUEST_URI'])) {
			$this->request_uri = $_SERVER['REQUEST_URI'];
		}
	}

	/**
	 * read request method from server params
	 */
	private function requestMethod() {
		if (isset($_SERVER['REQUEST_METHOD'])) {
			$this->request_method = $_SERVER['REQUEST_METHOD'];
		}
	}

	/**
	 * read script name from server params
	 */
	private function scriptName() {
		if (isset($_SERVER['SCRIPT_NAME'])) {
			$this->script_name = $_SERVER['SCRIPT_NAME'];
		}
	}

	/**
	 * read script file name from server params
	 */
	private function scriptFileNameS() {
		if (isset($_SERVER['SCRIPT_FILENAME'])) {
			$this->script_file_name = $_SERVER['SCRIPT_FILENAME'];
		}
	}

	/**
	 * read query string from server params
	 */
	private function queryString() {
		if (isset($_SERVER['QUERY_STRING'])) {
			$this->query_string = $_SERVER['QUERY_STRING'];
		}
	}

	/**
	 * set document root
	 */
	private function documentRootS() {
	    $this->document_root = str_replace($this->script_name, '', $this->script_file_name);
	}

	/**
	 * set query params from current uri
	 */
	private function queryParams() {
		if (!is_null($this->query_string)) {
			$queryParams = array();
			$tmpQuery = array_filter(explode('&', $this->query_string));

			foreach ($tmpQuery as $value) {
				if (strpos($value, '=') !== false) {
					$arrTemp = explode('=', $value);
					$queryParams[$arrTemp[0]] = $arrTemp[1];
				} else {
					$queryParams[] = $value;
				}
			}
			$this->query_params = $queryParams;
		}
	}

	/**
	 * set get parameters
	 */
	private function getParams() {
		if (isset($_GET)) {
		    $this->setParams($_GET, 'get');
		}
	}

	/**
	 * set post parameters
	 */
	private function postParams() {
		if (isset($_POST)) {
		    $this->setParams($_POST, 'post');
		}
	}

	/**
	 * set server parameters
	 */
	private function serverParams() {
		if (isset($_SERVER)) {
			$this->setParams($_SERVER, 'server');
		}
	}

	/**
	 * set parameters
	 *
	 * @param array $params
	 * @param string $name
	 */
	private function setParams(&$params, $name) {
	    if (is_array($params)) {
	        $property = $name . '_params';
	        foreach ($params as $key => $value) {
	            if (is_object($key) || is_array($key) || is_object($value) || is_array($value)) {
	                $this->msg[] = 'couldn\'t set some ' . $name . 'params.Please ensure server settings';
	            }

	            $this->{$property}[$key] = $value;
	        }

	        if (SERVER_PARAMS_DESTRUCT) {
	            $params = [];
	        }
	    }
	}

	/**
	 * check the set parameters for routing
	 */
	private function routingValidation() {
	    if (is_null($this->request_uri)) {
	        $this->msg[] = 'Define System cannot scan request uri of server parameter.Please ensure server settings';
	        return false;
	    }
	    if (is_null($this->script_name)) {
	        $this->msg[] = 'Define System cannot scan script name of server parameter.Please ensure server settings';
	        return false;
	    }
	    if (is_null($this->script_file_name)) {
	        $this->msg[] = 'Define System cannot scan script file name of server parameter.Please ensure server settings';
	        return false;
	    }
	    return true;
	}

	/**
	 * routing
	 *
	 * @param string $route
	 * @return string
	 */
	private function routingPath($route) {

	    switch (ROUTING_TYPE) {
	        case 'path':
	            foreach ($this->routing_table as $base => $path) {
	                if ($route == $base) {
	                    return $path;
	                }
	            }
	            break;
	        case 'smarty':

	            break;
	    }

	    return $route;
	}

	/**
	 * returns gets parameters
	 *
	 * @return array
	 */
	public function GETS() {
		return $this->get_params;
	}

	/**
	 * returns posts parameters
	 *
	 * @return array
	 */
	public function POSTS() {
		return $this->post_params;
	}

	/**
	 * returns server parameters
	 *
	 * @return array
	 */
	public function SERVERS() {
		return $this->server_params;
	}

	/**
	 * returns document root
	 *
	 * @return array
	 */
	public function documentRoot() {
	    return $this->document_root;
	}

	/**
	 * returns script file name
	 *
	 * @return array
	 */
	public function scriptFileName() {
	    return $this->script_file_name;
	}
}
