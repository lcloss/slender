<?php
use LCloss\Route\Route;
use LCloss\Route\Request;

function request() 
{
    return new Request();
}

function resolve( $request = NULL )
{
    if ( is_null($request) ) {
        $request = request();
    }
    return Route::resolve( $request );
}

function route( $name, $params = NULL )
{
    return Route::translate( $name, $params );
}

function redirect( $pattern, $params = NULL )
{
    return header('Location: ' . route($pattern, $params));
    exit();
}

function back()
{
    return header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit();
}

function asset( $resource = "" )
{
    return Route::asset( $resource );
}