<?php
include_once APPLICATION_PATH . '/forms/Common.php';
class Application_Form_Nessusplugin extends Zend_Form
{

	public function init()
	{
	    $this->setDisableLoadDefaultDecorators(true);
	    $arrRiskLevel = array('' => '', '3' => 'High', '2' => 'Medium', '1' => 'Low', '0' => 'Issue');
	    //search form START
	    //plugin_id_search
	    $element = new Zend_Form_Element_Text('plugin_id_search', array('size' => 31));
	    $element->addFilter('StripTags')
			    ->addFilter('StringTrim')
			    ->setAllowEmpty(true)
			    ->setRequired(false);
	    $this->addElement($element, 'plugin_id_search');
	    //title_search
	    $element = new Zend_Form_Element_Text('title_search', array('size' => 31));
	    $element->addFilter('StripTags')
	    		->addFilter('StringTrim')
			    ->setAllowEmpty(true)
			    ->setRequired(false);
	    $this->addElement($element, 'title_search');
	    //explanation_search
	    $element = new Zend_Form_Element_Text('explanation_search', array('size' => 31));
	    $element->addFilter('StripTags')
			    ->addFilter('StringTrim')
			    ->setAllowEmpty(true)
			    ->setRequired(false);
	    $this->addElement($element, 'explanation_search');
	    //plugin_output_search
	    $element = new Zend_Form_Element_Text('plugin_output_search', array('size' => 31));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(true)
			    ->setRequired(false)
			    ->addFilter('StringTrim');
	    $this->addElement($element, 'plugin_output_search');
	    
	    //solution_search
	    $element = new Zend_Form_Element_Text('solution_search', array('size' => 31));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(true)
			    ->setRequired(false)
			    ->addFilter('StringTrim');
	    $this->addElement($element, 'solution_search');
	    
	    $element = new Zend_Form_Element_Select('risk_level_search', array('attribs' => array('style' => 'width:90%')));
	    $element->setRequired(false);
	    $element->addMultiOptions($arrRiskLevel);
	    $this->addElement($element);
	    
	    //search button
	    $this->addElement('submit', 'button-search', array(
	    		'ignore'   => true,
	    		'label'    => 'Search',
	    		'attribs' => array('class' => 'btn')
	    ));
	    //search form END
	    //modify form START
	    //plugin_id
	    $element = new Zend_Form_Element_Text('plugin_id', array('class' => 'textField', 'style' => 'width:100%'));
	    $element->addFilter('StripTags')
			    ->addFilter('StringTrim')
			    ->setAllowEmpty(false)
			    ->setRequired(true)
	    		->addValidator(new Int_Validate());
	    $this->addElement($element, 'plugin_id');
	    //title
	    $element = new Zend_Form_Element_Textarea('title', array('attribs' => array('class' => 'textField', 'style' => 'width:100%')));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(true)
			    ->setRequired(false)
			    ->addFilter('StringTrim'); 
	    $this->addElement($element, 'title');
	    
	    //explanation
	    $element = new Zend_Form_Element_Textarea('explanation', array('attribs' => array('class' => 'textField', 'style' => 'width:100%')));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(true)
			    ->setRequired(false)
			    ->addFilter('StringTrim'); 
	    $this->addElement($element, 'explanation');
	    
	    //plugin_output
	    $element = new Zend_Form_Element_Textarea('plugin_output', array('attribs' => array('class' => 'textField', 'style' => 'width:100%')));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(true)
			    ->setRequired(false)
			    ->addFilter('StringTrim'); 
	    $this->addElement($element, 'plugin_output');
	    
	    //risk_level
	    $element = new Zend_Form_Element_Select('risk_level', array('attribs' => array('class' => 'textField', 'style' => 'width:100%')));
	    $element->setRequired(true);
	    $element->addMultiOptions($arrRiskLevel);
	    $this->addElement($element);
	    
	    //solution
	    $element = new Zend_Form_Element_Textarea('solution', array('attribs' => array('class' => 'textField', 'style' => 'width:100%')));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(true)
			    ->setRequired(false)
			    ->addFilter('StringTrim');
	    $this->addElement($element, 'solution');
	    
	    //id hidden field
	    $element = new Zend_Form_Element_Hidden('plid');
	    $this->addElement($element, 'plid');
	    $element = new Zend_Form_Element_Hidden('id');
	    $this->addElement($element, 'id');
	    //pid hidden field
	    $element = new Zend_Form_Element_Hidden('pid');
	    $this->addElement($element, 'pid');
	    
	    //cancel button
	    $this->addElement('button', 'cancel-button', array(
	    		'ignore'   => true,
	    		'label'    => 'Cancel',
	    		'attribs' => array('class' => 'btn', 'style' => 'width:60px; display: inline-block', 'onclick' => 'javascript:doCancel()')
	    ));
	    
	    //save button
	    $this->addElement('submit', 'submit-button', array(
	    		'ignore'   => true,
	    		'label'    => 'Save',
	    		'attribs' => array('class' => 'btn', 'style' => 'width:60px; display: inline-block')
	            ));    
	    //modify form END
	}
}
?>