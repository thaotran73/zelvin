<?php
class Application_Model_Ipaddress extends Zend_Db_Table_Abstract{
    
    protected $_name = 'ip_address';
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
     * load list of ip address
     * @param: $arrCondition
     * @return: list of data
     * **/
    public function loadList($arrCondition = NULL, $export = false)
    {
        $select = $this->select();
//         $select->distinct(true);
        $select->setIntegrityCheck(false);
        if (!$export){
        	$select->from(array('ip' => 'ip_address'), array('ip.ip_address_id', 'ip.ip_address', 'ip.url', 'ip.mnemonics', 'ip.owner_name'));
        }else{
            $select->from(array('ip' => 'ip_address'), array('ip.ip_address', 'ip.url', 'ip.mnemonics', 'ip.owner_name'));
        }

        $oCommonModel = new Application_Model_Common();
    	$infoProject = $oCommonModel->loadDetail('project', 'md5(project_id)', $this->_CommonController->decodeKey($arrCondition['pid']));
    	if ($this->_permission_type!=ADMIN && $infoProject->user_create!=$this->_username) {
        	$select->join(array('pip' => 'project_ip_user'),
        			'pip.project_id=ip.project_id',
        			array());
        	$select->join(array('pipd' => 'project_ip_user_detail'),
        			'pip.project_ip_user_id=pipd.project_ip_user_id',
        			array());
        	$select->where('pipd.username = ?', $this->_username);
//         	$select->where('INET_ATON(trim(ip.ip_address)) >= INET_ATON(trim(pipd.ip_address_from))');
//         	$select->where('INET_ATON(trim(ip.ip_address)) <= INET_ATON(trim(pipd.ip_address_to))');
        	$select->where('(INET_ATON(trim(ip.ip_address)) >= INET_ATON(trim(pipd.ip_address_from)) AND INET_ATON(trim(ip.ip_address)) <= INET_ATON(trim(pipd.ip_address_to))) OR (ip.mnemonics >= pipd.mnemonics_from AND ip.mnemonics <= pipd.mnemonics_to)');
        }
        
        if (isset($arrCondition['pid']) && $arrCondition['pid']!=''):
        	$select->where('md5(ip.project_id) = ?', $this->_CommonController->decodeKey($arrCondition['pid']));
        endif;
        if (isset($arrCondition['ip_address']) && $arrCondition['ip_address']!=''):
        	$select->where('ip.ip_address like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['ip_address']) . '%');
        endif;
        if (isset($arrCondition['mnemonics']) && $arrCondition['mnemonics']!=''):
    		$select->where('ip.mnemonics like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['mnemonics']) . '%');
        endif;
        if (isset($arrCondition['url']) && $arrCondition['url']!=''):
    		$select->where('ip.url like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['url']) . '%');
        endif;
        if (isset($arrCondition['owner_name']) && $arrCondition['owner_name']!=''):
    		$select->where('ip.owner_name like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['owner_name']) . '%');
        endif;
    	if (isset($arrCondition['st']) && $arrCondition['st']!=''):
    		$select->order('ip.mnemonics ' . $arrCondition['st']);
    	else:
    		$select->order('INET_ATON(trim(ip.ip_address)) ASC');
    	endif;
        $stmt = $select->query();
        $result = $stmt->fetchAll();               
        $this->_CommonController->writeLog( $select->__toString());
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
    	$select->from('ip_address', array('ip_address_id', 'ip_address', 'url', 'mnemonics', 'owner_name'));
    	if ($id!=''):
    		$select->where('md5(ip_address_id) = ?', $this->_CommonController->decodeKey($id));
    	endif;
		$stmt = $select->query();
    	$result = $stmt->fetch();
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
            	$where = array('md5(ip_address_id) = ?' => $this->_CommonController->decodeKey($value));
            	$this->delete($where);
            }
        } catch (Exception $e) {
            // pass possible exceptions to log file
        	$this->_CommonController->writeLog($e->getMessage());
        }
    } // public function deleteItems($delList)
    
    /**
     * delete records of ip address
     * @param: list of ip addresses
     * **/
    public function deleteProject($id){
    	try {
    			$where = array('md5(project_id) = ?' => $this->_CommonController->decodeKey($id));
    			$this->delete($where);
    	} catch (Exception $e) {
    		// pass possible exceptions to log file
    		$this->_CommonController->writeLog($e->getMessage());
    	}
    } // public function deleteProject($id)
    
    /**
     * load list record of ip address
     * @param: id
     * @return: array records
     * **/
    public function loadIpAddressByProjectID($project_id = NULL)
    {
    	$arrIpAddress = array();
    	$select = $this->select();
    	$select->distinct(true);
    	$select->setIntegrityCheck(false);
    	$select->from('ip_address', array('ip_address'));
    	$select->where('md5(project_id) = ?', $this->_CommonController->decodeKey($project_id));
    	$stmt = $select->query();
    	$result = $stmt->fetchAll();
    	//     	echo $select->__toString() . "\n";
    	return $result;
//     	
    } // public function loadDetail($arrCondition)
}
?>