<?php
namespace App\Controller;

class AppController extends Controller
{
    public function index() {
        parent::__construct();
        echo $this->app->view('index');
    }
}