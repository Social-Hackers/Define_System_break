<?php

class Exception404 extends Exception {


    const STATUS_CODE = 404;


    public function __construct($message) {

        DefineSystem\turnStatusCode(self::STATUS_CODE);
        parent::__construct($message, self::STATUS_CODE);
    }


}