<?php
class Application_Form_Search extends Zend_Form
{

	public function init()
	{
	    $this->setDisableLoadDefaultDecorators(true);
    	//ip_address_search
    	$element = new Zend_Form_Element_Text('ip_address_search', array('size' => 31));
    	$element->addFilter('StripTags')
		    	->addFilter('StringTrim')
		    	->setAllowEmpty(true)
		    	->setRequired(false);
    	$this->addElement($element, 'ip_address_search');
    	//port_search
    	$element = new Zend_Form_Element_Text('port_search', array('size' => 31));
    	$element->addFilter('StripTags')
		    	->addFilter('StringTrim')
		    	->setAllowEmpty(true)
		    	->setRequired(false);
    	$this->addElement($element, 'port_search');
    	//state_search
    	$element = new Zend_Form_Element_Text('state_search', array('size' => 31));
    	$element->addFilter('StripTags')
		    	->addFilter('StringTrim')
		    	->setAllowEmpty(true)
		    	->setRequired(false);
    	$this->addElement($element, 'state_search');
    	//protocol_search
    	$element = new Zend_Form_Element_Text('protocol_search', array('size' => 31));
    	$element->addFilter('StripTags')
		    	->addFilter('StringTrim')
		    	->setAllowEmpty(true)
		    	->setRequired(false);
    	$this->addElement($element, 'protocol_search');
    	//service_search
    	$element = new Zend_Form_Element_Text('service_search', array('size' => 31));
    	$element->addFilter('StripTags')
		    	->addFilter('StringTrim')
		    	->setAllowEmpty(true)
		    	->setRequired(false);
    	$this->addElement($element, 'service_search');
    	//mnemonics_search
    	$element = new Zend_Form_Element_Text('mnemonics_search', array('size' => 31));
    	$element->addFilter('StripTags')
		    	->setAllowEmpty(true)
		    	->setRequired(false)
		    	->addFilter('StringTrim');
    	$this->addElement($element, 'mnemonics_search');
    	//search button
    	$this->addElement('submit', 'button-search', array(
    			'ignore'   => true,
    			'label'    => 'Search',
    			'attribs' => array('class' => 'btn')
    	));
	}
}

?>