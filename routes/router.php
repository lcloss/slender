<?php
use LCloss\Route\Route;

Route::get(['set' => '/', 'as' => 'home'], 'AppController@index');