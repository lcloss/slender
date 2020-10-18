<?php
namespace App\Controller;

class AppController extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }
    
    public function index() 
    {
        echo $this->app->view('index');
    }
}