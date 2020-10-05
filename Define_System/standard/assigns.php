<?php

namespace DefineSystem;

class Assigns {


    private static $assigns_value = [];

    private static $msg = [];

    public static function set($index, $value) {
        if (! is_single_word($index)) {
            self::$msg[] = 'cannot set assigns index : ' . print_r($index);
            return false;
        }
        self::$assigns_value[$index] = $value;
        return true;
    }

    public static function value($index = null) {
        if (is_single_word($index)) {
            self::$msg[] = 'cannot set assigns value index : ' . print_r($index);
            return false;
        }
        if (is_null($index)) {
            return self::$assigns_value;
        }
        return isset(self::$assigns_value[$index]) ? self::$assigns_value[$index] : '';
    }


    public static function erase() {
        self::$assigns_value = [];
    }


}