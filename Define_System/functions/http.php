<?php

namespace DefineSystem;

function turnStatusCode($code) {

    http_response_code($code);
    Status::turnStatusCode($code);

    switch ($code) {}

}






