<?php
include_once APPLICATION_PATH . '/forms/Common.php';
class Application_Form_Ipaddress extends Zend_Form
{

	public function init()
	{
	    $this->setDisableLoadDefaultDecorators(true);
	    $validator_ip = new Zend_Validate_Ip();
	    //search form START
	    //ip_address_search
	    $element = new Zend_Form_Element_Text('ip_address_search', array('size' => 31));
	    $element->addFilter('StripTags')
	    		->addFilter('StringTrim')
			    ->setAllowEmpty(true)
			    ->setRequired(false);
	    $this->addElement($element, 'ip_address_search');
	    //url_search
	    $element = new Zend_Form_Element_Text('url_search', array('size' => 31));
	    $element->addFilter('StripTags')
			    ->addFilter('StringTrim')
			    ->setAllowEmpty(true)
			    ->setRequired(false);
	    $this->addElement($element, 'url_search');
	    //mnemonics_search
	    $element = new Zend_Form_Element_Text('mnemonics_search', array('size' => 31));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(true)
			    ->setRequired(false)
			    ->addFilter('StringTrim');
	    $this->addElement($element, 'mnemonics_search');
	    //Owner Name_search
	    $element = new Zend_Form_Element_Text('owner_name_search', array('size' => 31));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(true)
			    ->setRequired(false)
			    ->addFilter('StringTrim');
	    $this->addElement($element, 'owner_name_search');
	    //search button
	    $this->addElement('submit', 'button-search', array(
	    		'ignore'   => true,
	    		'label'    => 'Search',
	    		'attribs' => array('class' => 'btn')
	    ));
	    //search form END
	    //modify form START
	    //ip_address
	    $element = new Zend_Form_Element_Text('ip_address', array('attribs' => array('class' => 'textField', 'size' => 31, 'maxlength' => 15)));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(false)
			    ->setRequired(true)
			    ->addFilter('StringTrim')
			    ->addValidator($validator_ip);    
	    $this->addElement($element, 'ip_address');
	    //url
	    $element = new Zend_Form_Element_Text('url', array('attribs' => array('class' => 'textField', 'size' => 31, 'maxlength' => 30)));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(false)
			    ->setRequired(false)
			    ->addFilter('StringTrim')
			    ->addValidator(new Url_Validator);
	    $this->addElement($element, 'url');
	    
	    //Mnemonics
	    $element = new Zend_Form_Element_Text('mnemonics',  array('attribs' => array('class' => 'textField', 'size' => 31, 'maxlength' => 3)));
	    $element->addFilter('StripTags')
			    ->setRequired(false)
			    ->addFilter('StringTrim')
	    		->addValidator('stringLength', false, array(1, 3));
	    $this->addElement($element, 'mnemonics');
	    
	    //owner_name
	    $element = new Zend_Form_Element_Text('owner_name', array('attribs' => array('class' => 'textField', 'size' => 31, 'maxlength' => 30)));
	    $element->addFilter('StripTags')
			    ->setRequired(false)
			    ->addFilter('StringTrim');
	    $this->addElement($element, 'owner_name');
	    //id hidden field
	    $element = new Zend_Form_Element_Hidden('id');
	    $this->addElement($element, 'id');
	    //pid hidden field
	    $element = new Zend_Form_Element_Hidden('pid');
	    $this->addElement($element, 'pid');
	    
	    //save button
	    $this->addElement('submit', 'submit-button', array(
	    		'ignore'   => true,
	    		'label'    => 'Save',
	    		'attribs' => array('class' => 'btn')
	            ));    
	    //modify form END
	}
}
?>