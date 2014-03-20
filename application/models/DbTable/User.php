<?php
class Base_Model_DbTable_User extends Zend_Db_Table
{

	protected $_name="user";
	
	public function isUsernameAvailable($username)
	{
		$select = $this->_db->select()
			->from($this->_name,array('username'))
			->where('username=?',$username);		
		$result = $this->getAdapter()->fetchOne($select);
		
		return $result ? false : true;
	}
	
	public function insert($data){	    
		$db_data = $this->prepareToSave($data);
		return parent::insert($db_data);
	}
	
	protected static function prepareToSave($data){
		$db_data = array('username' => $data['username']);
		$db_data['password_salt'] = md5($db_data['username'].mt_rand());
		$db_data['password'] = md5($data['password'] . $db_data['password_salt']);
		return $db_data;
	}
}