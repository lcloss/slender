<?php

class Template {
    const RESOURCES_PATH = "resources" . DIRECTORY_SEPARATOR;
    const VIEWS_PATH = self::RESOURCES_PATH . "views" . DIRECTORY_SEPARATOR;

    private $tpl_name = "";
    private $tpl = "";
    private $doc = "";
    private $keys = [];
    private $fors = [];
    private $ifs = [];

    public function __construct( $template = '' ) {
        $this->tpl_name = $template;
        if ( $template != '' ) {
            $this->_setData(Template::_getTemplate($template));
        }
    }
    
    public function display($data = []) {
        $this->_setKeys($data);
        $this->_run();
    }

    private function _setData($data) {
        $this->doc = $data;
    }
    
    private function _run($return_content = false) {
        $this->_parse();
        if ( $return_content == true ) {
            return $this->doc;
        } else {
            echo $this->doc;
        }
    }

    private function _parse() {
        debugPrint("Parsing " . $this->tpl_name);
        debugPrint($this->doc);
        // First extract all fors
        $this->_extractFors();
        // ...than replace fors keys
        $this->_replaceFors();

        $this->_extractIfs();
        // $this->_replaceIfs();

        $this->_replaceKeys();

        // ...and at last: replace blocks
        $this->_extractBlocks();
        $this->_extractUse();
    }

    private function _setKey($key, $value) {
        $this->keys[$key] = $value;
    }

    private function _setKeys($keys) {
        foreach($keys as $key => $value) {
            $this->_setKey($key, $value);
        }
    }

    private function _setFor($key, $value) {
        $this->fors[$key] = $value;
    }

    private function _setIf($key, $value) {
        $this->ifs[$key] = $value;
    }

    private function _getKey($key) {
        return $this->keys[$key];
    }

    private function _getFor($key) {
        if ( array_key_exists($key, $this->fors) ) {
            return $this->fors[$key];
        } else {
            return '';
        }
    }

    /**
     * Use
     */
    private function _extractUse() {
        debugPrint("extractUse");
        // Check for 'use' a template
        $use_pattern = '/{% use (.*) %}/i';
        preg_match($use_pattern, $this->doc, $matches);

        if ( count($matches) > 0 ) {
            $parent_template = $matches[1];
            // Clear tag
            $this->doc = preg_replace($use_pattern, NULL, $this->doc);

            $template = new Template($parent_template);
            $template->_setKeys($this->keys);
            $template->_setKey('content', $this->doc);

            // Update the doc with parsed Template
            $this->doc = $template->_run(true);
        }
    }


    /**
     * For
     */
    private function _extractFors() {
        debugPrint("extractFors");
        // Extract all for as entries
        $for_pattern = '/{% for ([\w\.]+) %}(.*?){% endfor %}/is';
        preg_match_all($for_pattern, $this->doc, $matches);

        // Run over all occurrences
        foreach($matches[0] as $k => $match) {
            // Set the content of this occurrence
            $this->_setFor($matches[1][$k], $matches[2][$k]);

            // Replace block content by the key
            $this->doc = str_replace($match, '{{ ' . $matches[1][$k] . ' }}', $this->doc);
        }
    }

    private function _replaceFors() {
        debugPrint("replaceFor");
        foreach ($this->fors as $entry => $data) {
            $content = $this->_getKey($entry);

            if ( is_array($content) ) {
                $block = '';
                foreach($content as $data) {
                    $for_template = new Template();
                    $for_template->_setKeys($this->keys);

                    if ( is_array($data) ) {
                        $for_template->_setData($this->_getFor($entry));
                        foreach($data as $key => $value) {
                            $for_template->_setKey($entry . '.' . $key, $value);
                        }
                    }
                    $for_template->_replaceKeys();
                    $block .= $for_template->_run(true);
                }
                $this->_setKey($entry, $block);
            }
        }
    }

    /**
     * Ifs
     */
    private function _extractIfs() {
        $this->_replaceKeys();
        debugPrint("extractIfs");
        // $if_pattern = '/{% if ([\w\.]+) %}(.*?){% else %}(.*?)){% endif %}/is';
        // $if_pattern = '/{% if ([\w\.]*) %}(.*?)(?:{% else %}(.*?))?{% endif %}/is';
        // See: https://stackoverflow.com/questions/26074070/matching-if-elseif-else-statement-with-regular-expression
        // {\?if ([^{}]+)}(.*?)({\?elseif [^{}]+}.*?)*(?:{\?else}(.+?))?{\?endif}
        //
        // {\?if // match "{?if " literally
        //     ([^{}]+)// capture the condition in group 0
        //     }// match "}"
        //     (.*?)// capture the content of the {if} branch in group 1
        //     (// in group 2,...
        //        {\?elseif //...capture "{?elseif "...
        //        [^{}]+//...a condition...
        //        }//..."}"...
        //        .*?// and the content of the {elseif} branch...
        //     )*//...as often as possible.
        //     (?:// if possible,...
        //        {\?else}//...match "{?else}"...
        //        (.+?)//...and the content of the {else} branch.
        //     )?
        // {\?endif}// finally, match "{?endif}". 
        // $if_pattern = '/{% if ([^{}]+) %}(.*?)({% elseif [^{}]+ %}.*?)*(?:{% else %}(.+?))?{% endif %}/is';
        // $if_pattern = '/{% if ([^{}]*) %}(.*?)({% elseif [^{}]* %}.*?)*(?:{% else %}(.*?))?{% endif %}/is';
        $if_pattern = '/{% if ([^{}]*) %}(.*?)(?:{% else %}(.*?))?{% endif %}/is';
        
        preg_match_all($if_pattern, $this->doc, $matches);
        // var_dump($matches);
        $count_if = 0;

        // Run over all occurrences
        foreach ($matches[0] as $k => $match) {
            // debugPrint("Vou substituir " . $match);
            // debugPrint("Vou avaliar " . $matches[1][$k]);
            // debugPrint("Se sim, então: " . $matches[2][$k]);
            // debugPrint("Senão: " . $matches[3][$k]);
            // if (array_key_exists($matches[1][$k], $this->keys)) {
            //     debugPrint("Sim, existe.");;
            // } else {
            //     debugPrint("Não, não existe.");;                
            // }

            $count_if++;
            // debugPrint($count_if . "|" . $k . "|" . $matches[1][$k] . "|" . $matches[2][$k] . "|" . $matches[3][$k]);
            foreach($matches as $keyPart => $Valuepart) {
                debugPrint("\$matches[" . $keyPart . "][\$k]: " . $matches[$keyPart][$k]);
            }
            // debugPrint($match);
            if ( empty($matches[1][$k]) ) {
                $bResult = false;
            } else {
                $evalCond = "\$bResult = (" . $matches[1][$k] . ");";
                debugPrint($evalCond);
                eval($evalCond);
            }
            if ( $bResult ) {
                debugPrint("If condition TRUE");
                debugPrint($matches[2][$k]);
                // If condition is TRUE
                $this->doc = str_replace($match, $matches[2][$k], $this->doc);
            } else {
                if ( !empty($matches[4][$k]) ) {
                    debugPrint("Else condition TRUE");
                    debugPrint($matches[4][$k]);
                    // Else condition is TRUE
                    $this->doc = str_replace($match, $matches[4][$k], $this->doc);
                } else {
                    debugPrint("If condition FALSE");
                    // None condition is TRUE
                    $this->doc = str_replace($match, "", $this->doc);
                }
            }

            // $if_data = array(
            //     $matches[2][$k],
            //     $matches[3][$k]
            // );
            // $this->_setIf($);
            // $this->doc = str_replace($match, "{{ " . $matches[1][$k] . " }}", $this->doc);


            // // Check for the entry (first key)
            // if (array_key_exists($matches[1][$k], $this->keys)) {
            //     if ($this->_getKey($matches[1][$k])) {
            //         // Replace by the true
            //         $content = $matches[2][$k];
            //     } else {
            //         // Replace by the false or empty
            //         $content = $matches[3][$k];
            //     }
            //     $key_name = "if_" . $count_if . "_" . $k;
            //     $this->_setIf($key_name, $content);
            //     debugPrint("setIf $key_name = $content");

            //     // Replace this occurrence with the appropriate content
            //     $this->doc = str_replace($match, "{{ " . $key_name . " }}", $this->doc);
            // } else {
            //     $key_name = "if_" . $count_if . "_" . $k;
            //     $this->_setIf($key_name, $matches[2][$k]);
            //     debugPrint("setIf $key_name = " . $matches[2][$k]);

            //     // $this->doc = str_replace($match, $matches[3][$k], $this->doc);
            //     $this->doc = str_replace($match, "{{ " . $key_name . " }}", $this->doc);
            // }
        }
    }

    // Replace if entries
    private function _replaceIfs() {
        debugPrint("replaceIfs");
        foreach ($this->ifs as $entry => $data) {
            debugPrint("Return $entry => $data");;
            $if_template = new Template();
            $if_template->_setKeys($this->keys);
            $if_template->_setKey($entry, $data);
            $this->_setKey($entry, $if_template->_run(true));
        }
    }
    
    /**
     * Blocks
     */
    private function _extractBlocks() {
        debugPrint("extractBlocks");
        // Extract all block as entries
        $block_pattern = '/{% block (\w+) %}(.*?){% endblock %}/is';
        preg_match_all($block_pattern, $this->doc, $matches);

        if ( count($matches) > 0 ) {
            foreach ($matches[1] as $i => $entry) {
                if ( count($matches[0]) > 0 ) {
                    // Set the appropriate entry
                    $this->_setKey($entry, $matches[2][$i]);

                    // Clear block content
                    $this->doc = str_replace($matches[0][$i], NULL, $this->doc);
                }
            }
        }
    }

    /**
     * Keys
     */
    private function _replaceKeys() {
        debugPrint("replaceKeys");
        // Replace template keys
        $entry_pattern = '/{{ ([\w\.]+) }}/';

        foreach($this->keys as $key => $value) {
            preg_match_all($entry_pattern, $this->doc, $matches);

            if ( count($matches) > 0 ) {
                foreach($matches[1] as $i => $entry) {
                    if ( array_key_exists($entry, $this->keys) ) {
                        $this->doc = str_replace($matches[0][$i], $this->keys[$entry], $this->doc);
                    } else {
                        $this->doc = str_replace($matches[0][$i], NULL, $this->doc);
                    }
                }
            }
        }
    }

    /**
     * Handle template
     */
    private static function _getTemplate($template) {
        $tpl_full_name = Template::VIEWS_PATH . str_replace(".", DIRECTORY_SEPARATOR, $template) . ".tpl.php";
        if (file_exists($tpl_full_name)) {
            return file_get_contents($tpl_full_name);
        } else {
            return "";
        }

    }
}
?>