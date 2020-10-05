<?php

namespace DefineSystem;




function is_string_all($strings) {
    $is_string = true;
    foreach ((array)$strings as $stg) {
        if (! is_string($stg) && ! is_numeric($stg)) {
            $is_string = false;
            break;
        }
    }
    return $is_string;
}

function is_string_numeric($string) {
    if (is_string($string)) {
        return true;
    }
    if (is_numeric($string)) {
        return true;
    }
    return false;
}

function is_single_word($string) {
    $is_single = true;

    if (! is_string_numeric($string)) {
        $is_single = false;
    } elseif (preg_match('/\s/', $string)) {
        $is_single = false;
    }
    return $is_single;
}

function fill_up_slash($directory) {
    return preg_match('/\/$/su', $directory) ? $directory : $directory.'/';
}

function eraseTraverse($path) {
    if (! is_string_numeric($path)) {
        return $path;
    }

    while (strpos($path, '../') !== false) {
        $path = str_replace('../', '', $path);
    }

    return $path;
}


class PreFunctions {


	public static function fillUpSlash($directory) {
		return preg_match('/\/$/su', $directory) ? $directory : $directory.'/';
	}

	public static function isTypicalString($str, $strict = false) {
		if (!is_string($str)) {
			return false;
		}
		if (strlen($str) === 0) {
			return false;
		}
		if ($strict) {
			if (!preg_match('/^[a-z0-9_-]+$/isu', $str)) {
				return false;
			}
		}

		return true;
	}
}
