<?php

namespace DefineSystem;


function defaults($defines, $boolean_values, $number_values, $pre_defines) {
    foreach ($defines as $define) {
        $name = $define['name'];
        $judge_method = $define['judge_method'];
        define(__NAMESPACE__ . '\\' . $name, ! empty($pre_defines[$name]) && $judge_method($pre_defines[$name]) ? $pre_defines[$name] : $define['default']);
    }
    foreach ($boolean_values as $boolean_value) {
        $name = $boolean_value['name'];
        $value = null;
        if (isset($pre_defines[$name])) {
            $value = empty($pre_defines[$name]) ? false : true;
        }
        define(__NAMESPACE__ . '\\'. $name, is_bool($value) ? $value : $boolean_value['default']);
    }
    foreach ($number_values as $number_value) {
        $name = $number_value['name'];
        define(__NAMESPACE__ . '\\'. $name, ! empty($pre_defines[$name]) && is_numeric($pre_defines[$name]) ? (int)$pre_defines[$name] : $number_value['default']);
    }


    define(__NAMESPACE__ . '\SQL_SANITIZING_LIST', json_encode(["'" => '"', '&' => '']));

    define(__NAMESPACE__ . '\DEFAULT_DEFINES', json_encode([
        'constant' => [
            'defaults.php',
            'status.php'
        ],
        'core' => [
            'route.php',
            'visual.php'
        ],
        'functions' => [
            'cache.php',
            'http.php',
            'load.php',
            'log.php',
            'prefunction.php',
            'session.php'
        ],
        'standard' => [
            'assigns.php',
            'cache.php',
            'defines.php',
            'log.php',
            'session.php',
            'spl.php'
        ],
        'db' => [
            'connection.php',
            'sql.php'
        ],
        'exception' => [
            'defines.php',
            'exception404.php',
            'exception503.php'
        ],
        'empty_object.php'
    ]));

    define(__NAMESPACE__ . '\EXCLUSIVE_AUTOLOAD_DIRECTORY', json_encode([
        '.', '..'
    ]));

}


$defines = [
    ['name' => 'DEFINE_SYSTEM_VAR', 'judge_method' => 'is_string', 'default' => 'system'],
    ['name' => 'URI_FILE_EXTENSION', 'judge_method' => 'is_string', 'default' => 'html'],
    ['name' => 'DEFAULT_FILE_EXTENSION', 'judge_method' => 'is_string', 'default' => 'html'],
    ['name' => 'APPLICATION_FILE_EXTENSION', 'judge_method' => 'is_string', 'default' => 'php'],
    ['name' => 'CONFIG_FILE_EXTENSION', 'judge_method' => 'is_string', 'default' => 'php'],
    ['name' => 'LOG_FILE_EXTENSION', 'judge_method' => 'is_string', 'default' => 'log'],
    ['name' => 'INDEX_FILE', 'judge_method' => 'is_string', 'default' => 'index'],
    ['name' => 'LOG_CONFIG_FILE', 'judge_method' => 'is_string', 'default' => 'log'],
    ['name' => 'MEMCACHE_CONFIG_FILE', 'judge_method' => 'is_string', 'default' => 'memcache'],
    ['name' => 'MEMCACHE_OPTIONS_CONFIG_FILE', 'judge_method' => 'is_string', 'default' => 'memoptions'],
    ['name' => 'DB_SESSION_CONFIG_KEY', 'judge_method' => 'is_string', 'default' => 'session'],
    ['name' => 'DB_SESSION_TABLE_NAME', 'judge_method' => 'is_string', 'default' => 'defines_db_session'],
    ['name' => 'CACHE_STYLE', 'judge_method' => 'is_string', 'default' => 'file'],
    ['name' => 'SESSION_STYLE', 'judge_method' => 'is_string', 'default' => 'session'],
    ['name' => 'ROUTE_CONFIG_FILE', 'judge_method' => 'is_string', 'default' => 'route'],
    ['name' => 'DB_CONFIG_FILE', 'judge_method' => 'is_string', 'default' => 'db'],
    ['name' => 'DB_DRIVER_KEY', 'judge_method' => 'is_string', 'default' => 'driver'],
    ['name' => 'DB_USER_KEY', 'judge_method' => 'is_string', 'default' => 'user'],
    ['name' => 'DB_PASS_KEY', 'judge_method' => 'is_string', 'default' => 'pass'],
    ['name' => 'DB_HOST_KEY', 'judge_method' => 'is_string', 'default' => 'host'],
    ['name' => 'DB_PORT_KEY', 'judge_method' => 'is_string', 'default' => 'port'],
    ['name' => 'DB_NAME_KEY', 'judge_method' => 'is_string', 'default' => 'db'],
    ['name' => 'ROUTING_TYPE', 'judge_method' => 'is_string', 'default' => 'path'],
    ['name' => 'GUIDANCE_URI', 'judge_method' => 'is_string', 'default' => 'guidance'],
    ['name' => 'NOTIFICATION_URI', 'judge_method' => 'is_string', 'default' => 'notification'],
    ['name' => 'NOT_FOUND_MESSAGE', 'judge_method' => 'is_string', 'default' => '<pre><span style="font-weight: 700; font-size: 22px; font-size: 2.2rem; margin-bottom: 14px">Not Found</span><br>The requested URL was not found on this server</pre><hr><pre>Apangel/7.7.7 Server at Port 70</pre>'],
    ['name' => 'SERVICE_UNAVAILABLE_MESSAGE', 'judge_method' => 'is_string', 'default' => '<pre><span style="font-weight: 700; font-size: 22px; font-size: 2.2rem; margin-bottom: 14px">Service Unavailable</span><br>The service is anavailable at this time</pre><hr><pre>Apangel/7.7.7 Server at Port 70</pre>'],
    ['name' => 'TOKEN_GENERATE_BASE', 'judge_method' => 'is_string', 'default' => json_encode([
        'defines',
        'token',
        'base'
    ])],
];
$boolean_values = [
    ['name' => 'ENABLE_CACHE', 'default' => true],
    ['name' => 'SESSIOIN_START', 'default' => true],
    ['name' => 'SQL_BIND_VALUES', 'default' => true],
    ['name' => 'SQL_COMMAND_EXEC', 'default' => false],
    ['name' => 'SQL_RETURN_EMPTY_ARRAY', 'default' => false],
    ['name' => 'SQL_DELETE_ALL_NOTIFY', 'default' => true],
    ['name' => 'SQL_UPDATE_ALL_NOTIFY', 'default' => true],
    ['name' => 'SERVER_PARAMS_DESTRUCT', 'default' => true],
];
$number_values = [
    ['name' => 'CACHE_EXPIRED', 'default' => 0],
    ['name' => 'SESSION_EXPIRED', 'default' => 3 * 60 * 60],
    ['name' => 'MAX_CACHE_FILE_SIZE', 'default' => 512 * 1024],
    ['name' => 'TOKEN_EXPIRE', 'default' => 512 * 1024],
    ['name' => 'MAX_TOKEN_USE', 'default' => 512 * 1024],
    ['name' => 'LEFT_JOIN', 'default' => 0],
    ['name' => 'RIGHT_JOIN', 'default' => 1],
    ['name' => 'INNER_JOIN', 'default' => 2],
    ['name' => 'FULL_OUTER_JOIN', 'default' => 3],
];
defaults($defines, $boolean_values, $number_values, $arg);


unset($defines);


