<?php ?>
<?php
$content = "";

include 'config/bootstrap.php';
// printMsg("Incluído Bootstrap");

$nl = "<br />\n";
$entrada = setFormFieldValue('entrada');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $message = "Enviado por POST:<br />";
    $execution = eval($_POST['entrada']);
} else {
    $message = "";
    $execution = "";
}


$content .= <<<SOC
<form action="index.php" method="post">
<div>
    <textarea name="entrada" id="entrada" cols="180" rows="30">$entrada</textarea>
</div>
<div>
    $message
    $execution
</div>
<div>
    <button type="submit">Enviar</button>
</div>
</form>
SOC;
// printMsg("Criado o conteúdo");

$template = new Template('run.index');

$data = array(
    'title' => 'Run',
    'content' => $content
);
// printMsg("Criado o Template");

$template->display($data);
// printMsg("Display do Template");

?>
    <?php
    ?>

    <!-- Bootstrap scripts -->
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js" integrity="sha384-9/reFTGAW83EW2RDu2S0VKaIzap3H66lZH81PoYlFhbGU+6BZp6G7niu735Sk7lN" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js" integrity="sha384-B4gt1jrGC7Jh4AgTPSdUtOBvfO8shuf57BaghqFfPlYxofvL8/KUEfYiJOMMV+rV" crossorigin="anonymous"></script>

</body>
</html>
<?php