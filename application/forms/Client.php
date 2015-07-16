<?php
include_once APPLICATION_PATH . '/forms/Common.php';
class Application_Form_Client extends Zend_Form
{

	public function init()
	{
	    $this->setDisableLoadDefaultDecorators(true);
	    //modify START        
	    $element = new Zend_Form_Element_Text('client_name', array('class' => 'textField', 'size' => 31, 'maxlength' => 30));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(false)
			    ->setRequired(true)
			    ->addFilter('StringTrim');
	    $this->addElement($element, 'client_name');
	    
	    $element = new Zend_Form_Element_Text('email', array('class' => 'textField', 'size' => 31, 'maxlength' => 50));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(true)
			    ->setRequired(false)
			    ->addFilter('StringTrim')
			    ->addValidator('EmailAddress',  TRUE  );
	    $this->addElement($element, 'email');
	    
	    $element = new Zend_Form_Element_Text('address', array('class' => 'textField', 'size' => 31, 'maxlength' => 100));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(true)
			    ->setRequired(false)
			    ->addFilter('StringTrim');
	    $this->addElement($element, 'address');
	    
	    $element = new Zend_Form_Element_Text('phone', array('class' => 'textField', 'size' => 31, 'maxlength' => 20));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(true)
			    ->setRequired(false)
			    ->addFilter('StringTrim')
	    		->addValidator(new PhoneNumber_Validate());
// 	    		->addValidator('regex', false, array('/^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/'));
	    $this->addElement($element, 'phone');
	    
	    //id hidden field
	    $element = new Zend_Form_Element_Hidden('cid');
	    $this->addElement($element, 'cid');
	    $element = new Zend_Form_Element_Hidden('old_client_name');
	    $element->addFilter('StripTags');
	    $this->addElement($element, 'old_client_name');
	    
	    //save button
	    $this->addElement('submit', 'submit-button', array(
	    		'ignore'   => true,
	    		'label'    => 'Save',
	    		'attribs' => array('class' => 'btn')
	    ));
	    //modify END
	    //search START
	    //client_code_search
	    $element = new Zend_Form_Element_Text('client_code_search', array('size' => 31));
	    $element->addFilter('StripTags')
			    ->addFilter('StringTrim')
			    ->setAllowEmpty(true)
			    ->setRequired(false);
	    $this->addElement($element, 'client_code_search');
	    //client_name_search
	    $element = new Zend_Form_Element_Text('client_name_search', array('size' => 31));
	    $element->addFilter('StripTags')
			    ->addFilter('StringTrim')
			    ->setAllowEmpty(true)
			    ->setRequired(false);
	    $this->addElement($element, 'client_name_search');
	    //email_search
	    $element = new Zend_Form_Element_Text('email_search', array('size' => 31));
	    $element->addFilter('StripTags')
			    ->addFilter('StringTrim')
			    ->setAllowEmpty(true)
			    ->setRequired(false);
	    $this->addElement($element, 'email_search');
	    //address_search
	    $element = new Zend_Form_Element_Text('address_search', array('size' => 31));
	    $element->addFilter('StripTags')
			    ->addFilter('StringTrim')
			    ->setAllowEmpty(true)
			    ->setRequired(false);
	    $this->addElement($element, 'address_search');
	    //phone_search
	    $element = new Zend_Form_Element_Text('phone_search', array('size' => 31));
	    $element->addFilter('StripTags')
			    ->addFilter('StringTrim')
			    ->setAllowEmpty(true)
			    ->setRequired(false);
	    $this->addElement($element, 'phone_search');
	    //search button
	    $this->addElement('submit', 'button-search', array(
	    		'ignore'   => true,
	    		'label'    => 'Search',
	    		'attribs' => array('class' => 'btn')
	    ));
	    //search END
	}
}