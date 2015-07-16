<?php
class Password_Validator extends Zend_Validate_Abstract
{
    const NOT_VALID = 'passwordInvalid';
    
    protected $_messageTemplates = array(
    		self::INVALID_PASSWORD => "Password '%value%' must be in the special character."
    );
    
    public function isValid($value)
    {
    	$this->_setValue($value);
    	if(!preg_match("/^[(][0-9]{3}[)] [0-9]{3} [0-9]{4}$/", $value)) {
    		$this->_error(self::INVALID_PASSWORD);
    		return false;
    	}
    
    	return true;
    }
}


/**
 * validdate url
 * **/
class Url_Validator extends Zend_Validate_Abstract
{
	const INVALID_URL = 'invalidUrl';

	protected $_messageTemplates = array(
			self::INVALID_URL => "'%value%' is not a valid URL.",
	);

	public function isValid($value)
	{
	    if ($value!='') {
			$valueString = (string) $value;
			$this->_setValue($valueString);
	
			if (!Zend_Uri::check($value)) {
				$this->_error(self::INVALID_URL);
				return false;
			}
	    }
		return true;
	}
}

class Int_Validate extends Zend_Validate_Abstract
{
    const INVALID_INT = 'invalidInt';
    
    protected $_messageTemplates = array(
    		self::INVALID_INT => "'%value%' does not appear to be an integer.",
    );
    
    public function isValid($value)
	{
	    $valueString = (string) $value;
		$this->_setValue($valueString);
		
        if (is_numeric($value)) {
            if ($value*1<-2147483647 || $value*1>2147483647) {
	            $this->_error(self::INVALID_INT);
				return false;
            }
        }else {
            $this->_error(self::INVALID_INT);
            return false;
        }
        return true;
    }
}

class PhoneNumber_Validate extends Zend_Validate_Abstract {
    const NOT_VALID = 'phoneInvalid';
    
    protected $_messageTemplates = array(
    		self::NOT_VALID => "Phone number '%value%' must be in the format (111) 222 3333."
    );
    
    public function isValid($value)
    {
    	$this->_setValue($value);
    	if(!preg_match("/^[(][0-9]{3}[)] [0-9]{3} [0-9]{4}$/", $value)) {
    		$this->_error(self::NOT_VALID);
    		return false;
    	}
    
    	return true;
    }
}

class Utils 
{
    public function convertData2Option($result, $fieldList, $emptyFirst=true){
        $arrData = array();
        if ($emptyFirst) {
            $arrData[''] = '';
        }
        foreach ($result as $item) {
            $arrData[$item[$fieldList[0]]] = $item[$fieldList[1]];
        }
        return $arrData;
    }
}
?>