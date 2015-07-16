<?php
class Application_Model_Functionuser extends Zend_Db_Table_Abstract{
    
    protected $_name = 'function_user';
    protected $_username = "";
    protected $_CommonModel = null;
    
    public function init()
    {
    	/* Initialize action controller here */
    	$this->_CommonModel = new Application_Model_Common();
    	$infoUser = $this->_CommonModel->loadInfoUser();
    	if ($infoUser!=null) {
    		$this->_username = $infoUser->username;
    	}
    }
    
    /**
     * load list of data
     * @param: $uname
     * @return: list of data
     * **/
    public function loadList($uname)
    {
    	$select = $this->select();
    	$select->setIntegrityCheck(false);
    	$select->from(array('fn' => 'function'),
    			array('fn.function_id', 'fn.name AS function_name'));
    	$select->joinLeft(array('fu' => 'function_user'),
    			'fn.function_id=fu.function_id AND fu.username = "'.$uname.'"',
    			array('fu.function_user_id', 'fu.username', 'fu.function_id', 'fu.f_insert', 'fu.f_update', 'fu.f_delete', 'fu.f_import', 'fu.f_export', 'fu.f_view'));
    	//     	$select->where('fu.username = ?', $uname);
    	$select->order('fn.function_id ASC');
    	$stmt = $select->query();
    	$result = $stmt->fetchAll();
//     	echo $select->__toString() . "\n";
    	return $result;
    } // public function loadList($arrCondition)
    
    /**
     * load list of data
     * @return: list of data
     * **/
    public function loadListFunction()
    {
    	$select = $this->select();
    	$select->distinct(true);
    	$select->setIntegrityCheck(false);
    	$select->from(array('fn' => 'function'),
    			array('fn.function_id', 'fn.name AS function_name'));
    	$stmt = $select->query();
    	$result = $stmt->fetchAll();
    	//     	echo $select->__toString() . "\n";
    	return $result;
    } // public function loadList($arrCondition)
    
    /**
     * load a record of Function
     * @param: id
     * @return: a record
     * **/
    public function loadDetailFunction($id = NULL)
    {
    	$select = $this->select();
    	$select->setIntegrityCheck(false);
    	$select->from('function', array('function_id', 'name'));
    	if ($id!=''):
    		$select->where('function_id = ?', $id);
    	endif;
    	$stmt = $select->query();
    	$result = $stmt->fetchObject();
    	return $result;
    	 
    } // public function loadDetail($arrCondition)
}
?>