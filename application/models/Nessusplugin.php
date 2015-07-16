<?php
class Application_Model_Nessusplugin extends Zend_Db_Table_Abstract{
    protected $_name = 'nessus_plugin';
    protected $_CommonController = null;
    
    public function init()
    {
    	/* Initialize action controller here */
    	$this->_CommonController = new CommonController();
    }
        
    /**
     * load list of plugin
     * @param: $arrCondition
     * @return: list of data
     * **/
    public function loadList($arrCondition)
    {
    	$select = $this->select();
//     	$select->distinct(true);
    	$select->from(array('np' => 'nessus_plugin'), array('np.nessus_plugin_id', 'np.nessus_detail_id', 'np.plugin_id', 'np.title', 'np.explanation', 'np.plugin_output', 'np.risk_level', 'np.solution', 'np.origin_id'));
    	if (isset($arrCondition['id']) && $arrCondition['id']!=''):
    		$select->where('md5(np.nessus_detail_id) = ?', $this->_CommonController->decodeKey($arrCondition['id']));
    	endif;
    	if (isset($arrCondition['plugin_id']) && $arrCondition['plugin_id']!=''):
    		$select->where('np.plugin_id = ?', $this->_CommonController->replaceSpecialChars($arrCondition['plugin_id']));
    	endif;
    	if (isset($arrCondition['title']) && $arrCondition['title']!=''):
    		$select->where('np.title like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['title']) . '%');
    	endif;
    	if (isset($arrCondition['explanation']) && $arrCondition['explanation']!=''):
    		$select->where('np.explanation like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['explanation']) . '%');
    	endif;
    	if (isset($arrCondition['plugin_output']) && $arrCondition['plugin_output']!=''):
    		$select->where('np.plugin_output like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['plugin_output']) . '%');
    	endif;
    	if (isset($arrCondition['risk_level']) && $arrCondition['risk_level']!=''):
    		$select->where('np.risk_level like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['risk_level']) . '%');
    	endif;
    	if (isset($arrCondition['solution']) && $arrCondition['solution']!=''):
    		$select->where('np.solution like ?', '%' . $this->_CommonController->replaceSpecialChars($arrCondition['solution']) . '%');
    	endif;
    	$select->where('np.plugin_id != 0');
    	$select->where('np.origin_id != 0');
    	if (isset($arrCondition['st']) && $arrCondition['st']!=''):
    		$select->order('np.risk_level ' . $arrCondition['st']);
    	else:
    		$select->order('np.nessus_plugin_id ASC');
    	endif;
    	$stmt = $select->query();
    	$result = $stmt->fetchAll();
    	$this->_CommonController->writeLog( $select->__toString());
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
    	$select->from(array('np' => 'nessus_plugin'), array('np.nessus_plugin_id', 'np.nessus_detail_id', 'np.plugin_id', 'np.title', 'np.explanation', 'np.plugin_output', 'np.risk_level', 'np.solution', 'np.origin_id'));
    	$select->where('md5(np.nessus_plugin_id) = ?', $this->_CommonController->decodeKey($id));
    	$stmt = $select->query();
    	$result = $stmt->fetchObject();
//     	    	echo $select->__toString() . "\n";
    	return $result;
    } // public function loadDetail($arrCondition)
    
    /**
     * delete records of plugin
     * @param: list of plugin
     * **/
    public function deleteItems($delList){
    	try {
    		$arrDelete = explode(',', $delList);
    		foreach ($arrDelete as $key=>$value) {
    			$where = array('md5(nessus_plugin_id) = ?' => $this->_CommonController->decodeKey($value));
    			$this->delete($where);
    		}
    	} catch (Exception $e) {
    		// pass possible exceptions to log file
    		$this->_CommonController->writeLog($e->getMessage());
    	}
    } // public function deleteItems($delList)
}
?>