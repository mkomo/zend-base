<?php

class ZFBase_User 
{

    public static function hasRole($role){
    	return in_array($role,self::getRoles());
    }

    public static function getRoles(){
    	$user_id = Zend_Auth::getInstance()->getIdentity()->id;
    	if ($user_id){
	    	$roleTable = new Base_Model_DbTable_RoleUser();
	        $roleObjects = $roleTable->getRolesForUser($user_id);
	        return array_map(create_function('$r', 'return $r->name;'), $roleObjects);
    	} else {
    		return array();
    	}
    }
    
    public static function isLoggedIn(){
    	return Zend_Auth::getInstance()->hasIdentity();
    }
    
    public static function getUserName(){
    	return Zend_Auth::getInstance()->getIdentity()->username;
    }

	public static function attemptLogin($username, $password)
	{
		$auth = Zend_Auth::getInstance();
		$db = Zend_Db_Table::getDefaultAdapter();
		$adapter = new Zend_Auth_Adapter_DbTable(
			$db,
			'user',
			'username',
			'password',
			'MD5( CONCAT(?,password_salt) )'
		);
		
		$adapter->setIdentity($username);
		$adapter->setCredential($password);

		$result = $auth->authenticate($adapter);
		
		if ($result->isValid()){
			ZFBase_User::doLogin($adapter->getResultRowObject(array('id', 'username')));
			return true;
		} else {
			return false;
		}
	}
	
	public static function doLogin($user)
	{
		Zend_Auth::getInstance()->getStorage()->write($user);
	}
}