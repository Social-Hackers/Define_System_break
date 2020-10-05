<?php

use DefineSystem\Route;
use DefineSystem\PreFunctions;
use DefineSystem\Db;
use DefineSystem\Cache;
use DefineSystem\Defines;
use DefineSystem\Log;
use DefineSystem\Session;
use DefineSystem\Visual;
use DefineSystem\Status;
use DefineSystem\Spl;


/**
 * non objective frame work
 * Define System
 * version 2.0.0
 *
 * Set up object of the system
 *
 * for get the instance :
 * activate()
 *
 * load system files :
 * load()
 *
 * visualize front page :
 * release()
 */
class DefineSystem {

	private $version = 'DefineSystem Version : 2.0.0'; // version

	// activation of Define System
	private static $activated_already = false;

	// setting objects
	private $configs = null;
	private $cache = null;
	private $log = null;
	private $session = null;
	private $db = null; //　data base

	// directories
	private static $defines_directory = null; // DefineSystem's base reading path
	private $plugins_directory = null;
	private $functions_directory = null;
	private $prefunctions_directory = null;
	private $actions_directory = null;

	private $config_directory = null;
	private $cache_directory = null;
	private $log_directory = null;
	private $include_directory = null;
	private $template_directory = null;
	private $public_directory = null;

	// routing
	private $route = null;

	// messaging
	private $assigns = [];
	private $msg = [];

	// pre defines
	private $predefines_file = null;

	// loader source
	private static $loader_source = null;


	/**
	 * set up method
	 * get system instance statically
	 *
	 * @return DefineSystem
	 */
    public static function activate() {
		if (self::$activated_already === false) {
			self::$defines_directory = dirname(dirname(__FILE__));
			self::$loader_source = DIRECTORY_SEPARATOR . 'functions' . DIRECTORY_SEPARATOR . 'load.php';
			self::$activated_already = true;
			return new self();
		}
	}

	/**
	 * version information
	 *
	 * @return string define system version
	 */
	public function version() {
		return $this->version;
	}


    /**
     * set your used function directory
     * this setting is recommanded
     *
     * @param string $directory
     * @return boolean
     */
	public function setFunctionsDirectory($directory) {
	    return $this->setStringPropertySingle($directory, 'functions', 'directory');
	}

	/**
	 * set your used pre function directory
	 * you can set the file path of usually called functions
	 *
	 * @param string $directory
	 * @return boolean
	 */
	public function setPrefunctionsDirectory($directory) {
	    $remained = (is_string($directory) && ! preg_match("/^\\".DIRECTORY_SEPARATOR."/", $directory)) ? DIRECTORY_SEPARATOR . $directory : $directory;
	    return $this->setStringPropertySingle($remained, 'prefunctions', 'directory');
	}

	/**
	 * set your action class' directory
	 * this setting is optional
	 *
	 * @param string $directory
	 * @return boolean
	 */
	public function setActionsDirectory($directory) {
	    return $this->setStringPropertySingle($directory, 'actions', 'directory');
	}

    /**
     * set your configs directory
     * this setting is optional
     * and when you use database connection,it is necessary setting on your configs
     *
     * @param string $string
     * @return boolean
     */
	public function setConfigsDirectory($string) {
	    return $this->setStringPropertySingle($string, 'config', 'directory');
	}

	/**
	 * set your cache directory
	 * this setting is optional
	 *
	 * @param string $string
	 * @return boolean
	 */
	public function setCacheDirectory($string) {
	    return $this->setStringPropertySingle($string, 'cache', 'directory');
	}

	/**
	 * set your log directory
	 * this setting is optional
	 *
	 * @param string $string
	 * @return boolean
	 */
	public function setLogDirectory($string) {
	    return $this->setStringPropertySingle($string, 'log', 'directory');
	}

	/**
	 * set your include directory
	 * this setting is optional
	 *
	 * @param string $string
	 * @return boolean
	 */
	public function setIncludeDirectory($string) {
	    return $this->setStringPropertySingle($string, 'include', 'directory');
	}

	public function setTemplateDirectory($string) {
	    return $this->setStringPropertySingle($string, 'template', 'directory');
	}

	/**
	 * use when you install composer and its plugins
	 * you can assign its Vendor directory
	 *
	 * @param string $string
	 * @return boolean
	 */
	public function setPluginsDirectory($string) {
	    return $this->setStringPropertySingle($string, 'plugins', 'directory');
	}

	/**
	 * set your pre defines file path
	 * this setting is optional
	 * and it is necessary for changing define system's default settings
	 *
	 * @param string $file
	 * @return boolean
	 */
	public function setPredefinesFile($file) {
	    return $this->setStringPropertySingle($file, 'predefines', 'file');
	}

    /**
     * call your used action class
     *
     * @param string $name
     * @param any $arg
     * @return (action class)
     */
	public function action($name, $arg = null) {
		if (! Status::statusLoading()) {
			$this->msg[] = '[Define Warnning] you can use action class after loading';
			return null;
		}

		return $this->actionClass($name, $arg);
	}

	public function read($path) {
	    if (! Status::statusLoading()) {
	        $this->msg[] = 'cannot include path,loading is required at first';
	        return;
	    }
	    if (is_null($this->include_directory)) {
	        $this->msg[] = 'please set include directpry for reading';
	        return;
	    }

	    DefineSystem\loadIncludeFile($this->include_directory, $path);
	}

	/**
	 * returns Define System's measage
	 *
	 * @return array
	 */
	public function msg() {
		return $this->msg;
	}

	/**
	 * loading is the step of integrating system files from Define System and your set directories' files
	 * functional directories have to be set before loading
	 *
	 * @throws Exception503
	 * @throws Exception
	 */
	public function load() {

	    try {
	        if (class_exists('DefineSystem\Status') && Status::statusLoading()) {
	            throw new Exception503('[Defines Error] cannot load system files,already loaded');
	        }

	        // loader
	        $this->loaderSource();

	        // pre defines
	        $pre_defines = [];
	        if (! is_null($this->predefines_file)) {
	            $read_pre_defines_file = DefineSystem\loadIniFile($this->predefines_file, false);
	            if (! is_null($read_pre_defines_file)) {
	                $pre_defines = $read_pre_defines_file;
	            } else {
	                $this->msg[] = '[Defines Warnning] cannot read pre defines file : ' . $this->predefines_file;
	            }
	        }

	        // default
	        if (! DefineSystem\loadDefault(self::$defines_directory, $pre_defines)) {
	            throw new Exception('[Defines Error] cannot load default settings');
	        }
	        // defines
	        if (! DefineSystem\loadDefines(self::$defines_directory)) {
	            throw new Exception('[Defines Error] cannot load define sytem files');
	        }
	        // configs
	        if (! is_null($this->config_directory)) {
	            $this->configs = DefineSystem\loadConfigs($this->config_directory);
	        }

	        // log
	        if (! is_null($this->log_directory)) {
	            $log_config = isset($this->configs[DefineSystem\LOG_CONFIG_FILE]) ? $this->configs[DefineSystem\LOG_CONFIG_FILE] : [];
	            DefineSystem\logSettings($this->log_directory, $log_config);
	        }
	        // cache
	        if (! is_null($this->cache_directory)) {
	            DefineSystem\cacheSettings($this->cache_directory, $this->configs);
	        }
	        // session
	        if (DefineSystem\SESSION_STYLE == 'db' && ! isset($this->configs[DefineSystem\DB_CONFIG_FILE][DefineSystem\DB_SESSION_CONFIG_KEY]['db'])) {
	            $this->msg[] = '[Defines Warnning] table name for using db session is not available.please make sure of your config settings for db session';
	        }
	        DefineSystem\sessionSettings($this->db(DefineSystem\DB_SESSION_CONFIG_KEY));

	        // route
	        $this->routeClass();
	        $this->routing();
	        // functions
	        if (! is_null($this->functions_directory)) {
	            DefineSystem\loadFunctions($this->functions_directory, $this->route->defaultFunction());
	            if (! is_null($this->prefunctions_directory)) {
	                DefineSystem\loadFunctions($this->functions_directory . $this->prefunctions_directory);
	            }
	        } else {
	            $this->msg[] = '[Defines Warnning] functions loading is recommanded : please use setFunctionsDirectory() mehod';
	        }
	        // actions
	        if (! is_null($this->actions_directory)) {
	            DefineSystem\splSettings($this->actions_directory);
	            DefineSystem\actionBaseSetting($this);
	        }
	        // plugins
	        if (! is_null($this->plugins_directory)) {
	            DefineSystem\loadPlugins($this->plugins_directory);
	        } else {
	            DefineSystem\loadPlugins($this->route->documentRoot());
	        }


	    } catch (Exception503 $e) {
	        $this->msg[] = $e->getMessage();
	    } catch (Exception $e) {

	        http_response_code(503);
	        $this->msg[] = $e->getMessage();
	        print 'failed to loading! please make sure Define System files are not broken<br>';

	    }

	    Status::changeIntoLoaded();
	}

	/**
	 * releasing is the step of revealing your front page
	 * and loading is required before releasing
	 *
	 * @throws Exception
	 * @throws Exception503
	 * @throws Exception404
	 */
	public function release() {

	    try {
	        if (! class_exists('DefineSystem\Status')) {
	            throw new Exception('loading is required before releasing,please use load() method');
	        }
	        if (! Status::statusLoading()) {
	            throw new Exception503('loading is required before releasing,please use load() method');
	        }
	        if (Status::statusReleasing()) {
	            $this->msg[] = '[Defines Warnning] cannot release site page,alread released';
	            return;
	        }
	        if (Status::statusCode() >= 400) {
	            throw new Exception503('[Defines Error] cannot release on status error.please check on error output');
	        }
	        if (is_null($this->route)) {
	            throw new Exception503('[Defines Error] something is wrong from system set up.please check on define system folder or reinstall that.');
	        }
	        $route = $this->route->routing();

	        switch (DefineSystem\ROUTING_TYPE) {
	            case 'path':
	                if (! $this->visualize($route)) {
	                    throw new Exception404('cannot visualize route');
	                }
	                break;
	            case ROUTING_CGI:
	                break;
	            case ROUTING_SMARTY:
	                break;
	            default:
	        }


	        if (DefineSystem\SESSIOIN_START) {
	            Session::close();
	        }
	    } catch (Exception404 $e) {
	        $this->visualizeGuidance();
	    } catch (Exception503 $e) {
	        $this->visualizeNotification();
	    } catch (Exception $e) {

	        http_response_code(503);
	        $this->msg[] = $e->getMessage();
	        $this->visualizeNotification();

	    }

	    Status::changeIntoReleased();
	}

	/**
	 * returns your set config
	 *
	 * @param string $index
	 * @param string $key
	 * @return array
	 */
	public function configs($index, $key = null) {
	    $configs = $this->propertyValueSingle('configs', $index);
	    if ($configs !== '') {
	    	if (! is_null($key) &&  isset($configs[$key])) {
	    		$configs = $configs[$key];
	    	}
	    } else {
	    	$configs = [];
	    }
	    return $configs;
	}

	/**
	 * load configs from your set directory directly
	 *
	 * @param array $configs
	 * @return array
	 */
	public function configsTarget($configs) {
	    if (! Status::statusLoading()) {
	        return [];
	    }
	    if (is_null($this->config_directory)) {
	        return [];
	    }
	    return DefineSystem\loadConfigs($this->config_directory, $configs);
	}

	/**
	 * returns gets parameters of url
	 *
	 * @return array
	 */
	public function gets() {
	    if (is_null($this->route)) {
	        return [];
	    }

	    return $this->route->GETS();
	}

	/**
	 * returns posts data sent to the destination
	 *
	 * @return array
	 */
	public function posts() {
	    if (is_null($this->route)) {
	        return [];
	    }

	    return $this->route->POSTS();
	}

	/**
	 * returns data set to the server
	 *
	 * @return array
	 */
	public function servers() {
	    if (is_null($this->route)) {
	        return [];
	    }

	    return $this->route->SERVERS();
	}

	/**
	 * write Define System's measage to a log file
	 *
	 * @param string $file_key
	 */
	public function msgLogging($file_key = null) {
		if (! class_exists('DefineSystem\Log')) {
			$this->msg[] = 'Log class is not found';
			return;
		}
		if (! is_null($file_key) && ! DefineSystem\is_string_numeric($file_key)) {
			$this->msg[] = 'incorrect file key';
			return;
		}

		foreach ($this->msg as $msg) {
		    Log::logging($msg, $file_key);
		}
	}

	/**
	 * db connection
	 * for the usage of db set,loading and db access setting on your configs is required
	 *
	 * @param string $key
	 * @return Db
	 */
	public function db($key) {

	    if (! class_exists('DefineSystem\Status') || ! class_exists('DefineSystem\Db')) {
			$this->msg[] = '[Defines Error] you can use DB set after loading';
			return null;
		}
		if (! DefineSystem\is_string_numeric($key)) {
	        $this->msg[] = '[Defines Error] database set key input is not correct : '.print_r($key, true);
	        return null;
	    }

	    if (! isset($this->configs[DefineSystem\DB_CONFIG_FILE][$key])) {
	        $this->msg[] = '[Defines Error] No database set was found : ' .$key;
	        return null;
	    }
	    $db_set = $this->configs[DefineSystem\DB_CONFIG_FILE][$key];
	    if (! isset($db_set[DefineSystem\DB_DRIVER_KEY])
	        || ! isset($db_set[DefineSystem\DB_USER_KEY])
	        || ! isset($db_set[DefineSystem\DB_PASS_KEY])
	        || ! isset($db_set[DefineSystem\DB_HOST_KEY])) {
	        $this->msg[] = '[Defines Error] some settings are missong from database set : ' . $key;
	        return null;
	    }

	    $db = Db::activate();
	    $db->connect(
	        $db_set[DefineSystem\DB_DRIVER_KEY],
	        $db_set[DefineSystem\DB_USER_KEY],
	        $db_set[DefineSystem\DB_PASS_KEY],
	        $db_set[DefineSystem\DB_HOST_KEY],
	        ! empty($db_set[DefineSystem\DB_PORT_KEY]) ? $db_set[DefineSystem\DB_PORT_KEY] : null,
	        ! empty($db_set[DefineSystem\DB_NAME_KEY]) ? $db_set[DefineSystem\DB_NAME_KEY] : null
	    );
	    return $db;
	}

	/**
	 * import your function files adding to your path's function
	 *
	 * @param string $functions
	 */
	public function useFunctions($functions) {
	    if (! is_array($functions)) {
	        $this->msg[] = '[Defines Warnning] : input functions subloading is required as array';
	        return;
	    }
	    if (! Status::statusLoading()) {
	        return;
	    }
	    DefineSystem\loadFunctions($this->functions_directory, $functions);
	}

    /**
     * loader class luncher
     *
     * @throws Exception
     */
	private function loaderClass() {
		$directoryPath = self::$defines_directory.'/core/';
		$path = $directoryPath.self::LOADER_FILE;

		if (!is_file($path)) {
			throw new Exception();
		}
		require_once $path;
		if (!class_exists('DefineSystem\Loader')) {
			throw new Exception();
		}
		$this->loader = new Loader();
	}

	/**
	 * returns loader class' path
	 *
	 * @throws Exception
	 */
	private function loaderSource() {
	    $path = self::$defines_directory . self::$loader_source;

	    if (! is_file($path)) {
	        throw new Exception('cannot setup loader');
	    }
	    require_once $path;
	}

	/**
	 * set routing
	 *
	 * @throws Exception503
	 */
	private function routing() {
	    if (is_null($this->route)) {
	        throw new Exception503('oops, something is wrong with defines loading');
	    }
	    if (! empty($this->configs[DefineSystem\ROUTE_CONFIG_FILE])) {
	        $routing_set = $this->configs[DefineSystem\ROUTE_CONFIG_FILE];
	        $this->routing_set($routing_set);
	    }
	}

	/**
	 * routing set
	 *
	 * @param array $routing_set
	 */
	private function routing_set($routing_set) {
	    foreach ($routing_set as $base_route => $target_route) {
	        $base_route = trim((string)$base_route);
	        $target_route = trim((string)$target_route);

	        if (! DefineSystem\is_single_word($base_route)) {
	            $this->msg[] = 'Incorrect base url,one setting was skiped : ' . print_r($target_route, true);
	            continue;
	        }
	        if (! DefineSystem\is_single_word($target_route)) {
	            $this->msg[] = 'Incorrect target url,one setting was skiped : ' . print_r($target_route, true);
	            continue;
	        }

            $this->route->setRoute($base_route, $target_route);
	    }
	}

	/**
	 * set up the Route class
	 *
	 * @throws Exception503
	 */
	private function routeClass() {
		if (! class_exists('DefineSystem\Route')) {
			throw new Exception503('route class could not set up');
		}
		$route = new Route();
		$route->activate();

		$this->route = $route;
	}

	/**
	 * Setting for actions class' base
	 */
	private function definesClassSettings() {
		if (class_exists('DefineSystem\Defines')) {
		    DefineSystem\Defines::setSystem($this);
		}
	}

	/**
	 * call for action class
	 *
	 * @param string $name
	 * @param any $arg
	 * @return (action class)
	 */
	private function actionClass($name, $arg) {
	    if (! Spl::issetSpl($name)) {
	        return null;
	    }
	    $action = is_null($arg) ? new $name() : new $name($arg);
	    return $action;
	}

	/**
	 * revealing the front page object
	 *
	 * @param string $route
	 * @param boolean $guidance_visualize
	 * @return boolean
	 */
	private function visualize($route, $guidance_visualize = false) {
	    if (! is_file($route)) {
	        $this->msg[] = 'route is not exists : '.print_r($route, true);
	        return false;
	    }
	    if ($guidance_visualize === false) {
	        if (strpos($route, self::$defines_directory) !== false) {
	            $this->msg[] = 'cannot set route on your defines directory : '.print_r($route, true);
	            return false;
	        }
	        if (strpos($route, $this->route->scriptFileName()) !== false) {
	            $this->msg[] = 'cannot set route on your define index file : '.print_r($route, true);
	            return false;
	        }
	        if (! empty($this->functions_directory) && strpos($route, realpath($this->functions_directory)) !== false) {
	            $this->msg[] = 'cannot set route on your functions directory : '.print_r($route, true);
	            return false;
	        }
	        if (! empty($this->actions_directory) && strpos($route, realpath($this->actions_directory)) !== false) {
	            $this->msg[] = 'cannot set route on your actions directory : '.print_r($route, true);
	            return false;
	        }
	        if (! empty($this->config_directory) && strpos($route, realpath($this->config_directory)) !== false) {
	            $this->msg[] = 'cannot set route on your config directory : '.print_r($route, true);
	            return false;
	        }
	        if (! empty($this->cache_directory) && strpos($route, realpath($this->cache_directory)) !== false) {
	            $this->msg[] = 'cannot set route on your cache directory : '.print_r($route, true);
	            return false;
	        }
	        if (! empty($this->log_directory) && strpos($route, realpath($this->log_directory)) !== false) {
	            $this->msg[] = 'cannot set route on your log directory : '.print_r($route, true);
	            return false;
	        }
	        if (! empty($this->predefines_file) && strpos($route, realpath($this->predefines_file)) !== false) {
	            $this->msg[] = 'cannot set route on your predefines file : '.print_r($route, true);
	            return false;
	        }
	        if (! empty($this->include_directory) && strpos($route, realpath($this->include_directory)) !== false) {
	            $this->msg[] = 'cannot set route on your include directory : '.print_r($route, true);
	            return false;
	        }
	    }

		$visual = Visual::activate();
		$visual->space($route, $this);
		return true;
	}

	private function visualizeCgi($route) {}

	private function visualizeSmarty($route) {}

	/**
	 * revealing not found page
	 */
	private function visualizeGuidance() {
	    $guidance = $this->route->guidanceUri();
	    if (! $this->visualize($guidance, true)) {
	        $this->msg[] = 'oops! guidance uri was not found neither';
	        print_r(DefineSystem\NOT_FOUND_MESSAGE);
	    }
	}

	/**
	 * revealing service unavailable page
	 */
	private function visualizeNotification() {
	    if (is_null($this->route)) {
	        print 'oops! somethig is wrong!';
	        return;
	    }
	    $notification = $this->route->notificationUri();
	    if (! $this->visualize($notification, true)) {
	        $this->msg[] = 'oops! notifucation uri was not found neither';
	        print_r(DefineSystem\SERVICE_UNAVAILABLE_MESSAGE);
	    }
	}

	/**
	 * class property setting
	 *
	 * @param string $string
	 * @param string $name
	 * @param string $entry
	 * @return boolean
	 */
	private function setStringPropertySingle($string, $name, $entry = null) {
	    if (! $this->isTypicalString($string, true)) {
	        $this->msg[] = '[Defines Warnning] cannot set ' . $name . ' ' . $entry . ' name.Please enter alpha numeric charactor : ' . print_r($string, true);
	        return false;
	    }

	    $property = $entry ? $name . '_' . $entry : $name;
	    if (! is_null($this->{$property})) {
	        $this->msg[] = $name . ' ' . $entry . ' is already set';
	        return false;
	    }
	    $value = preg_replace("/\\".DIRECTORY_SEPARATOR."$/", '', $string);
	    $this->{$property} = $value;
	    return true;
	}

	/**
	 * retunrs property value
	 *
	 * @param string $name
	 * @param string $key
	 * @return string
	 */
	private function propertyValueSingle($name, $key) {
		if (isset($this->{$name}[$key])) {
			return $this->{$name}[$key];
		}
		return '';
	}

	/**
	 * strictky checked string value
	 *
	 * @param string $str
	 * @param boolean $strict
	 * @return boolean
	 */
	private function isTypicalString($str, $strict = false) {
		if (!is_string($str)) {
			return false;
		}
		if (strlen($str) === 0) {
			return false;
		}
		if ($strict) {
			if (!preg_match("/\A[(a-z)|(0-9)|(_)|(-)|(\\" . DIRECTORY_SEPARATOR . ")|(\.)]+\z/isu", $str)) {
				return false;
			}
		}

		return true;
	}
}


