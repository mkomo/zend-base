<?php

class ZFBase_Controller_Plugin_Acl extends Zend_Controller_Plugin_Abstract
{

    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
    	$module = $request->getModuleName();
    	$controller = $request->getControllerName();
    	$action = $request->getActionName();
    	$resource = new Base_Model_Resource(array(
			'module'=>$module,
			'controller'=>$controller,
			'action'=>$action
		));
		
		ZFBase_Benchmark::getInstance()->startTimer('cache');
		$allRolesRequired = $this->getRolesRequiredCached($resource);
		ZFBase_Benchmark::getInstance()->stopTimer('cache');
        if (!self::hasRoles($allRolesRequired)) {
            if (ZFBase_User::isLoggedIn()) {
                // authenticated, denied access, forward to index
                $request->setModuleName('default');
                $request->setControllerName('auth');
                $request->setActionName('access-denied');
            } else {
                // not authenticated, forward to login form
                $request->setModuleName('default');
                $request->setControllerName('auth');
                $request->setActionName('login');
                $request->setParam("attempted_url",array('module'=>$module, 'controller'=>$controller, 'action'=>$action));
            }
        }
    }
    
    protected function getRolesRequired(Base_Model_Resource $resource){
    	$roleResourceTable = new Base_Model_DbTable_RoleResource();
		$allRolesRequired = $roleResourceTable->getAllRolesRequired($resource);
		return $allRolesRequired;
    }
    
    protected function getRolesRequiredCached(Base_Model_Resource $resource){
	    $frontendOptions = array(
		   'lifetime' => 7200, // cache lifetime of 2 hours
		   'automatic_serialization' => true
		);
		 
		$backendOptions = array(
		    'cache_dir' => '/tmp/' // Directory where to put the cache files
		);
		 
		// getting a Zend_Cache_Core object
		$cache = Zend_Cache::factory('Core',
		                             'File',
		                             $frontendOptions,
		                             $backendOptions);
		 
		$item = 'rope';
//		// see if a cache already exists:
//		if(!$result = $cache->load($item)) { 
//		    echo "Saving $item to cache.\n\n";
//		    $cache->save($this->resourceTree, $item);
//		    $result = $cache->load($item);
//		} else {
//		    // cache hit! shout so that we know
//		    echo "This one is from cache!\n\n";
//		}
		$result = $cache->load($item);
		return $result->getAllRolesRequired($resource);
    }
    
    protected static function hasRoles($rolesRequired)
    {
    	if (!empty($rolesRequired)){
	        $userRoles = ZFBase_User::getRoles();
	        foreach ($rolesRequired as $role){
	        	if (sizeof($userRoles) == 0 || !in_array($role,$userRoles, FALSE)){
	        		return false;
	        	}
	        }
    	}
    	return true;
    }
}