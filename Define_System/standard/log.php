<?php

namespace DefineSystem;

class Log {

    private static $log_directory = null;
    private static $log_file_extension = LOG_FILE_EXTENSION;

    private static $default_file = null;
	private static $files = [];
	private static $msg = [];

	const LOG_SUBJECT = '[%s] %s : %s';
	const NON_TITLED_LOG_SUBJECT = 'System Log %s : %s';

	public static function setDirectory($directory) {
	    if (! is_null(self::$log_directory)) {
	        return;
	    }
	    if (! is_string_numeric($directory)) {
	        self::$msg[] = 'Incorrect directory name : '.print_r($directory, true);
	        return;
	    }

	    self::$log_directory = $directory;
	}

	public static function setFile($name, $file) {
	    if (! is_string_numeric($name)) {
	        self::$msg[] = 'Incorrect file key name : '.print_r($name, true);
	        return;
	    }
	    if (! is_string_numeric($file)) {
	        self::$msg[] = 'Incorrect file name : '.print_r($name, true);
	        return;
	    }
	    if (isset(self::$files[$name])) {
	        self::$msg[] = 'Key file name '.$name.' is already exists.file path will be over written';
	    }

		self::$files[$name] = $file . '.' . self::$log_file_extension;
	}

	public static function setDefaultFile($file) {
	    if (! is_null(self::$default_file)) {
	        return;
	    }
	    if (! is_string_numeric($file)) {
	        self::$msg[] = 'Incorrect file name : '.print_r($file, true);
	        return;
	    }

	    self::$default_file = $file . '.' . self::$log_file_extension;
	}

	public static function logging($log_info, $name = null, $type = null) {
	    self::writeLog($log_info, $type, $name);
	}

	public static function info($log_info, $name = null) {
	    self::writeLog($log_info, 'info', $name);
	}

	public static function error($log_info, $name = null) {
	    self::writeLog($log_info, 'error', $name);
	}

	public static function msg() {
	    return self::$msg;
	}

	private static function logPathName($name) {
	    $path = null;
	    if (is_null($name)) {
	        if (is_null(self::$default_file)) {
	            self::$msg[] = 'default file is not set : '.print_r($name, true);
	        } else {
	            $path = self::$default_file;
	        }
	    } else {
	        if (isset(self::$files[$name])) {
	            $path = self::$files[$name];
	        } else {
	            self::$msg[] = 'file key is not set : '.print_r($name, true);
	        }
	    }
	    return $path;
	}


	private static function writeLog($log_info, $type, $name) {

	    $path = self::logPathName($name);
	    if (is_null($path)) {
	        return;
	    }

	    $fp = @fopen(self::$log_directory . DIRECTORY_SEPARATOR . $path, "a");
	    if ($fp !== false) {
	        $log = is_null($type) ? sprintf(self::NON_TITLED_LOG_SUBJECT, date('Y-m-d H:i:s'), print_r($log_info, true)) : sprintf(self::LOG_SUBJECT, $type, date('Y-m-d H:i:s'), print_r($log_info, true));
	        $log = $log.PHP_EOL;
	        fwrite($fp, $log);
	        fclose($fp);
	    }
	}

}

