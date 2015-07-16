<?php

class Application_Form_Index extends Zend_Form
{
    public function init()
    {       
        // Create and configure username element:
        $element = $this->createElement('text', 'username');
        $element->addFilter('StripTags')
			    ->addValidator('alnum')
//         $element->addValidator('regex', false, array('/^[a-zA-Z0-9]{6,20}$/'))
// 		        ->addValidator('stringLength', false, array(3, 20))
		        ->setRequired(true)
		        ->setAttribs(array('class' => 'textField', 'size' => '50', 'maxlength' => 20, 'autocomplete' => 'off'))
		        ->addFilter('StringToLower');
        $this->addElement($element);
                
        // Create and configure password element:
		$element = new Zend_Form_Element_Password('password');
        $element->setRenderPassword(true)
// 		        ->addValidator('StringLength', false, array(8, 20))
		        ->setRequired(true)
		        ->setAttribs(array('class' => 'textField', 'size' => '50', 'maxlength' => 20, 'autocomplete' => 'off'));
        $this->addElement($element);

		//save button
        $this->addElement('submit', 'submit-button', array(
        		'ignore'   => true,
        		'label'    => 'Login',
        		'attribs' => array('class' => 'button')
        ));
        
    }
}
?>