<?php
class Application_Form_Importfile extends Zend_Form
{
    protected $_ext_up = "nessus, nmap";
    public function init()
    {
    	$this->setDisableLoadDefaultDecorators(true);
    	$element = new Zend_Form_Element_Checkbox('chk_overwrite', array("checked" => "checked"));
    	$element->setDescription('Overwrite to existing data?')
       			->setRequired(false);
    	$element->getDecorator('Description')->setOption('style', 'display: inline');
    	$this->addElement($element);
    	//upload file
    	$element = new Zend_Form_Element_File('importFile');
    	$element->setDescription(INFO_UPLOADFILE_MSG);
    	$element->setDestination(FILE_UPLOAD_DESTINATION);
    	$element->setRequired(true);
    	$element->setMaxFileSize(maxfilesize);
    	$element->addValidator('Count', false, 1);
    	// limit to 100K
    	$element->addValidator('Size', false, maxfilesize);
//     	$validator = new Zend_Validate_File_Upload();
//     	$validator->setMessages(array(
//     			Zend_Validate_File_Upload::INI_SIZE => LIMIT_FILESIZE_MSG,
//     			Zend_Validate_File_Upload::NO_FILE => SELECT_FILE_IMPORT_MSG,
//     	));
//     	$element->addValidator($validator);
    	// only nmap, nessus
//     	$element->addValidator('Extension', false, $this->_ext_up);
    	$element->setIgnore(true);
    	$this->addDecorators(array(
    			array('ViewHelper'),
    			array('Errors'),
    			array('Description', array('tag' => 'p', 'class' => 'description')),
    			array('HtmlTag', array('tag' => 'dd')),
    			array('Label', array('tag' => 'dt')),
    	));
    	$element->setValueDisabled(true);
    	$this->addElement($element, 'importFile');
    	//submit button    	
    	$this->addElement('submit', 'submit-button', array(
	            'ignore'   => true,
	            'label'    => 'Execute',
    			'attribs' => array('class' => 'button')
    	)); 
    }
}
?>