<?php
class Application_Model_Projectipuser extends Zend_Db_Table_Abstract{
    
    protected $_name = 'project_ip_user';
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
     * load list of project_ip_user
     * @param: $arrCondition
     * @return: list of data
     * **/
    public function loadList($arrCondition = NULL)
    {
        $select = $this->select();
        $select->distinct(true);
        $select->setIntegrityCheck(false);
        $select->from(array('prip' => 'project_ip_user'),
        		array('prip.project_ip_user_id', 'prip.ip_address_list', 'prip.mnemonics_list', 'prip.username_list'));
        $select->join(array('pr' => 'project'),
        		'pr.project_id=prip.project_id',
        		array());
        $select->where('md5(prip.project_id) = ?', $this->_CommonController->decodeKey($arrCondition['pid']));
        
        if ($this->_permission_type!=ADMIN) {
            $select->where('pr.user_create = ?', $this->_username);
        }
        
        if (isset($arrCondition['ip_address_list']) && $arrCondition['ip_address_list']!=''):
        	$select->where('trim(prip.ip_address_list) like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['ip_address_list']) . '%');
        endif;
        
        if (isset($arrCondition['mnemonics_list']) && $arrCondition['mnemonics_list']!=''):
        	$select->where('trim(prip.mnemonics_list) like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['mnemonics_list']) . '%');
        endif;
        
        if (isset($arrCondition['username_assigned']) && $arrCondition['username_assigned']!=''):
    		$select->where('pripd.username like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['username_assigned']) . '%');
        endif;
        
    	$select->order('prip.project_ip_user_id ASC');
        $stmt = $select->query();
        $result = $stmt->fetchAll();               
//         echo $select->__toString() . "\n";
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
    	$select->from('project_ip_user', array('project_ip_user_id', 'project_id', 'username_list', 'ip_address_list', 'mnemonics_list', 'range_type'));
    	if ($id!=''):
    		$select->where('md5(project_ip_user_id) = ?', $this->_CommonController->decodeKey($id));
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
            	$where = array('md5(project_ip_user_id) = ?' => $this->_CommonController->decodeKey($value));
            	$this->delete($where);
            }
        } catch (Exception $e) {
            // pass possible exceptions to log file
    		$this->_CommonController->writeLog($e->getMessage());
        }
    } // public function deleteItems($delList)
}
?>