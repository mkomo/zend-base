<?php

class ZFBase_ResourceTree {
	private $root;
	private $roles = array();
	private $children = array();
	private $parent;
	
	public static function buildWithRoles(){
		$resourceTree = ZFBase_ResourceTree::getAllResources();
		$roleResourceTable = new Base_Model_DbTable_RoleResource();
		$resourcesByRole = $roleResourceTable->getAll();
		foreach ($resourcesByRole as $roleName => $resources){
			foreach ($resources as $resource) {
				$resourceTree->requireRoleForResource($resource, $roleName);
			}
		}
		return $resourceTree;
	}
	
	public function __construct(Base_Model_Resource $root, ZFBase_ResourceTree $parent = null)
    {
        $this->root = $root;
        $this->parent = $parent;
    }
	public function addResource(Base_Model_Resource $resource){
		foreach ($this->walkNodes() as $node){
			if ($node->getRoot()->isParentOf($resource)){
				$node->appendChild($resource);
				return;
			}
		}
	}
	public function requireRoleForResource(Base_Model_Resource $resource, $roleName){
		foreach ($this->walkNodes() as $node){
			if ($node->getRoot()->getUrl() == $resource->getUrl()){
				$node->requireRole($roleName);
				return;
			}
		}
	}
	public function getRolesRequired(){
		return $this->roles;
	}
	public function getRolesRequiredInherited(){
		if ($this->parent == null){
			return array();
		} else {
			return array_merge($this->parent->getRolesRequired(),$this->parent->getRolesRequiredInherited());
		}
	}
	public function getAllRolesRequired(){
		if ($this->parent == null){
			return $this->roles;
		} else {
			return array_merge($this->parent->getRolesRequired(),$this->roles);
		}
	}
	public function requireRole($roleName){
		$this->roles[$roleName] = $roleName; 
	}
	public function appendChild(Base_Model_Resource $resource){
		$this->children[$resource->getUrl()] = new ZFBase_ResourceTree($resource, $this);
	}
	public function getRoot(){
		return $this->root;
	}
	
	public function walkNodes(){
		$childNodeArray = array_map(array("self","callWalkNodes"),$this->children);
		if (end($childNodeArray) && is_array(end($childNodeArray))){
			$childNodeArray = $this->flatten($childNodeArray);	
		}
		return array_merge(array($this),$childNodeArray);
	}
	
	public static function callWalkNodes(ZFBase_ResourceTree $bmrt){
		return $bmrt->walkNodes();
	}
	
	protected static function flatten($nodeArray)
	{
		$final = array();
		foreach ($nodeArray as $nodes){
			if (is_array($nodes)){
				foreach ($nodes as $node){
					$final[] = $node;
				}
			} else {
				$final[] = $node;
			}
		}
		return $final;
	}
	
	public static function getAllResources()
	{
		$front = Zend_Controller_Front::getInstance();
		$resourceTree;
		foreach ($front->getControllerDirectory() as $module => $path) {
			$moduleResource = new Base_Model_Resource(array(
				'module'=>$module,
			));
			$resourceTree = new ZFBase_ResourceTree($moduleResource);
			foreach (scandir($path) as $file) {
				if (self::endswith($file, "Controller.php")) {
					include_once $path . DIRECTORY_SEPARATOR . $file;
					$class = substr($file, 0, strpos($file, ".php"));
					if (is_subclass_of($class, 'Zend_Controller_Action')) {
						$controller = self::camelCaseToDashed(substr($class, 0, strpos($class, "Controller")));
						$controllerResource = new Base_Model_Resource(array(
							'module'=>$module,
							'controller'=>$controller
						));
						$resourceTree->addResource($controllerResource);
						foreach (get_class_methods($class) as $action) {
							if (self::endswith($action, "Action")) {
								$action = self::camelCaseToDashed(substr($action, 0, strpos($action, "Action")));
								$actionResource = new Base_Model_Resource(array(
									'module'=>$module,
									'controller'=>$controller,
									'action'=>$action
								));
								$resourceTree->addResource($actionResource);
							}
						}
					}			
				}
			}
		}
		return $resourceTree;
	}
	
	static function camelCaseToDashed($str) {
		$str[0] = strtolower($str[0]);
		$func = create_function('$c', 'return "-" . strtolower($c[1]);');
		return preg_replace_callback('/([A-Z])/', $func, $str);
	}
	
	
	static function endswith($string, $test) {
	    $strlen = strlen($string);
	    $testlen = strlen($test);
	    if ($testlen > $strlen) return false;
	    return substr_compare($string, $test, -$testlen) === 0;
	}
}