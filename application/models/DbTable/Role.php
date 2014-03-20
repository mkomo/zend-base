<?php
class Base_Model_DbTable_Role extends Zend_Db_Table
{
	public $_name="role";
    
    public function addRole(Base_Model_Role $role)
    {
        $data = $role->getData();
        unset($data['id']);
    	if (!$this->hasRole($role->name)){
	    	return $this->insert($data);
    	}
    }
    
    public function deleteRole($roleId){
    	$where = array('id=?'=>$roleId);
    	$this->delete($where);
    	$ru = new Base_Model_DbTable_RoleUser();
    	$where = array('role_id=?'=>$roleId);
    	$ru->delete($where);
    }
    
    public function hasRole($role_name)
    {
    	$select = $this->_db->select()
			->from($this->_name,array('name'))
			->where('name=?',$role_name);		
		$result = $this->getAdapter()->fetchOne($select);
		
		return $result ? true : false;
    }
    
    public function getAllRoles(){
    	$roles = array();
    	$roleTable = new Base_Model_DbTable_Role();
        $fetchedRoles = $roleTable->fetchAll(null,'id');
    	foreach ($fetchedRoles as $key => $value) {
			array_push($roles, new Base_Model_Role($value->toArray()));
        }
        return $roles;
    }
}