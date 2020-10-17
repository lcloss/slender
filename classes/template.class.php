<?php

class Template {
    const RESOURCES_PATH = "resources" . DIRECTORY_SEPARATOR;
    const VIEWS_PATH = self::RESOURCES_PATH . "views" . DIRECTORY_SEPARATOR;

    private $tpl_name = "";
    private $tpl = "";
    private $doc = "";
    private $blocks = [];
    private $keys = [];
    private $fors = [];
    private $ifs = [];

    public function __construct( $template = '' ) {
        $this->tpl_name = $template;
        if ( $template != '' ) {
            $this->setData(Template::_getTemplate($template));
        }
    }
    
    /**
     * Setters
     */
    public function setData($data) {
        debugPrint("setData");
        $this->doc = $data;
    }

    private function _setKey($key, $value) {
        debugPrint("Set key " . $key);
        $this->keys[$key] = $value;
    }

    private function _setKeys($keys) {
        debugPrint("setKeys");

        foreach($keys as $key => $value) {
            $this->_setKey($key, $value);
        }
    }

    private function _setBlock($key, $value) {
        debugPrint("setBlock");
        debugPrint("   Set " . $key . " with: " . $value);

        $this->blocks[$key] = $value;
    }

    private function _setBlocks($blocks) {
        foreach( $blocks as $key => $value ) {
            $this->_setBlock($key, $value);
        }
    }

    private function _setFor($key, $value) {
        $this->fors[$key] = $value;
    }

    private function _setIf($key, $value) {
        $this->ifs[$key] = $value;
    }

    /**
     * Getters
     */
    private function _getData() {
        return $this->doc;
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
     * Display template Output
     */
    public function display($data = []) {
        debugPrint("display");

        $this->_setKeys($data);
        $this->_parse();
        // $this->_clearKeys();
        echo $this->_getData();
    }

    /**
     * Parse content
     */
    private function _parse() {
        debugPrint("-----------------------------------------------");
        debugPrint("Parsing " . $this->tpl_name);
        debugPrint($this->doc);

        // First process is {% use %} blocks
        $this->_extractBlocks();
        $this->_processUse();
        $this->_replaceBlocks();

        $this->_replaceKeys();

        // Than, check and process each block as they appear
        $this->_processStructs();






        // // First extract all fors
        // $this->_extractFors();
        // // ...than replace fors keys
        // $this->_replaceFors();

        // $this->_extractIfs();
        // $this->_replaceIfs();

        // $this->_replaceKeys();
    }

    /**
     * Use
     */
    private function _processUse() {
        debugPrint("processUse");

        // Check for 'use' a template
        $use_pattern = '/{% use (.*) %}/i';
        preg_match($use_pattern, $this->doc, $matches);

        if ( count($matches) > 0 ) {
            $parent_template = $matches[1];

            // Clear tag
            $this->doc = preg_replace($use_pattern, NULL, $this->doc);

            // Compose parent template
            $template = new Template($parent_template);
            $template->_setKey('content', $this->doc);
            // // Process blocks from $keys
            // $template->_setBlocks( $this->blocks );
            // $template->_replaceBlocks( $this->keys );

            $template->_replaceKeys();
            
            $this->doc = $template->_getData();
        }
        debugPrint("End of processUse");
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
            foreach ($matches[0] as $j => $block) {
                debugPrint("   Extract " . $matches[1][$j]);
                // Set the appropriate entry
                $this->_setBlock($matches[1][$j], $matches[2][$j]);

                // Clear block content
                $this->doc = str_replace($block, NULL, $this->doc);
            }
        }
    }

    private function _replaceBlocks() {
        debugPrint("replaceBlocks");

        foreach( $this->blocks as $key => $value ) {
            debugPrint("   Replacing " . $key . "...");
            $this->doc = str_replace("{{ " . $key . " }}", $value, $this->doc);
        }
    }

    /**
     * Replace Keys
     */
    private function _replaceKeys() {
        debugPrint("replaceKeys");
        debugPrint("   Template: " . $this->tpl_name, 5);

        // Replace all current keys
        foreach( $this->keys as $key => $value ) {
            if ( is_array( $value ) ) {
                debugPrint("   Value (" . $key . ") is an array and will be postpone.", 6);
            } else {
                debugPrint("   Replacing " . $key . " by " . $value, 6);
                $this->doc = str_replace( "{{ " . $key . " }}", $value, $this->doc );
            }
        }

        $continue = true;
        while( $continue ) {
            $continue = false;

            // Check an alternative way for blocks
            $replace_pattern = '/{%[^\$%]+(\$[\w\.]+)[^%]+%}/is';
            preg_match_all( $replace_pattern, $this->doc, $matches );
            if ( count( $matches ) > 0 ) {
                foreach( $matches[0] as $j => $expression ) {
                    // Remove '$' at begining of string
                    $search_key = substr( $matches[1][$j], 1, strlen( $matches[1][$j] ) -1 );
                    debugPrint("   Found " . $search_key . " inside " . $matches[0][$j], 4);
                    if ( array_key_exists( $search_key, $this->keys ) ) {
                        debugPrint("   Key exists and will be replaced", 6);

                        // Create a new match replacing first key founded
                        $new_match = str_replace( $matches[1][$j], '"' . $this->keys[$search_key] . '"', $matches[0][$j] );
                        debugPrint("   New match: " . $new_match, 6);

                        // Replace this new match into match found to remove key reference
                        $this->doc = str_replace( $matches[0][$j], $new_match, $this->doc );
                        // Continue to lookup for more keys (even on the same expression)
                        $continue = true;
                    } else {
                        debugPrint("   Key does not exists: " . $search_key, 6);
                        debugPrint("   Matches: ", 6);
                        debugPrint("   " . $matches[0][$j], 6);
                        debugPrint("   Doc: ", 6);
                        debugPrint("    " . $this->doc, 6);
                        if ( $search_key == 'dbname' ) {
                            debugPrint("   dbname. Listing current keys:", 6);
                            foreach( $this->keys as $key => $value ) {
                                debugPrint("   Key: " . $key, 6);
                            }
                        }
                    }
                }
            }
        }
    }

    /** 
     * Check and process all structs
     */
    private function _processStructs() {
        debugPrint("processStructs");

        $struct_pattern = '/{% ([\w]+)/i';
        $continue = 1;
        $continue_last = 0;
        $doc_last = "";
        $antilooping_count = 0;
        $tries = 0;

        while( $continue > 0 ) {
            $antilooping_count++;

            preg_match($struct_pattern, $this->doc, $matches);

            // Check how many structs left
            $continue = count( $matches );

            // If this number did not changed, than close the looping
            if ( $continue == $continue_last && $this->doc == $doc_last ) {
                if ( $tries >= 3 ) {
                    debugPrint("   Struct not solved!", 3);
                    debugPrint($this->doc, 3);
                    $continue = 0;
                } else {
                    $tries++;
                }
            } else {
                $continue_last = $continue;
                $doc_last = $this->doc;
            }

            if ( $continue > 0 ) {
                switch ( $matches[1] ) {
                    case 'if':
                        $this->_processIf($tries);
                        break;
        
                    case 'for':
                        $this->_processFor();
                        break;
        
                }
                $this->_replaceKeys();
            }

            if ( $antilooping_count > 100000 ) {
                debugPrint("   Antilooping detectd: " . $antilooping_count, 1);
                $continue = 0;
            }
        }
    }
    
    /**
     * If
     */
    private function _processIf($tries = 0) {
        debugPrint("processIf", 3);

        $pos = strpos( $this->doc, '{% if' );

        // https://www.phpliveregex.com/p/xcy
        $if_pattern = '/{% if ([^{}]*) %}(?:(?!{% if).)*?(?:{% else %}(.*?))?{% endif %}/is';
        preg_match_all($if_pattern, $this->doc, $matches);

        if ( $pos !== false && $tries > 0 ) {
            debugPrint("   If not detected!", 3);
            var_dump( $matches );
            debugPrint("   ==>  " . substr( $this->doc, $pos, 100), 3);
        }
        if ( count( $matches ) > 0 ) {
            foreach( $matches[0] as $j => $expression ) {
                debugPrint("   Expression: " . $expression, 3);

                // Check if there is a variable not replaced
                if ( strpos( $matches[1][$j], "\$" ) !== false ) {
                    debugPrint("   If has a variable not replaced: " . $matches[1][$j], 3);
                    $count_ifs = count( $this->ifs );
                    $if_key = "\$ifno_" . $count_ifs;
                    $this->_setIf( $if_key, $matches[0][$j]);
                    $this->doc = str_replace( $matches[0][$j], '{{ ' . $if_key . ' }}', $this->doc);
                } else {
                    // Extract condition
                    $cond = "\$eval = " . $matches[1][$j] . ";";
                    debugPrint("   Eval: " . $cond);
                    eval( $cond );
                    if ( $eval ) {
                        debugPrint("      Condition: TRUE", 3);
                        $this->doc = str_replace( $matches[0][$j], $matches[2][$j], $this->doc);
                    } else {
                        debugPrint("      Condition: FALSE", 3);
                        // Check if there is an ELSE:
                        if ( empty( $matches[3][$j] ) ) {
                            $this->doc = str_replace( $matches[0][$j], NULL, $this->doc);
                        } else {
                            $this->doc = str_replace( $matches[0][$j], $matches[3][$j], $this->doc);
                        }
                    }
                }
            }

        }
    }

    /**
     * For
     */
    private function _processFor() {
        debugPrint("processFor");
        debugPrint("   Template: " . $this->tpl_name);

        // https://www.phpliveregex.com/p/xcz
        $if_pattern = '/{% for ([^{}]*) %}((?:(?!{% for).)*?){% endfor %}/is';
        preg_match_all($if_pattern, $this->doc, $matches);

        if ( count( $matches ) > 0 ) {
            foreach( $matches[1] as $j => $key ) {
                debugPrint("   Check For " . $key);

                if ( array_key_exists( $key, $this->keys )) {
                    debugPrint("   Processing For $key...");

                    if ( !is_array( $this->keys[$key] )) {
                        die('Key $key must be an array!');
                    }

                    $content = "";

                    // Process all occurrences from keys
                    foreach( $this->keys[$key] as $item ) {
                        if ( is_array( $item ) ) {
                            // Create a block template
                            $for_template = new Template();
                            $template_data = $matches[2][$j];

                            $for_keys = new Template();
                            $for_keys->setData( $template_data );
                            foreach( $this->fors as $f_key => $f_data ) {
                                $for_keys->_setKey( $f_key, $f_data );
                            }
                            $for_keys->_replaceKeys();
                            $template_data = $for_keys->_getData();

                            $for_template->setData( $template_data );
                            foreach( $item as $prop => $attr ) {
                                $for_template->_setKey( $key . "." . $prop, $attr);
                            }
                            $for_template->_replaceKeys();
                            $content .= $for_template->_getData();

                        } else {
                            // Direct replacement
                            $content .= $item;
                        }
                    }
                    debugPrint("   New content:");
                    debugPrint($content);
                 
                    $this->doc = str_replace( $matches[0][$j], $content, $this->doc );
                } else {
                    debugPrint("   Key not exists: " . $key);

                    // Add key for later process
                    $this->_setFor( $key, $matches[2][$j] );
                    $this->doc = str_replace( $matches[0][$j], '{{ \$' . $key . ' }}', $this->doc);
                    debugPrint("   Create For key: {{ \$" . $key . " }}");
                }
            }
        }
    }



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
                $cond_template->setData($matches[1][$k]);
                $cond_template->_setKeys($this->keys);
                $cond_template->_parse();
                $cond = $cond_template->_getData();
                $evalCond = "\$bResult = (" . $cond . ");";
                debugPrint("Evaluationg COND:");
                debugPrint($evalCond);
                eval($evalCond);
            }
            if ( $bResult ) {
                debugPrint("If condition TRUE");
                $do_template = new Template();
                $do_template->setData($matches[2][$k]);
                $do_template->_setKeys($this->keys);
                $do_template->_parse();
                $do = $do_template->_getData();
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
                    $do_template->setData($matches[4][$k]);
                    $do_template->_setKeys($this->keys);
                    $do_template->_parse();
                    $do = $do_template->_getData();
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
                        $for_template->setData($this->_getFor($entry));
                        foreach($data as $key => $value) {
                            $for_template->_setKey($entry . '.' . $key, $value);
                        }
                    }
                    // This replaceKeys is necessary to replace the for keys.
                    $for_template->_replaceKeys();
                    $block .= $for_template->_getData();
                }
                $this->_setKey($entry, $block);
            }
        }
    }

    /**
     * Clear Keys
     */
    private function _clearKeys() {
        debugPrint("clearKeys");
        $keys_pattern = '/{{ ([\w\.])+ }}/is';

        preg_match_all( $keys_pattern, $this->doc, $matches );

        if ( count($matches) > 0 ) {
            foreach( $matches[1] as $i => $entry ) {
                $this->doc = str_replace( $matches[0][$i], NULL, $this->doc );
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