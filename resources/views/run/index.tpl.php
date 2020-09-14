<?php
if (!defined('INSIDE')) {
    die("Silence is golden.");
} else {
    if (INSIDE != true) {
        die("Silence is golden.");
    }
}
?>
{% use index %}
<div class="row">
    <div class="col-5">
        <h2 class="item-title">Código PHP</h2>
        <form>
            <div class="form-group">
                <textarea class="form-control php-code" name="entrada" id="entrada" cols="110" rows="30">{{ entrada }}</textarea>
            </div>
            <button type="submit" class="btn btn-primary">Enviar</button>
        </form>
    </div>
    <div class="col-7">
        <h2 class="item-title">Resultados</h2>
    </div>
</div>