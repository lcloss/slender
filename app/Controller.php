<?php
namespace App;

use App\App;
use LCloss\View\View;

class Controller 
{
    protected $app;
    protected $view;

    public function __construct()
    {
        $this->app = App::getInstance();
        $this->view = new View();
        $this->view->fromEnv( SITE_FOLDER, '.env' );
        $this->view->setPath( 'resources.views' );
    }
}