<?php

class Base_Model_Resource extends ZFBase_Model_Abstract
{
    public static function fromUrl($url){
    	$dataRaw = explode('/',$url);
    	$data = array(
			'module'=>$dataRaw[1],
			'controller'=>$dataRaw[2],
			'action'=>$dataRaw[3]
		);
    	return new Base_Model_Resource($data);
    }
    
    public $id;
    public $module;
    public $controller;
    public $action;
    
    public function isModule(){
    	return 	$this->action == null && 
    			$this->controller == null && 
    			$this->module != null;
    }
    
    public function isController(){
    	return 	$this->action == null && 
    			$this->controller != null && 
    			$this->module != null;
    }
    
    public function isAction(){
    	return 	$this->action != null && 
    			$this->controller != null && 
    			$this->module != null;
    }
    
    public function getParent(){
    	if ($this->isModule()){
    		return NULL;
    	}
    	$parent = new Base_Model_Resource();
    	$parent->module = $this->module;
    	if ($this->isAction()){
    		$parent->controller = $this->controller;
    	}
    	return $parent;
    }
    
    public function isParentOf($resource){
    	if ($this->isModule() && $resource->isController() 
    		&& ($this->module == $resource->module)){
    		return true;
    	}
    	if ($this->isController() && $resource->isAction() 
    		&& ($this->module == $resource->module)
    		&& ($this->controller == $resource->controller)){
    		return true;
    	}
    }
    public function getUrl(){
    	if ($this->isModule()){
    		return '/'.$this->module;
    	} else if ($this->isController()){
    		return '/'.$this->module.'/'.$this->controller;
    	} else if ($this->isAction()){
    		return '/'.$this->module.'/'.$this->controller.'/'.$this->action;
    	} else {
    		return '(undefined)';
    	}
    }
    
    public function __toString(){
    	return $this->getUrl();
    }
}