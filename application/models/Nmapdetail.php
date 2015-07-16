<?php
class Application_Model_Nmapdetail extends Zend_Db_Table_Abstract{
    
    protected $_name = 'nmap_detail';
    protected $_CommonController = null;
    
    public function init()
    {
    	/* Initialize action controller here */
    	$this->_CommonController = new CommonController();
    }
    
    /**
     * load list of ip address
     * @param: $arrCondition
     * @return: list of data
     * **/
    public function loadList($listId)
    {
    	$select = $this->select();
    	$select->from('nmap_detail', 'nmap_id');
    	$select->where('nmap_detail_id IN (?)', $listId);
    	$select->distinct(true);
    	$stmt = $select->query();
    	$result = $stmt->fetchAll();
//     	echo $select->__toString() . "\n";
    	return $result;
    } // public function loadList($arrCondition)
        
    /**
     * delete records of ip address
     * @param: list of ip addresses
     * **/
    public function deleteItems($delList){
        $arrNmapID = array();
    	try {
    		$arrDelete = explode(',', $delList);
    		foreach ($arrDelete as $key=>$value) {
    		    $id = $this->_CommonController->decodeKey($value);
    			$where = array('md5(nmap_detail_id) = ?' => $id);
    			$intDelete = $this->delete($where);
    			if ($intDelete<=0) {
    			    $arrNmapID[] = $value;
    			}
    		}
    	} catch (Exception $e) {
    		// pass possible exceptions to log file
    		$this->_CommonController->writeLog($e->getMessage());
    	}
    	return $arrNmapID;
    } // public function deleteItems($delList)
    
}
?>