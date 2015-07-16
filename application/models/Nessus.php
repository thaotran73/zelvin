<?php
class Application_Model_Nessus extends Zend_Db_Table_Abstract{
    protected $_name = 'nessus';
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
    public function loadList($arrCondition = NULL, $export = false)
    {
    	if (!$export) {
    		$detail_fields = array('nessus_detail_id', 'port', 'state', 'protocol', 'service');
    	}else{
    		$detail_fields = array('port', 'state', 'protocol', 'service');
    	}
    	 
    	$select = $this->select();
//     	$select->distinct(true);
    	$select->setIntegrityCheck(false);
    	if (!$export) {
    		$select->from(array('nm' => 'nessus'), array('nm.ip_address', 'nm.host_ip', 'nm.nessus_id'));
    	}else {
    	    $select->from(array('nm' => 'nessus'), array('nm.ip_address', 'nm.host_ip'));
    	}
    	$select->join(array('nd' => 'nessus_detail'),
    			'nm.nessus_id=nd.nessus_id',
    			$detail_fields);
    	$select->join(array('ip' => 'ip_address'),
    			'nm.ip_address=ip.ip_address AND ip.project_id=nm.project_id',
    			array('mnemonics'));
    	
    	//get list of ip addresses
    	$oIpAddress = new Application_Model_Ipaddress();
    	$arrIpAddressList = $oIpAddress->loadIpAddressByProjectID($arrCondition['pid']);
    	$arrIpAddressList[] = '';
    	if (count($arrIpAddressList)>0) {
			$select->where('nm.ip_address IN (?)', $arrIpAddressList);
    	}
    	$oCommonModel = new Application_Model_Common();
    	$infoProject = $oCommonModel->loadDetail('project', 'md5(project_id)', $this->_CommonController->decodeKey($arrCondition['pid']));
    	if ($this->_permission_type!=ADMIN && $infoProject->user_create!=$this->_username) {
	        $select->join(array('pip' => 'project_ip_user'),
	        		'pip.project_id=nm.project_id',
	        		array());
	        $select->join(array('pipd' => 'project_ip_user_detail'),
	        		'pip.project_ip_user_id=pipd.project_ip_user_id',
	        		array());
	        $select->where('pipd.username = ?', $this->_username);
	        $select->where('(INET_ATON(trim(ip.ip_address)) >= INET_ATON(trim(pipd.ip_address_from)) AND INET_ATON(trim(ip.ip_address)) <= INET_ATON(trim(pipd.ip_address_to))) OR (ip.mnemonics >= pipd.mnemonics_from AND ip.mnemonics <= pipd.mnemonics_to)');
// 	        $select->where('INET_ATON(trim(nm.ip_address)) >= INET_ATON(trim(pipd.ip_address_from))');
// 	        $select->where('INET_ATON(trim(nm.ip_address)) <= INET_ATON(trim(pipd.ip_address_to))');
        }
    	
    	if ($export) {
    	    $plugin_fields = array('plugin_id', 'title', 'explanation', 'plugin_output', 
    	            				new Zend_Db_Expr('CASE WHEN np.risk_level=3 THEN "High" WHEN np.risk_level=2 THEN "Medium" WHEN np.risk_level=1 THEN "Low" ELSE "Issue" END AS risk_level'), 'solution');
	    	$select->joinLeft(array('np' => 'nessus_plugin'),
	    			'np.nessus_detail_id=nd.nessus_detail_id',
	    			$plugin_fields);
	    	$select->where('np.origin_id =0');
    	}
    	if (isset($arrCondition['pid']) && $arrCondition['pid']!=''):
    		$select->where('md5(nm.project_id) = ?', $this->_CommonController->decodeKey($arrCondition['pid']));
    	endif;
    	if (isset($arrCondition['ip_address']) && $arrCondition['ip_address']!=''):
    		$select->where('nm.ip_address like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['ip_address']) . '%');
    	endif;
    	if (isset($arrCondition['port']) && $arrCondition['port']!=''):
    		$select->where('nd.port like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['port']) . '%');
    	endif;
    	if (isset($arrCondition['state']) && $arrCondition['state']!=''):
    		$select->where('nd.state like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['state']) . '%');
    	endif;
    	if (isset($arrCondition['protocol']) && $arrCondition['protocol']!=''):
    		$select->where('nd.protocol like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['protocol']) . '%');
    	endif;
    	if (isset($arrCondition['service']) && $arrCondition['service']!=''):
    		$select->where('nd.service like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['service']) . '%');
    	endif;
    	if (isset($arrCondition['mnemonics']) && $arrCondition['mnemonics']!=''):
    		$select->where('ip.mnemonics like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['mnemonics']) . '%');
    	endif;
    	if (isset($arrCondition['st']) && $arrCondition['st']!=''):
    		$select->order('ip.mnemonics ' . $arrCondition['st']);
    	else:
    		$select->order(array('nm.nessus_id ASC', 'nd.nessus_detail_id ASC'));
    	endif;
    	$stmt = $select->query();
    	$result = $stmt->fetchAll();
    	$this->_CommonController->writeLog( $select->__toString());
    	return $result;
    } // public function loadList($arrCondition)
    
    /**
     * delete records of ip address
     * @param: list of ip addresses
     * **/
    public function deleteProject($id){
    	try {
    		$where = array('project_id = ?' => $id);
    		$this->delete($where);
    	} catch (Exception $e) {
    		// pass possible exceptions to log file
    		$this->_CommonController->writeLog($e->getMessage());
    	}
    } // public function deleteProject($id)
}
?>
