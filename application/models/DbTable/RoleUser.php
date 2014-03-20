<?php
class Base_Model_DbTable_RoleUser extends Zend_Db_Table
{

	protected $_name="user_role";
	
    public function grantRole($user_id, $role_id)
    {
    	if (!$this->hasRole($user_id,$role_id)){
	        $data = array(
	        	'user_id' => $user_id,
	        	'role_id' => $role_id
	    	);
	    	$this->insert($data);
    	}
    }
    
    public function revokeRole($user_id, $role_id)
    {
    	if ($this->hasRole($user_id,$role_id)){
	        $where = array(
	        	'user_id=?' => $user_id,
	        	'role_id=?' => $role_id
	    	);
	    	$this->delete($where);
    	}
    }
    
	public function hasRole($user_id, $role_id)
	{
		$select = $this->_db->select()
			->from($this->_name)
			->where('user_id=?',$user_id)
			->where('role_id=?',$role_id);		
		$result = $this->getAdapter()->fetchOne($select);
		
		return $result ? true : false;
	}
	
    public function getRoles()
    {
    	$bmrt = new Base_Model_DbTable_Role();
    	$results = $this->_db->fetchAll(
    		$this->_db->select()
    			->from(array('u_r' => $this->_name),array('user_id'))
    			->join(array('r' => $bmrt->_name), 'u_r.role_id = r.id')
    			->order('r.id')
    	);
    	$roles = array();
		foreach ($results as $key => $value) {
			$user_id = $value['user_id'];
			$role = new Base_Model_Role($value);
			if (!$roles[$user_id]){
				$roles[$user_id] = array();
			}
            $roles[$user_id][$role->id] = $role;
        }
        return $roles;
	}
	
	public function getRolesForUser($user_id)
	{
		$bmrt = new Base_Model_DbTable_Role();
    	$results = $this->_db->fetchAll(
    		$this->_db->select()
    			->from(array('u_r' => $this->_name),array())
    			->where("user_id=?",$user_id)
    			->join(array('r' => $bmrt->_name), 'u_r.role_id = r.id')
    			->order('r.id')
    	);
    	$roles = array();
		foreach ($results as $key => $value) {
            array_push($roles, new Base_Model_Role($value));
        }
        return $roles;
	}
}