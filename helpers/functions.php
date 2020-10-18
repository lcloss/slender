<?php

if ( !function_exists('startsWith') ) {
    function startsWith( $test, $subject ) {
        if ( strlen($test) > strlen($subject) ) {
            return false;
        } else {
            if ( substr( $subject, 0, strlen($test) ) == $test) {
                return true;
            } else {
                return false;
            }
        }
    }
}

if ( !function_exists('endsWith') ) {
    function endsWith( $test, $subject ) {
        if ( strlen($test) > strlen($subject) ) {
            return false;
        } else {
            $len = strlen($test);
            $start = strlen($subject) - $len;
            if ( substr( $subject, $start, $len ) == $test) {
                return true;
            } else {
                return false;
            }
        }
    }
}