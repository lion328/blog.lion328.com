<?php
defined('MYBLOG') || die();

interface IMarkdownParser {
    
    public function parse($text);
}

class MarkdownExtraMarkdownParser implements IMarkdownParser {
    
    public function parse($text) {
        return \Michelf\MarkdownExtra::defaultTransform($text);
    }
}

class ParsedownMarkdownParser implements IMarkdownParser {
    
    private $obj;
    
    public function __construct() {
        $this->obj = new Parsedown();
    }
    
    public function parse($text) {
        flush();
        return $this->obj->text($text);
    }
}
?>