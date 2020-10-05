<?php

namespace DefineSystem;

class Session {

	private static $sessions = [];
	private static $session_id = null;
	private static $token = null;
	private static $tokenExpiringTime = null;
	private static $tokenUse = 0;
	private static $activated_already = false;
	private static $msg = array();

	private static $session = false;
	private static $db = false;

	private static $db_session_connection = null;

	public static function activate() {
	    if (self::$activated_already === true) {
			return new self();
		}
		if (session_status() !== 2) {
			session_start();
		}
		if (self::$session === true) {
		    if (isset($_SESSION)) {
		        foreach ($_SESSION as $name => $session) {
		            switch ($name) {
		                case 'token' : self::$token = $session; break;
		                case 'tokenExpiringTime' : self::$tokenExpiringTime = $session; break;
		                default : self::$sessions[$name] = $session;
		            }
		        }
		    }
		    if (SERVER_PARAMS_DESTRUCT) {
		        $_SESSION = [];
		    }
		}

		self::$session_id = session_id();
		self::$activated_already = true;
		return new self();
	}

	public static function close() {
	    if (self::$activated_already === false) {
			self::$msg[] = 'session is not activated';
			return;
		}
		if (self::$session === true) {
		    if (SERVER_PARAMS_DESTRUCT) {
		        $_SESSION = self::$sessions;
		    }
		    if (!is_null(self::$token) && !is_null(self::$tokenExpiringTime)) {
		        if (self::$tokenExpiringTime >= time()) {
		            $name = 'token';
		            $_SESSION[$name] = self::$token;
		            $name = 'tokenExpiringTime';
		            $_SESSION[$name] = self::$tokenExpiringTime;
		        }
		    }
		}

		self::$tokenUse = 0;
		self::$activated_already = false;
		session_write_close();
	}

	public static function reset() {
	    if (self::$activated_already === false) {
			self::$msg[] = 'session is not activated';
			return;
		}
		session_regenerate_id();
		self::$session_id = session_id();
	}

	public static function store($name, $value) {
	    if (self::$activated_already === false) {
	        self::$msg[] = 'session is not activated';
	        return;
	    }
	    if (self::$session === true) {
	        self::$sessions[$name] = $value;
	        if (! SERVER_PARAMS_DESTRUCT) {
	            $_SESSION[$name] = $value;
	        }
	    } elseif (self::$db === true) {
	        insertDbSession(self::$db_session_connection, self::$session_id, $name, $value);
	    } else {
	        self::$msg[] = 'unknown session style,please make sure your set session style is correct : [ session | db]';
	    }

	}

	public static function erase($name) {
	    if (self::$activated_already === false) {
			self::$msg[] = 'session is not activated';
			return;
		}
		if (! SERVER_PARAMS_DESTRUCT) {
		    unset($_SESSION[$name]);
		}

		if (self::$session === true) {
		    unset(self::$sessions[$name]);
		} elseif (self::$db === true) {
		    deleteDbSession(self::$db_session_connection, self::$session_id, $name);
		}
	}

	public static function cargo($name) {
	    if (self::$activated_already === false) {
			self::$msg[] = 'session is not activated';
			return false;
		}
		if (self::$session === true) {
		    return isset(self::$sessions[$name]) ? self::$sessions[$name] : false;
		} elseif (self::$db === true) {
		    return getDbSession(self::$db_session_connection, self::$session_id, $name);
		} else {
		    self::$msg[] = 'unknown session style,please make sure your set session style is correct : [ session | db]';
		}

	}

	public static function generateToken($sault = '') {
	    if (self::$activated_already === false) {
			self::$msg[] = 'session is not activated';
			return false;
		}
		if (!is_string($sault)) {
			self::$msg[] = 'ソルトが正常ではありません';
			return false;
		}

		$decoded = TOKEN_GENERATE_BASE;
		if ($decoded === false) {
			$decoded = '';
		}
		$num01 = rand(0, 2);
		$num02 = rand(3,10);
		$token = sha1($decoded.substr(self::$session_id, $num01, $num02).$sault);
		$tokenExpiringTime = time() + TOKEN_EXPIRE;

		self::$token = $token;
		self::$tokenExpiringTime = $tokenExpiringTime;
		if (! SERVER_PARAMS_DESTRUCT) {
			$_SESSION['token'] = $token;
			$_SESSION['tokenExpiringTime'] = $tokenExpiringTime;
		}
		return $token;
	}

	public static function tokenIntegrality($token) {
		if (is_null(self::$token) || is_null(self::$tokenExpiringTime)) {
			return false;
		}
		if (self::$tokenExpiringTime < time()) {
			return false;
		}
		if (self::$token == $token) {
			self::$tokenUse ++;
			$max_token_use = MAX_TOKEN_USE > 1 ? MAX_TOKEN_USE : 1;
			if (self::$tokenUse >= $max_token_use) {
				self::$token = null;
				self::$tokenExpiringTime = null;
				self::$tokenUse = 0;
			}

			return true;
		}

		return false;
	}

	public static function setSessionStyle() {

	    switch (strtolower(SESSION_STYLE)) {
	        case 'session' : self::$session = true; break;
	        case 'db' : self::$db = true; break;
	    }
	}

	public static function setDbSessionConnection($connection) {
	    if ($connection instanceof Db) {
	        self::$db_session_connection = $connection;
	    }
	}

	public static function msg() {
		return self::$msg;
	}

	public static function eraseMsg() {
		self::$msg = [];
	}
}
