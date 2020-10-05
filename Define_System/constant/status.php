<?php

namespace DefineSystem;

class Status {


    protected static $status_msg = [];


    public static function statusMsg() {
        return self::$status_msg;
    }

    public static function addStatusMsg($msg) {

        if (! is_string($msg)) {
            self::$status_msg[] = 'cannot add non string message : ' . print_r($msg, true);
            return false;
        }

        self::$msg[] = $msg;
        return true;
    }

    public static function eraseStatusMsg() {
        self::$status_msg = [];
    }

    private static $status_code = 200;

    public static function turnStatusCode($code) {
        self::$status_code = $code;
    }

    public static function statusCode() {
        return self::$status_code;
    }


    private static $loaded_already = false;

    public static function statusLoading() {
        return self::$loaded_already;
    }

    public static function changeIntoLoaded() {
        self::$loaded_already = true;
    }


    private static $released_already = false;

    public static function statusReleasing() {
        return self::$released_already;
    }

    public static function changeIntoReleased() {
        self::$released_already = true;
    }


}