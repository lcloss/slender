<?php

class Template {
    const RESOURCES_PATH = "resources" . DIRECTORY_SEPARATOR;
    const VIEWS_PATH = self::RESOURCES_PATH . "views" . DIRECTORY_SEPARATOR;

    private $tpl = "";
    private $doc = "";

    public function __construct($template) {
        $doc = Template::getTemplate($template);
        $this->doc = $doc;
    }
    
    public function display($data = []) {
        foreach($data as $key => $value) {
            $search_key = "{{ " . $key . " }}";
            $this->doc = str_replace($search_key, $value, $this->doc);
        }
        echo $this->doc;
    }

    private static function getTemplate($template) {
        $tpl_full_name = Template::VIEWS_PATH . str_replace(".", DIRECTORY_SEPARATOR, $template) . ".tpl.php";
        if (file_exists($tpl_full_name)) {
            return file_get_contents($tpl_full_name);
        } else {
            return "";
        }

    }
}
?>