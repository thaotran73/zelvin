<?php
class Application_Model_Client extends Zend_Db_Table_Abstract{
    
    protected $_name = 'client';
    protected $_username = "";
    protected $_CommonController = null;
    protected $_CommonModel = null;
    
    public function init()
    {
    	/* Initialize action controller here */
    	$this->_CommonController = new CommonController();
    	$this->_CommonModel = new Application_Model_Common();
    	$infoUser = $this->_CommonModel->loadInfoUser();
    	if ($infoUser!=null) {
    	    $this->_username = $infoUser->username;
    	}
    }
    
    /**
     * load list of data
     * @param: $arrCondition
     * @return: list of data
     * **/
    public function loadList($arrCondition = NULL)
    {    	 
    	$select = $this->select();
    	$select->distinct(true);
    	$select->setIntegrityCheck(false);
    	$select->from(array('cl' => 'client'),
	    			array('cl.client_id', 'cl.client_name', 'cl.email', 'cl.address', 'cl.phone'));
    	
    	if (isset($arrCondition['client_name']) && $arrCondition['client_name']!=''):
    		$select->where('cl.client_name like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['client_name']) . '%');
    	endif;
    	if (isset($arrCondition['email']) && $arrCondition['email']!=''):
    		$select->where('cl.email like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['email']) . '%');
    	endif;
    	if (isset($arrCondition['address']) && $arrCondition['address']!=''):
    		$select->where('cl.address like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['address']) . '%');
    	endif;
    	if (isset($arrCondition['phone']) && $arrCondition['phone']!=''):
    		$select->where('cl.phone like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['phone']) . '%');
    	endif;
    	$select->order('cl.client_id DESC');
    	$stmt = $select->query();
    	$result = $stmt->fetchAll();
//     	echo $select->__toString() . "\n";
    	return $result;
    } // public function loadList($arrCondition)
    
    /**
     * load a record of ip address
     * @param: id
     * @return: a record
     * **/
    public function loadDetail($id = NULL)
    {
    	$select = $this->select();
    	$select->from('client', array('client_id', 'client_name', 'email', 'address', 'phone'));
    	if ($id!=''):
    		$select->where('md5(client_id) = ?', $this->_CommonController->decodeKey($id));
    	endif;
    	$stmt = $select->query();
    	$result = $stmt->fetch();
    	return $result;
    	
    } // public function loadDetail($arrCondition)
    
    /**
     * delete records of client
     * @param: list of client
     * **/
    public function deleteItems($delList){
    	try {
    		$arrDelete = explode(',', $delList);
    		foreach ($arrDelete as $key=>$value) {
    			$where = array('md5(client_id) = ?' => $this->_CommonController->decodeKey($value));
    			$this->delete($where);
    		}
    	} catch (Exception $e) {
    		// pass possible exceptions to log file
        	$this->_CommonController->writeLog($e->getMessage());
    	}
    } // public function deleteItems($delList)
}
?>