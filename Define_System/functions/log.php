<?php

namespace DefineSystem;


function logSettings($log_directory, $log_config) {

    Log::setDirectory($log_directory);
    if (! empty($log_config)) {
        $default_key = '.';
        $default_log_file = isset($log_config[$default_key]) ? $log_config[$default_key] : 'defines';
        unset($log_config[$default_key]);
        logFileSettings($log_directory, $log_config);
    } else {
        $default_log_file = 'defines';
    }
    Log::setDefaultFile($default_log_file);
}

function logFileSettings($log_directory, $log_config, $sub_directory = null) {

    foreach ((array)$log_config as $key => $file) {
        if (is_array($file)) {
            $sub_directory_path = is_null($sub_directory) ? $key : $sub_directory . DIRECTORY_SEPARATOR . $key;
            if (! is_dir($log_directory . DIRECTORY_SEPARATOR . $sub_directory_path)) {
                @mkdir($log_directory . DIRECTORY_SEPARATOR . $sub_directory_path, 0755, true);
            }
            logFileSettings($log_directory, $file, $sub_directory_path);
        } elseif (is_string_numeric($file)) {
            $file_path = is_null($sub_directory) ? $file : $sub_directory . DIRECTORY_SEPARATOR . $file;
            $log_key_name = $key;
            Log::setFile($log_key_name, $file_path);
        }
    }
}
