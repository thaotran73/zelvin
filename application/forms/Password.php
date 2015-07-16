<?php
include_once APPLICATION_PATH . '/forms/Common.php';
class Application_Form_Password extends Zend_Form
{
    public function init()
    {
        // Create and configure password element:
        $element = new Zend_Form_Element_Password('password');
        $element->setRenderPassword(true)
		        ->addValidator('StringLength', false, array(6, 20))
		        ->setRequired(true)
		        ->setAttribs(array('class' => 'textField', 'size' => 31, 'maxlength' => 20));
        $this->addElement($element);
         
        $element = new Zend_Form_Element_Password('new_password');
        $element->setRenderPassword(true)
		        ->addValidator('StringLength', false, array(8, 20))
		        ->setRequired(true)
		        ->setAttribs(array('class' => 'textField', 'size' => 31, 'maxlength' => 20));
        $this->addElement($element);
        
        $token = Zend_Controller_Front::getInstance()->getRequest()->getPost('new_password');
        $element = new Zend_Form_Element_Password('confirm_password');
        $element->setRenderPassword(true)
		        ->addValidator('StringLength', false, array(8, 20))
		        ->setRequired(true)
		        ->setAttribs(array('class' => 'textField', 'size' => 31, 'maxlength' => 20))
		        ->addValidator(new Zend_Validate_Identical(trim($token)));
        $this->addElement($element);
        
        //save button
        $this->addElement('submit', 'submit-button', array(
        		'ignore'   => true,
        		'label'    => 'Save',
        		'attribs' => array('class' => 'button')
        ));
    }
}
?>