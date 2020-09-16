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
        $this->_replaceIfs();

        $this->_replaceKeys();

        // ...and at last: replace blocks
        $this->_extractBlocks();
        $this->_extractUse();
    }

    private function _setKey($key, $value) {
        $this->keys[$key] = $value;

        if ( is_array($value) ) {
            // Need to check if we have an array inside another array
            foreach ( $value as $keyArr ) {
                if ( is_array( $keyArr ) ) {
                    foreach ( $keyArr as $v_key => $v_data ) {
                        if ( is_array( $v_data ) ) {
                            $this->_setKey($key . "." . $v_key, $v_data);
                        }
                    }
                }
            }
        }
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
                    // This replaceKeys is necessary to replace the for keys.
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
        debugPrint("extractIfs");
        // See: https://stackoverflow.com/questions/26074070/matching-if-elseif-else-statement-with-regular-expression
        // $if_pattern = '/{% if ([^{}]*) %}(.*?)({% elseif [^{}]* %}.*?)*(?:{% else %}(.*?))?{% endif %}/is';
        // $if_pattern = '/{% if ([^{}]*) %}(.*?)(?:{% else %}(.*?))?{% endif %}/is';
        $this->_replaceKeys();
        $if_pattern = '/{% if ([^{}]*) %}(.*?)(?:{% else %}(.*?))?{% endif %}/is';
        
        preg_match_all($if_pattern, $this->doc, $matches);

        // Run over all occurrences
        foreach ($matches[0] as $k => $match) {
            foreach($matches as $keyPart => $Valuepart) {
                debugPrint("\$matches[" . $keyPart . "][\$k]: " . $matches[$keyPart][$k]);
            }

            // debugPrint($match);
            if ( empty($matches[1][$k]) ) {
                $bResult = false;
            } else {
                $cond_template = new Template();
                $cond_template->_setData($matches[1][$k]);
                $cond_template->_setKeys($this->keys);
                $cond = $cond_template->_run(true);
                $evalCond = "\$bResult = (" . $cond . ");";
                debugPrint("Evaluationg COND:");
                debugPrint($evalCond);
                eval($evalCond);
            }
            if ( $bResult ) {
                debugPrint("If condition TRUE");
                $do_template = new Template();
                $do_template->_setData($matches[2][$k]);
                $do_template->_setKeys($this->keys);
                $do = $do_template->_run(true);
                debugPrint($do);
                // If condition is TRUE
                // $this->doc = str_replace($match, $matches[2][$k], $this->doc);
                $this->ifs[] = array(
                    'block' => $match,
                    'content' => $do
                );
            } else {
                if ( !empty($matches[4][$k]) ) {
                    debugPrint("Else condition TRUE");
                    $do_template = new Template();
                    $do_template->_setData($matches[4][$k]);
                    $do_template->_setKeys($this->keys);
                    $do = $do_template->_run(true);
                    debugPrint($do);
                    // Else condition is TRUE
                    // $this->doc = str_replace($match, $do, $this->doc);
                    $this->ifs[] = array(
                        'block' => $match,
                        'content' => $do
                    );
                } else {
                    debugPrint("If condition FALSE");
                    // None condition is TRUE
                    // $this->doc = str_replace($match, "", $this->doc);
                    $this->ifs[] = array(
                        'block' => $match,
                        'content' => ""
                    );
                }
            }
        }
    }

    // Replace if entries
    private function _replaceIfs() {
        debugPrint("replaceIfs");
        foreach ($this->ifs as $data) {
            $this->doc = str_replace($data['block'], $data['content'], $this->doc);
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
            debugPrint("   Replacing $key...");
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
        debugPrint("   Final:");
        debugPrint($this->doc);
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