<?php ?>
<?php
include 'config/bootstrap.php';

ob_start();
phpinfo();
$content = ob_get_clean();

$template = new Template();

// $tpl_model = <<<TPL
// {% use index %}
// {{ info }}
// TPL;
// $template->setData($tpl_model);
// $data = array(
//     'info'  => $content
// );
// $template->display($data);

$template->setDatA($content);
$template->display();