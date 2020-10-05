<?php

class Exception503 extends Exception {

    const STATUS_CODE = 503;


    public function __construct($message) {

        DefineSystem\turnStatusCode(self::STATUS_CODE);
        parent::__construct($message, self::STATUS_CODE);
    }



}