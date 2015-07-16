<?php

class Application_Model_Common extends Zend_Db_Table_Abstract{
    
    protected $_name = 'users';
    protected $_username = "";
    protected $_permission_type = "";
    protected $_CommonController = null;
    
    public function init()
    {
    	/* Initialize action controller here */
        $this->_CommonController = new CommonController();
    	$infoUser = $this->loadInfoUser();
        if ($infoUser!=null) {
        	$this->_username = $infoUser->username;
        	$this->_permission_type = $infoUser->permission_type;
        }
    }
    
    public function loadFunctionUser(){
    	$select = $this->select();
    	$select->setIntegrityCheck(false);
    	$select->from(array('fu' => 'function_user'));
    	$select->where('fu.username = ?', $this->_username);
    	$select->order('fu.function_user_id ASC');
    	$stmt = $select->query();
    	$result = $stmt->fetchAll();
//     	echo $select->__toString() . "\n";
    	return $result;
    }
    
    public function loadClientFromProject($project_id){
        $select = $this->select();
        $select->setIntegrityCheck(false);
        $select->from(array('pr' => 'project'), array('pr.project_id', 'pr.project_name', 'pr.start_date', 'pr.user_create', 'pr.description'));
        $select->join(array('cl' => 'client'),
        		'cl.client_id=pr.client_id',
        		array('cl.client_id', 'cl.client_name'));
//         $select->join(array('pu' => 'project_ip_user'),
//         		'pu.project_id=pr.project_id'
//                 , array('pu.project_id'));
//         if ($this->_permission_type!=ADMIN) {
//         	$select->where('tr.user_create = ?', $this->_username);
//         }
        $select->where('md5(pr.project_id) = ?', $this->_CommonController->decodeKey($project_id));
        $stmt = $select->query();
        $result = $stmt->fetchObject();
//         echo $select->__toString() . "\n";
        return $result;
    }
    
    public function loadList($tableName, $fieldList='*', $idName=null, $idValue=null, $sortName=null){
    	$select = $this->select();
    	$select->setIntegrityCheck(false);
    	$select->from($tableName, $fieldList);
    	if ($idValue!=null) {
    		$select->where($idName . ' = ?', $idValue);
    	}
    	if ($sortName!=null) {
    		$select->order($sortName);
    	}
    	$stmt = $select->query();
    	$result = $stmt->fetchAll();
//     	echo $select->__toString() . "\n";
    	return $result;
    }
    
    public function loadDetail($tableName, $idName=null, $idValue=null){
    	$select = $this->select();
    	$select->setIntegrityCheck(false);
    	$select->from($tableName);
    	if ($idValue!=null) {
    		$select->where($idName . ' = ?', $idValue);
    	}
    	$stmt = $select->query();
    	$result = $stmt->fetchObject();
//     	    	echo $select->__toString() . "\n";
    	return $result;
    }
    
    public function checkDuplicate($tablename, $fieldname1, $value1, $fieldname2="", $value2="") {
    	$select = $this->select();
    	$select->setIntegrityCheck(false);
    	$select->from($tablename);
    	if ($value1!=null) {
    		$select->where($fieldname1 . ' = ?', $value1);
    	}
    	if ($value2!=null) {
    		$select->where($fieldname2 . ' = ?', $value2);
    	}
    	$stmt = $select->query();
    	$result = $stmt->fetchObject();
//     	    	    	echo $select->__toString() . "\n";
    	return $result;
    }
    
    public function loadInfoUser(){
		$userLogon = $this->_CommonController->loadUserLogon();
        $infoUser = $this->loadDetail('users', 'username', $userLogon->username);
        $infoUser->token = $userLogon->token;
        $infoUser->client_ip = $userLogon->client_ip;
        return $infoUser;
    }
}
?>