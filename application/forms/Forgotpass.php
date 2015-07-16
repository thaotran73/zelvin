<?php
class Application_Form_Forgotpass extends Zend_Form
{
    public function init()
    {              
        $element = new Zend_Form_Element_Text('email', array('attribs' => array('class' => 'textField', 'size' => 40, 'maxlength' => 50)));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(false)
			    ->setRequired(true)
			    ->addFilter('StringTrim')
			    ->addValidator('EmailAddress',  TRUE  );
	    $this->addElement($element, 'email');
	    
	    //save button
	    $this->addElement('submit', 'submit-button', array(
	    		'ignore'   => true,
	    		'label'    => 'Save',
	    		'attribs' => array('class' => 'button')
	    ));
	    
	    //cancel button
	    $this->addElement('button', 'cancel-button', array(
	    		'ignore'   => true,
	    		'label'    => 'Cancel',
	    		'attribs' => array('class' => 'button')
	    ));
    }
}
?>