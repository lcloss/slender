<?php ?>
<?php
include 'config/bootstrap.php';

$template = new Template('index');
$data = array(
    'title' => 'Home',
    'content' => ''
);
$template->display($data);

?>
<?php ?>