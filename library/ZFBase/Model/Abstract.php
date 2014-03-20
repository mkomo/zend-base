<?php

class ZFBase_Model_Abstract
{

    public function __construct(array $options = null)
    {
        if (is_array($options)) {
            $this->setData($options);
        }
    }

    public function setData(array $options)
    {
        $vars = get_class_vars($this->getClassName());
        foreach ($options as $key => $value) {
            if (array_key_exists($key,$vars)) {
                $this->$key = $value;
            }
        }
        return $this;
    }

    public function getData()
    {
        $data = array();
		foreach (get_class_vars($this->getClassName()) as $key => $value) {
			$data[$key] = $this->$key;
		}
        return $data;
    }
    
    public function getClassName()
    {
    	return (string) get_class($this);
    }
}