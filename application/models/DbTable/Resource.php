<?php
class Base_Model_DbTable_Resource extends Zend_Db_Table
{
	public $_name="resource";
	
	public function getResource($data, $addIfNew=false)
	{
		$select = $this->_db->select()
			->from($this->_name)
			->where('module=?',$data['module']);
		if ($data['controller']){
			$select->where('controller=?',$data['controller']);
		} else {
			$select->where('controller is null');
		}
		if ($data['action']){
			$select->where('action=?',$data['action']);
		} else {
			$select->where('action is null');
		}
		$result = $this->getAdapter()->fetchRow($select);
		if ($result){
			$resource = new Base_Model_Resource($result);
		} else {
			$resource = new Base_Model_Resource($data);
			if ($addIfNew){
				$this->insert($data);
				return $this->getResource($data);
			}
		}
		return $resource;
	}
	
	public function getResourceByUrl($url, $addIfNew=false)
	{
		$resource = Base_Model_Resource::fromUrl($url);
		$resource = $this->getResource($resource->getData(), $addIfNew);
		return $resource;
	}
}