<?php

class IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
		$this->view->username = ZFBase_User::getUserName();
		$this->view->roles = ZFBase_User::getRoles();
    }
}

