<?php

namespace xepan\cms;

class Model_Layout extends \Model {
	public $namespace = "xepan\cms";
    public $path;

	function init(){
		parent::init();

		$this->addField('name');
		$path = $this->path = $this->api->pathfinder->base_location->base_path.'/./vendor/'.str_replace("\\","/",$this->namespace)."/templates/xepan/tool/layouts";
        $p = scandir($path); 
        unset($p[0]);
        unset($p[1]);

        asort($p);
        $i=2;
        
        foreach ($p as $file) {
            // $temp = explode(".", explode("-", $file)[1]);
            
            $temp = explode(".",$file);
            $i++;
        }

        asort($p);
        $this->setSource('Array',$p);

        $this->addHook('beforeDelete',$this);

        return $this;
    }

    function path(){
    	return $this->path.'/'.$this['name'];
    }

    function getOptionPath(){
        return $this->api->pathfinder->base_location->base_path.'/./vendor/'.str_replace("\\","/",$this->namespace)."/templates/xepan/cms/tool_layout_options.html";
    }

    function beforeDelete($m){
        // throw new \Exception($this->id, 1);
        if(file_exists($this->path.'/'.$this['name'])){
            unlink($this->path.'/'.$this['name']);
        }
    }
}