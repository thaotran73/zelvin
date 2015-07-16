<?php
class Application_Model_Project extends Zend_Db_Table_Abstract{
    
    protected $_name = 'project';
    protected $_username = "";
    protected $_permission_type = "";
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
    		$this->_permission_type = $infoUser->permission_type;
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
    	$select->from(array('pr' => 'project'), array('pr.project_id', 'pr.project_name', 'pr.start_date', 'pr.user_create', 'pr.description'));
    	$select->join(array('cl' => 'client'),
	    			'cl.client_id=pr.client_id',
	    			array('cl.client_id', 'cl.client_name'));
//     	if ($this->_permission_type!=ADMIN) {
//     		$select->where('pr.user_create = ?', $arrCondition['username']);
//     	}
    	if (isset($arrCondition['user_create']) && $arrCondition['user_create']!=''):
    		$select->where('pr.user_create = ?', $arrCondition['user_create']);
    	endif;
    	if (isset($arrCondition['client_name']) && $arrCondition['client_name']!=''):
    		$select->where('cl.client_name like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['client_name']) . '%');
    	endif;
    	if (isset($arrCondition['project_name']) && $arrCondition['project_name']!=''):
    		$select->where('pr.project_name like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['project_name']) . '%');
    	endif;
    	if (isset($arrCondition['start_date']) && $arrCondition['start_date']!=''):
    		$select->where('DATE_FORMAT(pr.start_date, \'%m/%d/%Y\') = ?', $arrCondition['start_date']);
    	endif;
    	if (isset($arrCondition['cid']) && $arrCondition['cid']!=''):
    		$select->where('md5(cl.client_id) = ?', $this->_CommonController->decodeKey($arrCondition['cid']));
    	endif;
    	$select->order('pr.create_date DESC');
    	$stmt = $select->query();
    	$result = $stmt->fetchAll();
//     	echo $select->__toString() . "\n";
    	return $result;
    } // public function loadList($arrCondition)
    
    /**
     * load a record of project
     * @param: id
     * @return: a record
     * **/
    public function loadDetail($id = NULL)
    {
    	$select = $this->select();
    	$select->setIntegrityCheck(false);
    	$select->from(array('pr' => 'project'), array('pr.project_id', 'pr.project_name', 'pr.start_date', 'pr.user_create', 'pr.description'));
    	$select->join(array('cl' => 'client'),
	    			'cl.client_id=pr.client_id',
	    			array('cl.client_id', 'cl.client_name'));
    	$select->where('md5(pr.project_id) = ?', $this->_CommonController->decodeKey($id));
    	$stmt = $select->query();
    	$result = $stmt->fetchObject();
//     	echo $select->__toString() . "\n";
    	return $result;
    } // public function loadDetail($arrCondition)
    
    /**
     * delete records of ip address
     * @param: list of ip addresses
     * **/
    public function deleteItems($delList){
    	try {
    		$arrDelete = explode(',', $delList);
    		foreach ($arrDelete as $key=>$value) {
    			$where = array('md5(project_id) = ?' => $this->_CommonController->decodeKey($value));
    			$this->delete($where);
    		}
    	} catch (Exception $e) {
    		// pass possible exceptions to log file
    		$this->_CommonController->writeLog($e->getMessage());
    	}
    } // public function deleteItems($delList)
}
?>