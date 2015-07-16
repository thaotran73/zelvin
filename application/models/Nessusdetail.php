<?php
class Application_Model_Nessusdetail extends Zend_Db_Table_Abstract{
    protected $_name = 'nessus_detail';
    protected $_CommonController = null;
    
    public function init()
    {
    	/* Initialize action controller here */
    	$this->_CommonController = new CommonController();
    }
        
    /**
     * load list of plugin_detail
     * @param: $arrCondition
     * @return: list of data
     * **/
    public function loadList($listId)
    {
    	$select = $this->select();
    	$select->from('nessus_detail', 'nessus_id');
    	$select->where('nessus_detail_id IN (?)', $listId);
    	$select->distinct(true);
    	$stmt = $select->query();
    	$result = $stmt->fetchAll();
//     	echo $select->__toString() . "\n";
    	return $result;
    } // public function loadList($arrCondition)
    
    /**
     * load a record of nessus
     * @param: id
     * @return: a record
     * **/
    public function loadDetail($id = NULL)
    {
    	$select = $this->select();
    	$select->setIntegrityCheck(false);
    	$select->from(array('nd' => 'nessus_detail'),
    			array('nd.state', 'nd.port', 'nd.protocol', 'nd.service', 'nd.vulnerbility_state')
    	        );
    	$select->join(array('nm' => 'nessus'),
    			'nd.nessus_id=nm.nessus_id',
    			array('nm.ip_address', 'nm.host_ip', 'nm.nessus_id'));
    	$select->join(array('pr' => 'project'),
    			'nm.project_id=pr.project_id',
    			array('pr.project_id', 'pr.project_name', 'pr.start_date', 'pr.user_create', 'pr.description'));
    	$select->join(array('cl' => 'client'),
    			'cl.client_id=pr.client_id',
    			array('cl.client_id', 'cl.client_name'));
    	$select->where('md5(nd.nessus_detail_id) = ?', $this->_CommonController->decodeKey($id));
    	$stmt = $select->query();
    	$result = $stmt->fetchObject();
//     	    	echo $select->__toString() . "\n";
    	return $result;
    } // public function loadDetail($arrCondition)
    
    public function checkDuplicate($arrCondition) {
    	$select = $this->select();
    	$select->setIntegrityCheck(false);
    	$select->from('nessus_detail');
   		$select->where('nessus_id = ?', $arrCondition['nessus_id']);
   		$select->where('port = ?', $arrCondition['port']);
   		$select->where('protocol = ?', $arrCondition['protocol']);
   		$select->where('service = ?', $arrCondition['service']);
    	$stmt = $select->query();
    	$result = $stmt->fetchObject();
//     	    	    	    	echo $select->__toString() . "\n";
//     	    	    	    	exit;
    	return $result;
    }
    
    /**
     * delete records of plugin_detail
     * @param: list of plugin_detail
     * **/
    public function deleteItems($delList){
        $arrNessusID = array();
    	try {
    		$arrDelete = explode(',', $delList);
    		foreach ($arrDelete as $key=>$value) {
    			$where = array('md5(nessus_detail_id) = ?' => $this->_CommonController->decodeKey($value));
    			$intDelete = $this->delete($where);
    			if ($intDelete<=0) {
    			    $arrNessusID[] = $value;
    			}
    		}
    	} catch (Exception $e) {
    		// pass possible exceptions to log file
    		$this->_CommonController->writeLog($e->getMessage());
    	}
    	return $arrNessusID;
    } // public function deleteItems($delList)
}
?>