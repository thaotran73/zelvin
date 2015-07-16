<?php

class Application_Model_User extends Zend_Db_Table_Abstract{
    
    protected $_name = 'users';
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

    public function login($uname, $paswd){
        
        $auth = Zend_Auth::getInstance ();
        $authAdapter = new Zend_Auth_Adapter_DbTable();
        $authAdapter->setTableName('users')
			        ->setIdentityColumn('username')
			        ->setCredentialColumn('password');
        
        $authAdapter->setIdentity($uname);
        $authAdapter->setCredential(md5($paswd));       
        $select = $authAdapter->getDbSelect();

        $result = $auth->authenticate($authAdapter);
        $flag = false;
        if ($result->isValid()) {
            $authSession = new Zend_Session_Namespace('Zend_Auth');
            $authSession->setExpirationSeconds(auth_timeout);
        	$data = $authAdapter->getResultRowObject(null, array('password'));
        	$token = md5($this->_CommonController->generateRandomString());
         	$data->token = $token;
         	$data->client_ip = Zend_Controller_Front::getInstance()->getRequest()->getClientIp();
        	$auth->getStorage()->write($data);
        	setcookie('token', $token);
        	$flag = true;
        }
        return $flag;
    }
    
    /**
     * load list of data
     * @param: $arrCondition
     * @return: list of data
     * **/
    public function loadList($arrCondition = NULL)
    {
    	$select = $this->select();
    	$select->setIntegrityCheck(false);
    	$select->from(array('us' => 'users'),
    			array('us.user_id', 'us.username', 'us.email', 'us.password', 'us.address', 'us.phone', 'us.permission_type'));    	 
    	if (isset($arrCondition['username']) && $arrCondition['username']!=''):
    		$select->where('us.username like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['username']) . '%');
    	endif;
    	if (isset($arrCondition['email']) && $arrCondition['email']!=''):
    		$select->where('us.email like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['email']) . '%');
    	endif;
    	if (isset($arrCondition['address']) && $arrCondition['address']!=''):
    		$select->where('us.address like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['address']) . '%');
    	endif;
    	if (isset($arrCondition['phone']) && $arrCondition['phone']!=''):
    		$select->where('us.phone like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['phone']) . '%');
    	endif;
//     	$select->order('us.username DESC');
    	$stmt = $select->query();
    	$result = $stmt->fetchAll();
    	//     	echo $select->__toString() . "\n";
    	return $result;
    } // public function loadList($arrCondition)
    
    /**
     * delete records of user
     * @param: list of user
     * **/
    public function deleteItems($delList){
    	try {
    		$arrDelete = explode(',', $delList);
    		foreach ($arrDelete as $key=>$value) {
    			$where = array('md5(user_id) = ?' => $this->_CommonController->decodeKey($value));
    			$this->delete($where);
    		}
    	} catch (Exception $e) {
    		// pass possible exceptions to log file
    		$this->_CommonController->writeLog($e->getMessage());
    	}
    } // public function deleteItems($delList)
    
    /**
     * load a record of user
     * @param: id
     * @return: a record
     * **/
    public function loadDetail($id = NULL)
    {
        $select = $this->select();
        $select->setIntegrityCheck(false);
    	$select->from(array('us' => 'users'),
    			array('us.user_id', 'us.username', 'us.email', 'us.password', 'us.address', 'us.phone', 'us.permission_type', 'us.count_time', 'us.checked_user', 'us.active_password'));
        if ($id!=''):
        	$select->where('md5(us.user_id) = ?', $this->_CommonController->decodeKey($id));
        endif;
        $stmt = $select->query();
        $result = $stmt->fetchObject();
//             	echo $select->__toString() . "\n";
        return $result;
         
	} // public function loadDetail($arrCondition)
	
	/**
	 * load a record of user
	 * @param: id
	 * @return: a record
	 * **/
	public function loadDetailFromUsername($username = NULL)
	{
		$select = $this->select();
		$select->setIntegrityCheck(false);
		$select->from(array('us' => 'users'),
				array('us.user_id', 'us.username', 'us.email', 'us.password', 'us.address', 'us.phone', 'us.permission_type', 'us.count_time', 'us.checked_user', 'us.active_password'));
		if ($username!=''):
		$select->where('us.username = ?', $username);
		endif;
		$stmt = $select->query();
		$result = $stmt->fetchObject();
		//             	echo $select->__toString() . "\n";
		return $result;
		 
	} // public function loadDetail($arrCondition)
   	    
    /**
     * forgot password
     * @param: $uname
     * @return: a record
     * **/
    public function forgotPass($email)
    {
    	$select = $this->select();
    	$select->from('users', array('username', 'password'));
    	if ($email!=''):
    		$select->where('email = ?', $email);
    	endif;
    	$stmt = $select->query();
    	$result = $stmt->fetchObject();
//     	echo $select->__toString() . "\n";
    	return $result;
    } // public function forgotPass($uname)
    
    /**
     * forgot password
     * @param: $uname
     * @return: a record
     * **/
    public function changePass($password)
    {
    	$select = $this->select();
    	$select->from('users', array('username', 'email', 'password'));
    	$select->where('username = ?', $this->_username);
    	$select->where('password = ?', md5($password));
    	$stmt = $select->query();
    	$result = $stmt->fetchObject();
//     	echo $select->__toString() . "\n";
    	return $result;
    } // public function changePass($uname)

}
?>