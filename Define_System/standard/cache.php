<?php

namespace DefineSystem;

class Cache {

    private static $cache_directory = null;

    private static $file_cache = false;
	private static $memcache = false;
	private static $msg = [];

	private static $memcache_connection = null;


	public static function setDirectory($directory) {
	    if (! is_null(self::$cache_directory)) {
	        return;
	    }
	    if (! is_string_numeric($directory)) {
	        self::$msg[] = 'Incorrect directory name : '.print_r($directory, true);
	        return;
	    }

	    self::$cache_directory = $directory;
	}

	public static function store($cache_info, $cache_key = 'default', $file_key = 'defines') {
	    if (! ENABLE_CACHE) {
	        self::$msg[] = 'cache is not enable';
	        return;
	    }
	    if (! is_string_numeric($file_key)) {
	        self::$msg[] = 'incorrect file key';
	        return;
	    }

        /*
         * each cache style method is reviewed
         */
	    if (self::$file_cache === true) {
	        self::fileCacheStore($cache_info, $cache_key, $file_key);
	    } elseif (self::$memcache === true) {
	        self::memcacheStore($cache_info, $cache_key);
	    } else {
	        self::$msg[] = 'unknown cache style,please make sure your set cache style is correct : [ file | db]';
	    }
	}

	public static function cargo($cache_key = 'default', $file_key = 'defines') {
	    if (! ENABLE_CACHE) {
	        self::$msg[] = 'cache is not enable';
	        return false;
	    }
	    if (! is_string_numeric($file_key)) {
	        self::$msg[] = 'incorrect file key';
	        return false;
	    }

	    if (self::$file_cache === true) {
	        return self::fileCacheCargo($cache_key, $file_key);
	    } elseif (self::$memcache === true) {
	        return self::memcacheCargo($cache_key);
	    } else {
	        self::$msg[] = 'unknown cache style,please make sure your set cache style is correct : [ file | memcache]';
	        return false;
	    }
	}

	public static function msg() {
	    return self::$msg;
	}

	/**
	 * this method is excecuted by DefineSystem on loading
	 */
	public static function setCacheStyle() {

	    /******************************************************************************
	     * choise of cached style
	     * cache style is default defined by DefineSystem or assigned user definition
	     * cache style shuld be the correct signing string [ file | db]
	     ******************************************************************************/
	    switch (strtolower(CACHE_STYLE)) {
	        case 'file': self::$file_cache = true;break;
	        case 'memcache' : self::$memcache = true;break;
	    }
	}

	public static function memcacheConnect($memcache_servers, $memcache_options = []) {

	    if (! class_exists('Memcached')) {
	        self::$msg[] = 'cannot set up Memcached class';
	        return false;
	    }

	    $memcached = new Memcached();
	    if (! $memcached->addServers($memcache_servers)) {
	        self::$msg[] = 'cannot connect memcache servers';
	        return false;
	    }
	    self::$memcache_connection = $memcached;

	    if (! is_array($memcache_options)) {
	        self::$msg[] = 'memcache options are not valid array parameter';
	    } else {
	        foreach ($memcache_options as $key => $option) {
	            if (is_string_numeric($key) && is_string_numeric($option) && defined('Memcached::'.$key)) {
	                $memcached->setOption(Memcached::$key, $option);
	            }
	        }
	    }


	    return true;
	}


	private static function fileCacheStore($cache_info, $cache_key, $file_key) {

	    $path = self::$cache_directory . DIRECTORY_SEPARATOR . eraseTraverse($file_key);
	    list($current_cache_info, $file_size_overflew) = self::readCacheInfo($path);

	    $write_cache_info = [];
	    if (! empty($current_cache_info)) {

	        if ((isset($current_cache_info['expired']) && $current_cache_info['expired'] < date('Ymd His'))
	            || $file_size_overflew) {
	                $write_cache_info = self::renewd_cache($cache_info, $cache_key);
	            } else {
	                $current_cache_info['cache_info'][$cache_key] = $cache_info;
	                $write_cache_info = $current_cache_info;
	            }
	    } else {
	        $write_cache_info = self::renewd_cache($cache_info, $cache_key);
	    }

	    $fp = @fopen($path, "w");
	    if ($fp !== false) {
	        $result = json_encode($write_cache_info, 128);
	        fwrite($fp, $result);
	        fclose($fp);
	    }
	}

	private static function fileCacheCargo($cache_key, $file_key) {

	    $str = '';
	    @$fp = fopen(self::$cache_directory . DIRECTORY_SEPARATOR . eraseTraverse($file_key), "r");

	    if ($fp !== false) {
	        while (!feof($fp)) {
	            $str .= fgets($fp)."\n";
	        }

	        if ($str !== '') {
	            $result = json_decode($str, true);

	            if ($result !== false && isset($result['cache_info'][$cache_key])) {
	                return $result['cache_info'][$cache_key];
	            }
	        }
	        fclose($fp);
	    }
	    return false;
	}

	private static function memcacheStore($cache_info, $cache_key) {

	    if (! class_exists('Memcached')) {
	        self::$msg[] = 'cannot set up Memcached class';
	        return;
	    }
	    if (empty(self::$memcache_connection)) {
	        self::$msg[] = 'memcache connection is not stable,please use memcacheConnect() to connect memcache server';
	        return;
	    }

	    self::$memcache_connection->set($cache_key, $cache_info);
	}

	private static function memcacheCargo($cache_key) {

	    if (! class_exists('Memcached')) {
	        self::$msg[] = 'cannot set up Memcached class';
	        return false;
	    }
	    if (empty(self::$memcache_connection)) {
	        self::$msg[] = 'memcache connection is not stable,please use memcacheConnect() to connect memcache server';
	        return false;
	    }

	    return self::$memcache_connection->get($cache_key);
	}

	private static function readCacheInfo($path) {

	    $str = '';
	    $cache_info = [];
	    $file_size_overflew = false;
	    $fp = @fopen($path, "r");

	    if ($fp !== false) {
	        if (($file_size = filesize($path)) !== false) {
	            if (! empty(MAX_CACHE_FILE_SIZE) && $file_size > (int)MAX_CACHE_FILE_SIZE) {
	                $file_size_overflew = true;
	            }
	        }

	        while (!feof($fp)) {
	            $str .= fgets($fp)."\n";
	        }

	        if ($str !== '') {
	            $result = @json_decode($str, true);

	            if ($result !== false) {
	                $cache_info =  $result;
	            } else {
	                self::$msg[] = 'failed to encode json,cache file is posible to be broken : ' . $path;
	            }
	        }
	        fclose($fp);
	    }
	    return [$cache_info, $file_size_overflew];
	}

	private static function renewd_cache($cache_info, $cache_key) {
	    $cache_expired_date = empty(CACHE_EXPIRED) ? '99991201 595959' : date('Ymd His', time() + (int)CACHE_EXPIRED);
	    $write_cache_info = [
	        'expired' => $cache_expired_date,
	        'cache_info' => [
	            $cache_key => $cache_info
	        ]
	    ];
	    return $write_cache_info;
	}
}

