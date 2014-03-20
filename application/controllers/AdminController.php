<?php
class AdminController extends Zend_Controller_Action
{

	public function indexAction()
	{
	}
	
	public function listUsersAction()
	{
		$userTable = new Base_Model_DbTable_User();
		$roleUserTable = new Base_Model_DbTable_RoleUser();

		$this->view->allRoles = Base_Model_DbTable_Role::getAllRoles();
		$this->view->users = $userTable->fetchAll();
		$this->view->rolesByUser = $roleUserTable->getRoles();
		$this->view->newUserForm = new Base_Form_Auth_Registration();
	}

	public function editUserRolesAction()
	{
		if ($this->getRequest()->isPost()){
			$roleUserTable = new Base_Model_DbTable_RoleUser();
			$userIds = $_POST['users'];
			$roleId = $_POST['role'];
			if ($_POST['action'] == 'revoke'){
				foreach ($userIds as $userId => $null){
					$roleUserTable->revokeRole($userId,$roleId);
				}
			} else if ($_POST['action'] == 'grant'){
				foreach ($userIds as $userId => $null){
					$roleUserTable->grantRole($userId,$roleId);
				}
			}
		}
		$this->_helper->redirector('list-users');
	}

	public function listRolesAction()
	{
		$this->view->roles = Base_Model_DbTable_Role::getAllRoles();
	}

	public function addRoleAction()
	{
		if ($this->getRequest()->isPost()){
			$roleName = $_POST['rolename'];
			$role = new Base_Model_Role(array('name'=>$roleName));
			$roleTable = new Base_Model_DbTable_Role();
			$roleTable->addRole($role);
		}
		$this->_helper->redirector('list-roles');
	}

	public function deleteRolesAction()
	{
		if ($this->getRequest()->isPost()){
			$roleIds = $_POST['roles'];
			$roleTable = new Base_Model_DbTable_Role();
			foreach ($roleIds as $roleId => $null){
				$roleTable->deleteRole($roleId);
			}
			
		}
		$this->_helper->redirector('list-roles');
	}
	
	public function listResourcesAction()
	{
		$resourceTree = ZFBase_ResourceTree::buildWithRoles();
		$this->view->resourceTree = $resourceTree;
	}
	
	public function createUserAction()
	{
		$form = new Base_Form_Auth_Registration();
		if ($this->getRequest()->isPost()
				&& $form->isValid($_POST)) {
			$data = $form->getValues();
			$userTable = new Base_Model_DbTable_User();
			if(!$userTable->isUsernameAvailable($data['username'])){
				$this->view->flash("This username is not available. Please choose another one.", 'error');
			} else {
				$userTable->insert($data);
				$this->view->flash("Account created successfully.", 'success');
			}
		} else {
			$this->view->flash("Invalid submission.", 'error');
		}
		$this->_helper->redirector('list-users');
	}
	
	public function listRolesForResourceAction()
	{
		$resourceTable = new Base_Model_DbTable_Resource();
		$roleResourceTable = new Base_Model_DbTable_RoleResource();
		$resourceUrl = $this->getRequest()->getParam('resource');
		if ($this->getRequest()->isPost()){
			$resource = $resourceTable->getResourceByUrl(urldecode($resourceUrl), true);
			$allRoles = Base_Model_DbTable_Role::getAllRoles();
			$roleIds = array_keys($_POST['roles']);
			foreach ($allRoles as $role){
				if (in_array($role->id,$roleIds)){
					$roleResourceTable->requireRole($role,$resource);
				} else {
					$roleResourceTable->unrequireRole($role,$resource);
				}
			}
			$this->_helper->redirector('list-resources');
		} else {
			$resource = $resourceTable->getResourceByUrl(urldecode($resourceUrl));
			$rolesRequired = $roleResourceTable->getRolesRequired($resource);
			$roleIdsRequired = array();
			foreach ($rolesRequired as $role){
				$roleIdsRequired[] = $role->id;
			}
			$this->view->resource = $resource;
			$this->view->roleIdsRequired = $roleIdsRequired;
			$this->view->roles = Base_Model_DbTable_Role::getAllRoles();
		}		
	}	
}