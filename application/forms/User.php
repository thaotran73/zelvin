<?php
include_once APPLICATION_PATH . '/forms/Common.php';
class Application_Form_User extends Zend_Form
{
    public function init()
    {       
        // Create and configure username element:
        $element = $this->createElement('text', 'username');
        $element->addFilter('StripTags')
			    ->addValidator('alnum')
//         $username->addValidator('regex', false, array('/^[a-zA-Z0-9]{6,20}$/'))
		        ->addValidator('stringLength', false, array(3, 20))
		        ->setRequired(true)
		        ->setAttribs(array('class' => 'textField', 'size' => 31, 'maxlength' => 20));
        $this->addElement($element);
        
        $element = new Zend_Form_Element_Text('email', array('attribs' => array('class' => 'textField', 'size' => 31, 'maxlength' => 50)));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(false)
			    ->setRequired(true)
			    ->addFilter('StringTrim')
	    		->addValidator('stringLength', false, array(1, 50))
			    ->addValidator('EmailAddress',  TRUE  );
	    $this->addElement($element, 'email');

	    // Create and configure address element:
	    $element = new Zend_Form_Element_Text('address', array('attribs' => array('class' => 'textField', 'size' => 31, 'maxlength' => 100)));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(true)
			    ->setRequired(false)
			    ->addFilter('StringTrim')
			    ->addValidator('stringLength', false, array(1, 100));
	    $this->addElement($element, 'address');
	    
	    // Create and configure phone element:
	    $element = new Zend_Form_Element_Text('phone', array('attribs' => array('class' => 'textField', 'size' => 31, 'maxlength' => 20)));
	    $element->addFilter('StripTags')
			    ->setAllowEmpty(true)
			    ->setRequired(false)
			    ->addFilter('StringTrim')
			    ->addValidator('stringLength', false, array(1, 20))
// 	    		->addValidator('regex', false, array('/^\(?([0-9]{3})\)?[-. ]?([0-9]{3})[-. ]?([0-9]{4})$/'));
	    		->addValidator(new PhoneNumber_Validate());
	    $this->addElement($element, 'phone');
	    
	    // Create and configure permission_type element:
	    $element = new Zend_Form_Element_Select('permission_type', array('attribs' => array('class' => 'textField', 'style' => 'width:100%')));
	    $element->setRequired(true)
	    		->addMultiOption('', '')
	    		->addMultiOption(1, 'Admin')
	    		->addMultiOption(2, 'Agent');
	    $this->addElement($element);
	    
	    $element = new Zend_Form_Element_Text('permission_type_view', array('attribs' => array('class' => 'textField', 'size' => 31, 'readonly' => true)));
	    $this->addElement($element, 'permission_type_view');
	    
	    //id hidden field
	    $element = new Zend_Form_Element_Hidden('id');
	    $this->addElement($element);
	    
	    $element = new Zend_Form_Element_Hidden('old_username');
	    $element->addFilter('StripTags');
		$this->addElement($element, 'old_username');
		
		$element = new Zend_Form_Element_Hidden('old_email');
		$element->addFilter('StripTags');
		$this->addElement($element, 'old_email');
    
	    $element = new Zend_Form_Element_Hidden('isLoad');
	    $element->setValue(false);
	    $this->addElement($element);
	    
	    $oFunctionUser = new Application_Model_Functionuser();
	    $result = $oFunctionUser->loadListFunction();
	    foreach ($result as $oObj):
// 	    	$element = new Zend_Form_Element_Checkbox('chk_view_' . $oObj['function_name'], array('attribs' => array('onclick' => 'setValueToCheckbox(this)')));
// 	    	$this->addElement($element);
		    $this->addElement('checkbox', 'chk_insert_' . $oObj['function_name'], array('required' => false, 'attribs' => array('onclick' => 'setValueToCheckbox(this)')));
		    $this->addElement('checkbox', 'chk_update_' . $oObj['function_name'], array('required' => false, 'attribs' => array('onclick' => 'setValueToCheckbox(this)')));
		    $this->addElement('checkbox', 'chk_delete_' . $oObj['function_name'], array('required' => false, 'attribs' => array('onclick' => 'setValueToCheckbox(this)')));
		    $this->addElement('checkbox', 'chk_import_' . $oObj['function_name'], array('required' => false, 'attribs' => array('onclick' => 'setValueToCheckbox(this)')));
		    $this->addElement('checkbox', 'chk_export_' . $oObj['function_name'], array('required' => false, 'attribs' => array('onclick' => 'setValueToCheckbox(this)')));
		    $this->addElement('checkbox', 'chk_view_' . $oObj['function_name'], array('required' => false, 'attribs' => array('onclick' => 'setValueToCheckbox(this)')));
	    endforeach;
        
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
        
        //search START
        //username_search
        $element = new Zend_Form_Element_Text('username_search', array('size' => 31));
        $element->addFilter('StripTags')
		        ->addFilter('StringTrim')
		        ->setAllowEmpty(true)
		        ->setRequired(false);
        $this->addElement($element, 'username_search');
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
?>