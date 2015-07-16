<?php
include_once APPLICATION_PATH . '/forms/Common.php';
class Application_Form_Assignment extends Zend_Form
{

	public function init()
	{
	    $this->setDisableLoadDefaultDecorators(true);
	    //search form START
	    //ip_address_list_search
	    $element = new Zend_Form_Element_Text('ip_address_list_search', array('size' => 31));
	    $element->addFilter('StripTags')
	    		->addFilter('StringTrim')
			    ->setAllowEmpty(true)
			    ->setRequired(false);
	    $this->addElement($element, 'ip_address_list_search');
	    
	    //mnemonics_list_search
	    $element = new Zend_Form_Element_Text('mnemonics_list_search', array('size' => 31));
	    $element->addFilter('StripTags')
		    ->addFilter('StringTrim')
		    ->setAllowEmpty(true)
		    ->setRequired(false);
	    $this->addElement($element, 'mnemonics_list_search');
	    
	    //username_assigned_search
	    $element = new Zend_Form_Element_Text('username_assigned_search', array('size' => 31));
	    $element->addFilter('StripTags')
			    ->addFilter('StringTrim')
			    ->setAllowEmpty(true)
			    ->setRequired(false);
	    $this->addElement($element, 'username_assigned_search');
	    
	    //search button
	    $this->addElement('submit', 'button-search', array(
	    		'ignore'   => true,
	    		'label'    => 'Search',
	    		'attribs' => array('class' => 'btn')
	    ));
	    //search form END
	    
	    //modify form START
	    //ip_address_list
	    $element = new Zend_Form_Element_Textarea('ip_address_list', array('attribs' => array('style' => 'width:100%', 'class' => 'textField')));
	    $element->addFilter('StripTags')
			    ->addFilter('StringTrim');    
	    $this->addElement($element, 'ip_address_list');
	    
	    //mnemonics_list
	    $element = new Zend_Form_Element_Textarea('mnemonics_list', array('attribs' => array('style' => 'width:100%', 'class' => 'textField')));
	    $element->addFilter('StripTags')
			    ->addFilter('StringTrim');
	    $this->addElement($element, 'mnemonics_list');
	    
	    //username combobox
	    $element = new Zend_Form_Element_Multiselect('username', array('attribs' => array('style' => 'width:100%; line-height: 30px', 'size' => 10 )));
	    $element->setRequired(false);
	    $this->addElement($element);
	    
	    //username combobox
	    $element = new Zend_Form_Element_Multiselect('username_assigned', array('attribs' => array('style' => 'width:100%; line-height: 30px', 'size' => 10 )));
	    $element->setRequired(true);
	    $element->setRegisterInArrayValidator(false);
	    $this->addElement($element);
	    
	    $element = $this->createElement('radio','range_type');
	    $element->setLabel('Range type')
	    		->setRequired(true)
	    		->addMultiOptions(array(0 => 'IP Address', 1 => 'Mnemonics'));
	    $this->addElement($element);
	    
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