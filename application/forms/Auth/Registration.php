<?php
class Base_Form_Auth_Registration extends Zend_Form
{
	public function init()
	{
        $this->setMethod('post');
		$this->addElement(new Zend_Form_Element_Text('username'))->getElement('username') 
			->setLabel('Username') 
			->setRequired(true) 
			->addValidator('NotEmpty', true, array('messages' => array(
                'isEmpty' => 'Please enter a username.',
            )))
			->addValidator('Regex', false, array('/^[a-zA-Z0-9]+$/',
				'messages' => array(
					Zend_Validate_Regex::NOT_MATCH => "username must contain only letters and numbers"
			)))
            ->addValidator('StringLength', false, array(2,20)); 
            
		$this->addElement(new Zend_Form_Element_Password('password'))->getElement('password') 
			->setLabel('Password') 
			->setRequired(true) 
			->addValidator('NotEmpty', true, array('messages' => array(
                'isEmpty' => 'Please enter a password.',
            ))); 
            
		$this->addElement(new Zend_Form_Element_Password('confirm_password'))->getElement('confirm_password') 
			->setLabel('Confirm Password')
			->setRequired(true) 
			->addValidator(new ZFBase_Form_Validator_IdenticalField('password', 'Password'), true)
			->addValidator('NotEmpty', true, array('messages' => array(
                'isEmpty' => 'Please reenter password.',
            ))); 
            
        $this->addElement(
        	'submit', 'submit', array(
	            'ignore'   => true,
	            'label'    => 'Create User',
            ));
	}
}