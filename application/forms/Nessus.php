<?php
include_once APPLICATION_PATH . '/forms/Common.php';
class Application_Form_Nessus extends Zend_Form
{

	public function init()
	{
	    $this->setDisableLoadDefaultDecorators(true);
	    //modify form START
	    //ip_address
	    $element = new Zend_Form_Element_Text('ip_address', array('attribs' => array('class' => 'textField', 'size' => 31, 'readonly' => "readonly")));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(true)
			    ->setRequired(false);    
	    $this->addElement($element, 'ip_address');
	    
	    //state
	    $element = new Zend_Form_Element_Text('state', array('attribs' => array('class' => 'textField', 'size' => 31, 'readonly' => "readonly")));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(true)
			    ->setRequired(false);
	    $this->addElement($element, 'state');
	    
	    //port
	    $element = new Zend_Form_Element_Text('port', array('attribs' => array('class' => 'textField', 'size' => 31, 'readonly' => "readonly")));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(true)
			    ->setRequired(false);
	    $this->addElement($element, 'port');
	    
	    //protocol
	    $element = new Zend_Form_Element_Text('protocol',  array('attribs' => array('class' => 'textField', 'size' => 31, 'readonly' => "readonly")));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(true)
			    ->setRequired(false);
	    $this->addElement($element, 'protocol');
	    
	    //service
	    $element = new Zend_Form_Element_Text('service', array('attribs' => array('class' => 'textField', 'size' => 31, 'readonly' => "readonly")));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(true)
			    ->setRequired(false);
	    $this->addElement($element, 'service');
	    
	    //vulnerbility_state
	    $element = new Zend_Form_Element_Text('vulnerbility_state', array('attribs' => array('class' => 'textField', 'size' => 31, 'readonly' => "readonly")));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(true)
			    ->setRequired(false);
	    $this->addElement($element, 'vulnerbility_state');

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