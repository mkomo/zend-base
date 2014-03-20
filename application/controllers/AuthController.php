<?php
class AuthController extends Zend_Controller_Action
{
    
	public function loginAction()
	{
		if (ZFBase_User::isLoggedIn()){
			$this->logoutAction();
		}
		
		$loginForm = new Base_Form_Auth_Login();
		$this->view->form = $loginForm;
		
		if ($this->getRequest()->isPost()
				&& $loginForm->isValid($_POST)) {
			$success = ZFBase_User::attemptLogin(
				$loginForm->getValue('username'),
				$loginForm->getValue('password'));
			if ($success) {
				if ($this->getRequest()->getParam("attempted_url")){
					$dest = $this->getRequest()->getParam("attempted_url");
					$this->_helper->redirector($dest['action'],$dest['controller'],$dest['module']);
				} else if (ZFBase_User::hasRole('admin')){
					$this->_helper->redirector('index', 'admin');
				} else {
					$this->_helper->redirector('index', 'index');
				}
			} else {
				$this->view->flash("The credentials you supplied are incorrect.", 'error');
			}
		} else if ($this->getRequest()->getParam("attempted_url")){
			$this->view->flash("You must be logged in to see this page.", 'error');
		}
	}

	public function logoutAction()
	{
		Zend_Auth::getInstance()->clearIdentity();
		$this->_helper->redirector('index', 'index');
	}
	
	public function createUserAction()
	{
		if (ZFBase_User::isLoggedIn()){
			$this->_helper->redirector('index', 'index');
		}
		$form = new Base_Form_Auth_Registration();
		$this->view->form = $form;
		if ($this->getRequest()->isPost()
				&& $form->isValid($_POST)) {
			$data = $form->getValues();
			$userTable = new Base_Model_DbTable_User();
			if (!$userTable->isUsernameAvailable($data['username'])){
				$form->getElement('username')
					->addError("This username is not available. Please choose another one.");
				return;
			}
			$id = $userTable->insert($data);
			$u = $userTable->fetchRow("id=".$id);
			ZFBase_User::doLogin($u);
			$this->view->flash("Your account has been created successfully.", 'success');
			$this->_helper->redirector('index', 'index');
		}
	}
	
	public function accessDeniedAction(){
		$this->getResponse()->setHttpResponseCode(403);
		$this->view->flash("You are not authorized to view this page.", 'error');
	}
}
