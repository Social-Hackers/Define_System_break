<?php

namespace DefineSystem;



function loadDefault($defines_directory, $pre_defines) {
    return loadFile($defines_directory . DIRECTORY_SEPARATOR . 'constant' . DIRECTORY_SEPARATOR . 'defaults.php', $pre_defines);
}


function loadDefines($defines_directory) {

    $return = true;

    foreach (json_decode(DEFAULT_DEFINES, true) as $sector => $each) {
        if (is_array($each)) {
            $sub_directory = DIRECTORY_SEPARATOR . $sector;
            $files = $each;
        } else {
            $sub_directory = '';
            $files = [$each];
        }
        foreach ($files as $file) {
            if (! loadFile($defines_directory . $sub_directory . DIRECTORY_SEPARATOR . $file)) {
                $return = false;
            }
        }

    }
    return $return;
}


function loadFunctions($functions_directory, $functions = []) {

    if (! is_dir($functions_directory)) {
        return;
    }

    if (empty($functions)) {

        // autoload
        autoloadDirectoryHandle($functions_directory, APPLICATION_FILE_EXTENSION);
    } else {

        // costom load
        loadDirectoryFile($functions_directory, $functions, APPLICATION_FILE_EXTENSION);
    }
}


function loadConfigs($configs_directory, $configs = []) {

    if (! is_dir($configs_directory)) {
        return [];
    }

    if (empty($configs)) {
        $configs = autoloadConfigDirectory($configs_directory, CONFIG_FILE_EXTENSION);
    } else {
        $configs = loadDirectoryConfigFile($configs_directory, $configs, CONFIG_FILE_EXTENSION);
    }
    return $configs;
}


function loadPlugins($plugins_directory) {

    $loader = $plugins_directory . '/Vendor/autoload.php';
    if (is_file($loader)) {
        require_once $loader;
    }
}


function loadFile($path, $arg = null) {

    if (is_file($path)) {
        require_once $path;
        return true;
    }

    return false;
}


function loadConfigFile($path) {

    if (is_file($path)) {
        return require $path;
    }
    return null;
}


function loadIniFile($path, $dept = true) {

    if (is_file($path)) {
        return parse_ini_file($path, $dept);
    }
    return null;
}

function autoloadDirectoryHandle($directory, $extension) {

    $directory_path = $directory. DIRECTORY_SEPARATOR;
    $handle = opendir($directory_path. DIRECTORY_SEPARATOR);

    if ($handle === false) {
        return;
    }

    while ($file = readdir($handle)) {

        if (in_array($file, EXCLUSIVE_AUTOLOAD_DIRECTORY)) {
            continue;
        }

        if (is_dir($directory_path.$file)) {
            autoloadDirectoryHandle($directory_path.$file, $extension);
        } elseif (preg_match('/\.'.$extension.'$/', $file)) {
            loadFile($directory_path.$file);
        }

    }
}

function autoloadConfigDirectory($directory, $extension) {

    $configs = [];
    $directory_path = $directory. DIRECTORY_SEPARATOR;
    $handle = opendir($directory_path. DIRECTORY_SEPARATOR);

    if ($handle === false) {
        return;
    }

    while ($file = readdir($handle)) {

        if (in_array($file, json_decode(EXCLUSIVE_AUTOLOAD_DIRECTORY, true))) {
            continue;
        }

        if (preg_match('/\.'.$extension.'$/', $file)) {
            switch ($extension) {
                case 'ini':
                    $configs[basename($file, '.'.$extension)] = loadIniFile($directory_path.$file);
                    break;
                default:
                    $configs[basename($file, '.'.$extension)] = loadConfigFile($directory_path.$file);
                    break;
            }
        }

    }
    return $configs;
}

function loadDirectoryFile($directory, $files, $extension) {

    foreach ((array)$files as $key => $file) {

        if (is_array($file)) {
            loadDirectoryFile($directory . DIRECTORY_SEPARATOR . $key, $file, $extension);
        } elseif (is_string_numeric($file)) {
            loadFile($directory . DIRECTORY_SEPARATOR . $file . '.' . $extension);
        }
    }
}

function loadDirectoryConfigFile($directory, $files, $extension) {

    $configs = [];
    $directory_path = $directory. DIRECTORY_SEPARATOR;

    foreach ((array)$files as $file) {

        if (is_string_numeric($file)) {
            switch ($extension) {
                case 'ini':
                    $configs[$file] = loadIniFile($directory_path.$file.'.'.$extension);
                    break;
                default:
                    $configs[$file] = loadConfigFile($directory_path.$file.'.'.$extension);
                    break;
            }
        }
    }
    return $configs;
}

function splSettings($directory, $extension = APPLICATION_FILE_EXTENSION, $sub_loading = false) {

    if (! is_dir($directory)) {
        return;
    }

    $directory_path = $directory. DIRECTORY_SEPARATOR;
    $handle = opendir($directory_path. DIRECTORY_SEPARATOR);

    if ($handle === false) {
        return;
    }

    while ($file = readdir($handle)) {

        if (in_array($file, json_decode(EXCLUSIVE_AUTOLOAD_DIRECTORY, true))) {
            continue;
        }

        if (is_dir($directory_path.$file)) {
            splSettings($directory_path.$file, $extension, true);
        } elseif (preg_match('/\.'.$extension.'$/', $file)) {
            $content = file_get_contents($directory_path.$file);
            Spl::setSplPath($content, $directory_path.$file);
        }

    }

    if ($sub_loading === false) {
        Spl::setSpl();
    }
}

function actionBaseSetting($system) {
    Defines::setSystem($system);
}

function loadIncludeFile($directory, $path) {

    $include_path = $directory . DIRECTORY_SEPARATOR . $path;
    $include_path = str_replace('..', '', $include_path);
    $include_path = str_replace(DIRECTORY_SEPARATOR.DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $include_path);

    $file = $include_path . '.' . DEFAULT_FILE_EXTENSION;
    if (is_file($file)) {
        include $file;
    }
}
