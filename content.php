<?php
defined('MYBLOG') || die();

use \Michelf\MarkdownExtra;

class Content {
	
	private $id;
	public $writer;
	public $content;
	public $title;
	private $timestamp;
    private $version;
	
	public function __construct($id) {
		$this->id = $id;
	}
	
	public function load() {
        global $MYBLOG_MARKDOWN_PARSER;
		$file = @fopen(MYBLOG_BASEPATH . "/data/post-{$this->id}", 'r');
		if($file === false) return false;
		$version = explode('|',fgets($file));
		if($version[0] != 'version') { //legacy
			$this->id = $version[0];
			$this->timestamp = false;
            $this->version = 'legacy';
		} elseif(trim($version[1]) == '1.1' || trim($version[1]) == '1.2') {
			$this->id = fgets($file);
			$this->timestamp = (int)fgets($file);
            $this->version = trim($version[1]);
		}
		$this->writer = fgets($file);
		$this->title = fgets($file);
		while($line = fgets($file)) $this->content .= $line;
        if($this->version == '1.2') $this->content = $MYBLOG_MARKDOWN_PARSER->parse($this->content);
		@fclose($file);
	}
	
	public function save() {
		return file_put_contents(MYBLOG_BASEPATH . "/data/post-{$this->id}", "version|1.2\n".$this->id."\n".time()."\n".$this->writer."\n".$this->title."\n".$this->content) !== false;
	}
	
	public function getTimestamp() {
		return $this->timestamp;
	}
    
    public function getVersion() {
        return $this->version;
    }
    
    public function getID() {
        return $this->id;
    }
	
	public static function getLatestID() {
		if(!file_exists(MYBLOG_BASEPATH . "/data/post-latest")) file_put_contents(MYBLOG_BASEPATH . "/data/post-latest", "0");
        $id = file_get_contents(MYBLOG_BASEPATH . "/data/post-latest");
		return is_numeric($id) ? intval($id) : 0;
	}
	
	public static function setLatestID($id) {
        if(is_numeric($id)) file_put_contents(MYBLOG_BASEPATH . "/data/post-latest", $id);
	}
}
?>