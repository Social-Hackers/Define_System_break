<?php

namespace DefineSystem;

/**
 * Defines System Class for visualize
 *
 * usage
 *
 * import functions :
 * useFunctions()
 *
 * import configs :
 * useConfigs()
 *
 * import includes :
 * read()
 *
 * call action :
 * action()
 *
 * call db :
 * db()
 *
 * read configs :
 * configs()
 *
 * write log :
 * log()
 *
 * write info log :
 * info()
 *
 * write error log :
 * error()
 *
 * write/read cache :
 * cache()
 *
 * read system measage :
 * msg()
 */
class Visual {

	private $system = null;

	private $gets;
	private $posts;
	private $server;

	/**
	 * get instace statically
	 *
	 * @return Visual
	 */
    public static function activate() {
        return new self;
    }

    /**
     * revealed space of the page
     *
     * @param string $route
     * @param DefineSystem $system
     */
    public function space($route, $system = null) {
    	if (! is_null($system) && $system instanceof \DefineSystem) {

            $this->system = $system;
            $this->gets = $system->gets();
            $this->posts = $system->posts();
        }
        unset($system);

        foreach (Assigns::value() as $index => $value) {
            ${$index} = $value;
        }
        Assigns::erase();

        require_once $route;
    }

    /**
     * import functions from the system
     *
     * @param string $functions
     */
    public function useFunctions($functions) {
        if (! is_null($this->system)) {
            $this->system->useFunctions($functions);
        }
    }

    /**
     * import configs from the system
     *
     * @param string $configs
     * @return array
     */
    public function useConfigs($configs) {
        if (! is_null($this->system)) {
            return $this->system->configsTarget($configs);
        }
        return [];
    }

    /**
     * import files from includes
     *
     * @param string $path
     */
    public function read($path) {
        if (! is_null($this->system)) {
            $this->system->read($path);
        }
    }

    /**
     * call action class from the system
     *
     * @param string $name
     * @return (action class)
     */
    public function action($name) {
    	if (! is_null($this->system)) {
    		return $this->system->action($name);
    	}
    	return null;
    }

    /**
     * call db connection from the system
     *
     * @param string $key
     * @return Db
     */
    public function db($key) {
    	if (! is_null($this->system)) {
    		return $this->system->db($key);
    	}
    	return null;
    }

    /**
     * returns configs from the system
     *
     * @param string $index
     * @param string $key
     * @return array
     */
    public function configs($index, $key = null) {
    	if (! is_null($this->system)) {
    		return $this->system->configs($index, $key);
    	}
    	return null;
    }

    /**
     * write log by Log Class of Define System
     *
     * @param string $loginfo
     * @param string $name
     * @param string $type
     */
    public function log($loginfo, $name = null, $type = null) {
        if (class_exists('DefineSystem\Log')) {
            Log::logging($loginfo, $name, $type);
        }
    }

    /**
     * write info log by Log Class of Define System
     *
     * @param string $loginfo
     * @param string $name
     */
    public function info($loginfo, $name = null) {
        if (class_exists('DefineSystem\Log')) {
            Log::info($loginfo, $name);
        }
    }

    /**
     * write error log by Log Class of Define System
     *
     * @param string $loginfo
     * @param string $name
     */
    public function error($loginfo, $name = null) {
        if (class_exists('DefineSystem\Log')) {
            Log::error($loginfo, $name);
        }
    }

    /**
     * write or read cache info
     *
     * @param string $key
     * @param string $file
     * @param any $cache_info
     * @return (cache info)
     */
    public function cache($key, $file, $cache_info = null) {

        if (is_null($cache_info)) {
            return Cache::cargo($key, $file);
        } else {
            Cache::store($cache_info, $key, $file);
        }
    }

    /**
     * returns system measage
     *
     * @return array
     */
    public function msg() {
    	if (! is_null($this->system)) {
    		return $this->system->msg();
    	}
    	return [];
    }

}