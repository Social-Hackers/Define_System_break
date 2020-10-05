<?php

namespace DefineSystem;

class Defines {

	// temporary set values
	private static $pres_system = null;

	// used value in instances
	protected $system = null;
	protected $gets = [];
	protected $posts = [];
	protected $servers = [];

	protected $init_set = false;

	public function __construct() {
		$this->initSettings();
		$this->init_set = true;
	}

	public static function setSystem($system) {
	    if ($system instanceof \DefineSystem) {
	        self::$pres_system = $system;
	    }
	}

	protected function initSettings() {
	    if ($this->init_set === false) {
		    if (! is_null(self::$pres_system)) {
		        $this->system = self::$pres_system;
		        $this->gets = $this->system->gets();
		        $this->posts = $this->system->posts();
		        $this->servers = $this->system->servers();
		    }
		}
	}

	protected function db($key) {
	    return $this->system->db($key);
	}

	protected function msg() {
	    return $this->system->msg();
	}





}
