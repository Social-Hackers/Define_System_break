<?php

namespace DefineSystem;

class Spl {

    private static $spl_path = [];

    public static function setSplPath($content, $path) {
        $reg_namespace = "/(?:namespace)(?:\s+)(\w+)((?:\\\\\w+)*)(?:\s+)?(?:;|\{)/i";
        $reg_class = "/(class)(\s+\w+\s+)(extends\s+)?(\w+\s*)?(implements\s+)?(\w+\s*)?(\n*\s*\{)/i";

        if (preg_match_all($reg_namespace, $content, $matches_namespace)) {

            $split_content = preg_split("/(namespace)(\s+)(\w+)((\\\\\w+)*)(\s+)?(;|\{)/i", $content);
            array_shift($split_content);

            foreach ($matches_namespace[1] as $key => $name_space) {
                if (! empty($matches_namespace[2][$key])) { $name_space = $name_space.$matches_namespace[2][$key]; }
                $name_space = trim($name_space);

                if (isset($split_content[$key])) {

                    if (preg_match_all($reg_class, $split_content[$key], $matches)) {
                        foreach ($matches[2] as $class_name) {
                            $class_name = "{$name_space}\\" . trim($class_name);
                            self::$spl_path[$class_name] = $path;
                        }
                    }
                }
            }

        } else {

            if (preg_match_all($reg_class, $content, $matches)) {
                foreach ($matches[2] as $class_name) {
                    $class_name = trim($class_name);
                    self::$spl_path[$class_name] = $path;
                }
            }

        }
    }

    public static function setSpl() {
        spl_autoload_register(function($class_name) {
            if (isset(self::$spl_path[$class_name])) {
                require_once self::$spl_path[$class_name];
            }
        });
    }


    public static function issetSpl($class_name) {
        return isset(self::$spl_path[$class_name]);
    }


}