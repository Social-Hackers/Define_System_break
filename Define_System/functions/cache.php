<?php

namespace DefineSystem;


function cacheSettings($cache_directory, $configs = []) {

    Cache::setDirectory($cache_directory);
    Cache::setCacheStyle();

    if (! empty($configs[MEMCACHE_CONFIG_FILE])) {
        $memcache_servers = $configs[MEMCACHE_CONFIG_FILE];
        $memcache_options = ! empty($configs[MEMCACHE_OPTIONS_CONFIG_FILE]) ? $configs[MEMCACHE_OPTIONS_CONFIG_FILE] : [];
        Cache::memcacheConnect($memcache_servers, $memcache_options);
    }
}


