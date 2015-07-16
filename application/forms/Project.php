<?php
include_once APPLICATION_PATH . '/forms/Common.php';
class Application_Form_Project extends Zend_Form
{

	public function init()
	{
	    $this->setDisableLoadDefaultDecorators(true);
	    //modify START
// 	    $validator = new Zend_Validate_Ip();
	    //client combobox
	    $element = new Zend_Form_Element_Select('client_code', array('attribs' => array('class' => 'textField', 'style' => 'width:100%')));
	    $element->setRequired(true);
	    $this->addElement($element);
	    
	    $element = new Zend_Form_Element_Text('client_name', array('class' => 'textField', 'size' => 31, 'readonly'=>true));
	    $this->addElement($element, 'client_name');
	    
	    $element = new Zend_Form_Element_Text('project_name', array('class' => 'textField', 'size' => 31, 'maxlength' => 50));
	    $element->addFilter('StripTags')
			    ->addFilter('StringTrim')
			    ->setAllowEmpty(false)
			    ->setRequired(true);
	    $this->addElement($element, 'project_name');
	        
	    $element = new Zend_Form_Element_Text('start_date', array('attribs' => array('class' => 'textField', 'size' => 31)));
	    $element->addFilter('StripTags')
	    		->setAllowEmpty(false)
			    ->setRequired(true)
	    		->addValidator(new Zend_Validate_Date('MM/DD/YYYY'));
	    $this->addElement($element, 'start_date');
	    
	    $element = new Zend_Form_Element_Text('start_date_view', array('attribs' => array('class' => 'textField', 'size' => 31, 'readonly' => true)));
	    $this->addElement($element, 'start_date_view');
	    
	    $element = new Zend_Form_Element_Text('user_create', array('attribs' => array('class' => 'textField', 'size' => 31, 'readonly' => true)));
	    $element->addFilter('StripTags');
	    $this->addElement($element, 'user_create');
	    
	    $element = new Zend_Form_Element_Textarea('description', array('attribs' => array('class' => 'textField', 'style' => 'width:100%')));
	    $element->addFilter('StripTags')
	    		->setAllowEmpty(true)
	    		->setRequired(false);
	    $this->addElement($element, 'description');
	    
	    $element = new Zend_Form_Element_Textarea('description_view', array('attribs' => array('class' => 'textField', 'style' => 'width:100%', 'readonly' => true)));
	    $this->addElement($element, 'description_view');
	    
	    //id hidden field
	    $element = new Zend_Form_Element_Hidden('cid');
	    $this->addElement($element, 'cid');
	    $element = new Zend_Form_Element_Hidden('pid');
	    $this->addElement($element, 'pid');
	    
	    $element = new Zend_Form_Element_Hidden('old_client_code');
	    $this->addElement($element, 'old_client_code');
	    $element = new Zend_Form_Element_Hidden('old_project_name');
	    $this->addElement($element, 'old_project_name');
	    $element = new Zend_Form_Element_Hidden('old_start_date');
	    $element->addFilter('StripTags');
	    $this->addElement($element, 'old_start_date');
	    
	    //save button
	    $this->addElement('submit', 'submit-button', array(
	    		'ignore'   => true,
	    		'label'    => 'Save',
	    		'attribs' => array('class' => 'btn')
	    ));
	    //modify END
	    //search START
	    //user_create_search
	    $element = new Zend_Form_Element_Text('user_create_search', array('size' => 31));
	    $element->addFilter('StripTags')
			    ->addFilter('StringTrim')
			    ->setAllowEmpty(true)
			    ->setRequired(false);
	    $this->addElement($element, 'user_create_search');
	    //client_name_search
	    $element = new Zend_Form_Element_Text('client_name_search', array('size' => 31));
	    $element->addFilter('StripTags')
			    ->addFilter('StringTrim')
			    ->setAllowEmpty(true)
			    ->setRequired(false);
	    $this->addElement($element, 'client_name_search');
	    //project_name_search
	    $element = new Zend_Form_Element_Text('project_name_search', array('size' => 31));
	    $element->addFilter('StripTags')
			    ->addFilter('StringTrim')
			    ->setAllowEmpty(true)
			    ->setRequired(false);
	    $this->addElement($element, 'project_name_search');
	    //start_date_search
	    $element = new Zend_Form_Element_Text('start_date_search', array('size' => 31));
	    $element->addFilter('StripTags')
			    ->addFilter('StringTrim')
			    ->setAllowEmpty(true)
			    ->setRequired(false)
	    		->addValidator(new Zend_Validate_Date('MM/DD/YYYY'));
	    $this->addElement($element, 'start_date_search');
	    //search button
	    $this->addElement('submit', 'button-search', array(
	    		'ignore'   => true,
	    		'label'    => 'Search',
	    		'attribs' => array('class' => 'btn')
	    ));
	    //search END
	}
}