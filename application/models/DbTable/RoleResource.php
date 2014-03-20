<?php
class Base_Model_DbTable_RoleResource extends Zend_Db_Table
{
	protected $_name="role_resource";
	
	public function getRolesRequired(Base_Model_Resource $resource)
	{
		$roleTable = new Base_Model_DbTable_Role();
		$resourceTable = new Base_Model_DbTable_Resource();
		if (!$resource->id){
			$resource = $resourceTable->getResource($resource->getData());
			if (!$resource->id){
				return array();
			}
		}
    	$results = $this->_db->fetchAll(
    		$this->_db->select()
    			->from(array('role_resource' => $this->_name),array())
    			->where("resource_id=?",$resource->id)
    			->join(array('role' => $roleTable->_name), 'role_resource.role_id = role.id')
    			->order('role.id')
    	);
    	$roles = array();
		foreach ($results as $key => $value) {
            array_push($roles, $value['name']);
        }
        return $roles;
	}
	
	public function getAll(){
		$roleTable = new Base_Model_DbTable_Role();
		$resourceTable = new Base_Model_DbTable_Resource();
    	$results = $this->_db->fetchAll(
			$this->_db->select()
    			->from(array('role_resource' => $this->_name),array())
    			->join(array('role' => $roleTable->_name), 'role_resource.role_id = role.id',array('role_name'=>'name'))
    			->join(array('resource' => $resourceTable->_name), 'role_resource.resource_id = resource.id')
    	);
    	$resourcesByRole = array();
		foreach ($results as $result) {
			$roleName = $result['role_name'];
			if (!$resourcesByRole[$roleName]){
				$resourcesByRole[$roleName] = array();
			}
			$resourcesByRole[$roleName][] = new Base_Model_Resource($result);
        }
        return $resourcesByRole;
	}
	
	public function getAllRolesRequired(Base_Model_Resource $resource){
		$roles = $this->getRolesRequired($resource);
		while ($resource->getParent() != null) {
            $resource = $resource->getParent();
            $roles = array_merge($roles, $this->getRolesRequired($resource));
        }
        return $roles;
	}
	
	public function requireRole(Base_Model_Role $role, Base_Model_Resource $resource)
    {
    	if (!$this->isRoleRequired($role,$resource)){
	        $data = array(
	        	'role_id' => $role->id,
	        	'resource_id' => $resource->id
	    	);
	    	$this->insert($data);
    	}
    }
    
    public function unrequireRole(Base_Model_Role $role, Base_Model_Resource $resource)
    {
    	if ($this->isRoleRequired($role,$resource)){
	        $where = array(
				'role_id=?'=>$role->id,
				'resource_id=?'=>$resource->id	
	    	);
	    	$this->delete($where);
    	}
	}
	
	public function isRoleRequired(Base_Model_Role $role, Base_Model_Resource $resource){
		$select = $this->_db->select()
			->from($this->_name,array('id'))
			->where('role_id=?',$role->id)
			->where('resource_id=?',$resource->id);		
		$result = $this->getAdapter()->fetchOne($select);
		
		return $result ? true : false;
	}
}