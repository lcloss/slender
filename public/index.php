<?php
use App\App;

require_once('..' . DIRECTORY_SEPARATOR . 'bootstrap' . DIRECTORY_SEPARATOR . 'app.php');

$app = App::getInstance();
$app->run();