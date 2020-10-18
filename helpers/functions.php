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

if ( !function_exists('randomStr')) {
    function randomStr($length = 8, $params = ['lower', 'capital', 'number', 'symbol', 'simsym']) 
    {
        $low_letters = "abcdefghijklmnopqrstuvwxyz";
        $cap_letters = strtoupper($low_letters);
        $symbols = "!?#$%&-_";
        $simple_symbols = "-_";
        $numbers = "1234567890";
    
        $source = '';
        if ( in_array('lower', $params) ) {
            $source .= $low_letters;
        }
        if ( in_array('capital', $params) ) {
            $source .= $cap_letters;
        }
        if ( in_array('number', $params) ) {
            $source .= $numbers;
        }
    
        if ( in_array('symbol', $params) ) {
            $source .= $symbols;
    
        } elseif ( in_array('simsymb', $params) ) {
            $source .= $simple_symbols;
        }
    
        $max = strlen($source);
        $i = 0;
        $word = "";
    
        while ($i < $length) {
            $num = rand() % $max;
            $char = substr($source, $num, 1);
            $word .= $char;
            $i++;
        }
    
        return $word;
    }    
}
