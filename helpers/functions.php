<?php
/**
 * Mostra uma mensagem com quebra de linha.
 */
if (!function_exists('printMsg')) {
    function printMsg($msg) {
        echo "<p style='font-family: Courier;color: red;'>$msg</p>";
    }
}
/**
 * Read a template file from /resources/views folder.
 * Convert all points to DIRECTORY_SEPARATOR character.
 * Complete file name with ".tpl.php" extension.
 */
if (!function_exists('getTemplate')) {
    function getTemplate($tpl_name) {
        $tpl_full_name = "resources" . DIRECTORY_SEPARATOR . "views" . DIRECTORY_SEPARATOR . str_replace(".", DIRECTORY_SEPARATOR, $tpl_name) . ".tpl.php";
        // printMsg($tpl_full_name);
        if (file_exists($tpl_full_name)) {
            return file_get_contents($tpl_full_name);
        } else {
            return "";
        }
    }
}

/** 
 * Check if field $form_field exists in $_POST array.
 * If exists, return its value. Else, return $default_value (or space if not passed as argument)
 */
if (!function_exists('setFormFieldValue')) {
    function setFormFieldValue($form_field, $default_value = "") {
        if (isset($_POST[$form_field])) {
            return $_POST[$form_field];
        } else {
            return $default_value;
        }
    }
}