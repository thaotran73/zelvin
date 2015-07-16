<?php
// includes
include_once APPLICATION_PATH . '/controllers/CommonController.php';
class NessusController extends Zend_Controller_Action
{
    protected  $_filename = "/nessus_";
    protected  $_ext = ".nessus";
    protected $_username = "";
    protected $_CommonController = null;
    protected $_CommonModel = null;
    protected $_formProject = null;
    protected $_formNessus = null;
    protected $_formPlugin = null;
    protected $_pid = '';
    
    public function preDispatch() {
    	$pid = $this->_getParam("pid");
    	$id = $this->_getParam("id");
    	$intStatus = $this->_CommonController->checkLogon(false);
		if ($intStatus==0 || !$pid){
			$this->_redirect('/index/logout');
		}else if ($intStatus==1) {
			$this->_redirect('/user/changepass');
		}
		
		$this->_formProject    = new Application_Form_Project();
		$this->_formNessus    = new Application_Form_Nessus();
		$this->_formPlugin    = new Application_Form_Nessusplugin();
		//get client_id
// 		$pid = $this->_getParam("pid");
		if ($pid) {
			$oProject = $this->_CommonModel->loadClientFromProject($pid);
			$this->view->oProject = $oProject;
			$oUtils = new Utils();
			//load and set data to client options
			$fieldList = array('client_id', 'client_name');
			$result = $this->_CommonModel->loadList('client', $fieldList, null, null, 'client_id ASC');
			$arrData = $oUtils->convertData2Option($result, array('client_id', 'client_name'));
			$this->_formProject->client_code->addMultiOptions($arrData);
			//load project detail
			$oProject = new Application_Model_Project();
			$oDetail = $oProject->loadDetail($pid);
			$start_date = "";
			$client_id = isset($oDetail->client_id)?$oDetail->client_id:'';
			$client_name = isset($oDetail->client_name)?$oDetail->client_name:'';
			$description = isset($oDetail->description)?$oDetail->description:'';
			$project_id = isset($oDetail->project_id)?$oDetail->project_id:'';
			$project_name = isset($oDetail->project_name)?$oDetail->project_name:'';
			$user_create = isset($oDetail->user_create)?$oDetail->user_create:'';
			if (isset($oDetail->start_date)) {
			    $date = date_create($oDetail->start_date);
			    $start_date = $date->format('m/d/Y');
			}
			if ($this->getRequest()->getActionName()=='index') {
				$this->_formProject->getElement('client_code')->setValue(array($client_id, $client_name));
				$this->_formProject->getElement('project_name')->setValue($project_name);
				$this->_formProject->getElement('description')->setValue($description);
				$this->_formProject->getElement('start_date')->setValue($start_date);
				$this->_formProject->getElement('old_client_code')->setValue($this->_CommonController->encodeKey($client_id));
// 				$this->_formProject->getElement('old_start_date')->setValue($start_date);
				$this->_formProject->getElement('old_project_name')->setValue($project_name);
			}
		    $this->_formProject->getElement('client_name')->setValue($client_name);
		    $this->_formProject->getElement('project_name')->setValue($project_name);
		    $this->_formProject->getElement('description_view')->setValue($description);
		    $this->_formProject->getElement('start_date_view')->setValue($start_date);
			$this->_formProject->getElement('user_create')->setValue($user_create);
			$this->_formProject->getElement('pid')->setValue($pid);
			if ($this->getRequest()->getActionName()=='plugin' || $this->getRequest()->getActionName()=='detail'){
			    $oNessusDetail = new Application_Model_Nessusdetail();
			    $oNessusDetailData = $oNessusDetail->loadDetail($id);
			    $this->_formNessus->getElement('ip_address')->setValue($oNessusDetailData->ip_address);
			    $this->_formNessus->getElement('port')->setValue($oNessusDetailData->port);
			    $this->_formNessus->getElement('state')->setValue($oNessusDetailData->state);
			    $this->_formNessus->getElement('protocol')->setValue($oNessusDetailData->protocol);
			    $this->_formNessus->getElement('service')->setValue($oNessusDetailData->service);
			    $this->_formNessus->getElement('vulnerbility_state')->setValue($oNessusDetailData->vulnerbility_state);
			}
		}
    }
    
    public function init()
    {
        /* Initialize action controller here */
        $this->_pid = $this->_getParam("pid", null);
        /* Initialize action controller here */
        $this->_CommonModel = new Application_Model_Common();
        $this->_CommonController = new CommonController();
         
        //get user information when user is logon
        $infoUser = $this->_CommonModel->loadInfoUser();
        if ($infoUser!=null) {
        	$this->_username = $infoUser->username;
        }
        $oProjectFunUser = $this->_CommonController->getFunctionUser(PROJECT);
        $oNessusFunUser = $this->_CommonController->getFunctionUser(NESSUS);
        Zend_Registry::set('PROJECT', $oProjectFunUser);
        Zend_Registry::set('NESSUS', $oNessusFunUser);
    }

	public function indexAction()
    {
        // action body
        $request = $this->getRequest();
        $form    = new Application_Form_Search();
        $buttonsearch = $request->getPost('buttonsearch', null);
        $btnDelete = $request->getPost('act', null);
        $st = $this->_getParam("st", '');
        try {
	    	// action body
            if ($buttonsearch=="" && $btnDelete=="") {
            	if ($this->getRequest()->isPost()) {
            		if ($this->_formProject->isValid($request->getPost())) {
            			if ($this->_CommonController->saveProject($this->getRequest(), $this->_formProject)==false){
            				$this->_redirect('/project/index');
            				return false;
            			}
            		}
            	}
            }
            
	    	$oNessus = new Application_Model_Nessus();
	    	$oNessusDetail = new Application_Model_Nessusdetail();
        	$hidDel = Zend_Filter::filterStatic($request->getPost('hidDel', null), 'StripTags');
		    //delete items are selected
		    if ($hidDel!=""){
		        //delete items of nessus_detail table
				$arrNessusID = $oNessusDetail->deleteItems($hidDel);
				if (count($arrNessusID)>0) {
					$nessusIDList = implode(',', $arrNessusID);
					$oNessus->deleteItems($nessusIDList);
				}
		    }
		    //get post values
		    $pid = $this->_getParam("pid", 1);
		    //clear old session
		    $bNewSearch = $this->_getParam("ns", null);
		    if ($bNewSearch!=null) {
		    	$this->_CommonController->detroySession('psearch');
		    }
		    //store search param in session for pagination
		    $search = new Zend_Session_Namespace('psearch');
		    //get search param
		    $buttonsearch = Zend_Filter::filterStatic($request->getPost('buttonsearch', null), 'StripTags');
		    if ($buttonsearch=='Search'){
			    $ip_address_search = Zend_Filter::filterStatic($request->getPost('ip_address_search', null), 'StripTags');
			    $port_search = Zend_Filter::filterStatic($request->getPost('port_search', null), 'StripTags');
			    $state_search = Zend_Filter::filterStatic($request->getPost('state_search', null), 'StripTags');
			    $protocol_search = Zend_Filter::filterStatic($request->getPost('protocol_search', null), 'StripTags');
			    $service_search = Zend_Filter::filterStatic($request->getPost('service_search', null), 'StripTags');
			    $mnemonics_search = Zend_Filter::filterStatic($request->getPost('mnemonics_search', null), 'StripTags');
			    $arrCondition = array('ip_address' => $ip_address_search, 'port' => $port_search, 'state' => $state_search, 'protocol' => $protocol_search, 'service' => $service_search, 'mnemonics' => $mnemonics_search);
			    $search->psearch = $arrCondition;
		    }else{
	        	$arrCondition = $search->psearch;
			}
			$arrCondition['pid'] = $pid;
			//set search values to search box
			$form->getElement('ip_address_search')->setValue(isset($arrCondition['ip_address'])?$arrCondition['ip_address']:'');
			$form->getElement('port_search')->setValue(isset($arrCondition['port'])?$arrCondition['port']:'');
			$form->getElement('state_search')->setValue(isset($arrCondition['state'])?$arrCondition['state']:'');
			$form->getElement('protocol_search')->setValue(isset($arrCondition['protocol'])?$arrCondition['protocol']:'');
			$form->getElement('service_search')->setValue(isset($arrCondition['service'])?$arrCondition['service']:'');
			$form->getElement('mnemonics_search')->setValue(isset($arrCondition['mnemonics'])?$arrCondition['mnemonics']:'');
			if ($st!='') {
				$arrCondition['st'] = $st==1 ? 'ASC' : 'DESC';
			}
	        $currentPage = $this->_getParam("page", 1);
	        //count per page
		    $countperpage = $this->_getParam("cpage", COUNTPERPAGE);
		    $this->view->countperpage = $countperpage;
	        // Object of Zend_Paginator
	        $result = $oNessus->loadList($arrCondition);
	        $paginator = Zend_Paginator::factory($result);
	        // set the number of counts in a page
	        $paginator->setItemCountPerPage($countperpage);
	        // set the current page number
	        $paginator->setCurrentPageNumber($currentPage);
	        // assign to view
	        $this->view->paginator = $paginator;
        } catch (Exception $e) {
        	// pass possible exceptions to log file
        	$this->_CommonController->writeLog($e->getMessage());
        }
        $this->view->page = $currentPage;
        $this->view->st = ($st=='' || $st==2) ? 1 : 2;
        $this->view->form = $form;
        $this->view->formProject = $this->_formProject;
    }
    
    public function pluginAction()
    {
    	// action body
    	$request = $this->getRequest();
    	$form    = new Application_Form_Nessus();
    	$buttonsearch = $request->getPost('buttonsearch', null);
    	$btnDelete = $request->getPost('btnDelete', null);
    	try {
    		// action body   
    		$oNessusPlugin = new Application_Model_Nessusplugin();
    		$hidDel = Zend_Filter::filterStatic($request->getPost('hidDel', null), 'StripTags');
    		//delete items are selected
    		if ($hidDel!=""){
    			//delete items of nessus_detail table
    			$oNessusPlugin->deleteItems($hidDel);
    		}
    		//get post values
    		$id = $this->_getParam("id", 1);
    		$st = $this->_getParam("st", '');
    		//clear old session
    		$bNewSearch = $this->_getParam("ns", null);
    		if ($bNewSearch!=null) {
    			$this->_CommonController->detroySession('psearch');
    		}
    		//store search param in session for pagination
    		$search = new Zend_Session_Namespace('psearch');
    		//get search param
    		$buttonsearch = Zend_Filter::filterStatic($request->getPost('buttonsearch', null), 'StripTags');
    		if ($buttonsearch=='Search'){
    		    $plugin_id_search = Zend_Filter::filterStatic($request->getPost('plugin_id_search', null), 'StripTags');
    			$title_search = Zend_Filter::filterStatic($request->getPost('title_search', null), 'StripTags');
    			$explanation_search = Zend_Filter::filterStatic($request->getPost('explanation_search', null), 'StripTags');
    			$plugin_output_search = Zend_Filter::filterStatic($request->getPost('plugin_output_search', null), 'StripTags');
    			$risk_level_search = Zend_Filter::filterStatic($request->getPost('risk_level_search', null), 'StripTags');
    			$solution_search = Zend_Filter::filterStatic($request->getPost('solution_search', null), 'StripTags');
    			$arrCondition = array('plugin_id' => $plugin_id_search, 'title' => $title_search, 'explanation' => $explanation_search, 'plugin_output' => $plugin_output_search, 'risk_level' => $risk_level_search, 'solution' => $solution_search);
    			$search->psearch = $arrCondition;
    		}else{
    			$arrCondition = $search->psearch;
    		}
    		$arrCondition['id'] = $id;
    		if ($st!='') {
    		    $arrCondition['st'] = $st==1 ? 'ASC' : 'DESC';
    		}
    		//set search values to search box
    		$this->_formPlugin->getElement('plugin_id_search')->setValue(isset($arrCondition['plugin_id'])?$arrCondition['plugin_id']:'');
    		$this->_formPlugin->getElement('title_search')->setValue(isset($arrCondition['title'])?$arrCondition['title']:'');
    		$this->_formPlugin->getElement('explanation_search')->setValue(isset($arrCondition['explanation'])?$arrCondition['explanation']:'');
    		$this->_formPlugin->getElement('plugin_output_search')->setValue(isset($arrCondition['plugin_output'])?$arrCondition['plugin_output']:'');
    		$this->_formPlugin->getElement('risk_level_search')->setValue(isset($arrCondition['risk_level'])?$arrCondition['risk_level']:'');
    		$this->_formPlugin->getElement('solution_search')->setValue(isset($arrCondition['solution'])?$arrCondition['solution']:'');
    		$currentPage = $this->_getParam("page", 1);
    		//count per page
		    $countperpage = $this->_getParam("cpage", COUNTPERPAGE);
		    $this->view->countperpage = $countperpage;
    		// Object of Zend_Paginator
    		$result = $oNessusPlugin->loadList($arrCondition);
    		$paginator = Zend_Paginator::factory($result);
    		// set the number of counts in a page
    		$paginator->setItemCountPerPage($countperpage);
    		// set the current page number
    		$paginator->setCurrentPageNumber($currentPage);
    		// assign to view
    		$this->view->paginator = $paginator;
    	} catch (Exception $e) {
    		// pass possible exceptions to log file
    		$this->_CommonController->writeLog($e->getMessage());
    	}
    	$this->_formPlugin->getElement('pid')->setValue($this->_pid);
    	$this->_formPlugin->getElement('id')->setValue($id);
    	$this->view->id = $id;
    	$this->view->st = ($st=='' || $st==2) ? 1 : 2;
    	$this->view->page = $currentPage;
		$this->view->form = $form;
		$this->view->formProject = $this->_formProject;
		$this->view->formNessus = $this->_formNessus;
		$this->view->formPlugin = $this->_formPlugin;
    }
    
    public function detailAction()
    {
    	//detroy search session condition
    	$this->_CommonController->detroySession('psearch');
    	// action body
    	$request = $this->getRequest();
    	$form    = new Application_Form_Nessusplugin();
    	$formNessus    = new Application_Form_Nessus();
    	$oNessusPlugin = new Application_Model_Nessusplugin();
    	$id = $this->_getParam("id", null);
    	$plid = $this->_getParam("plid", null);
    	try{
    		if ($this->getRequest()->isPost()) {
    			if ($form->isValid($request->getPost())) {
    				//get post values
    				$submitbutton = Zend_Filter::filterStatic($request->getPost('submitbutton', null), 'StripTags');
    				//get search param
    				if ($submitbutton=='Save'){
    					$plugin_id = Zend_Filter::filterStatic($request->getPost('plugin_id', null), 'StripTags');
    					$title = Zend_Filter::filterStatic($request->getPost('title', null), 'StripTags');
    					$explanation = Zend_Filter::filterStatic($request->getPost('explanation', null), 'StripTags');
    					$plugin_output = Zend_Filter::filterStatic($request->getPost('plugin_output', null), 'StripTags');
    					$risk_level = Zend_Filter::filterStatic($request->getPost('risk_level', null), 'StripTags');
    					$solution = Zend_Filter::filterStatic($request->getPost('solution', null), 'StripTags');
    					$data = array('plugin_id' => $plugin_id, 'title' => $title, 'explanation' => $explanation, 'plugin_output' => $plugin_output, 'risk_level' => $risk_level, 'solution' => $solution);
    					//check insert or update
    					//insert
    					if ($plid=="") {
    					    $oNessusDetail = $this->_CommonModel->loadDetail('nessus_detail', 'md5(nessus_detail_id)', $this->_CommonController->decodeKey($id));
//     						$oNessusPluginDetail = $this->_CommonModel->loadDetail('nessus_plugin', 'md5(nessus_plugin_id)', $this->_CommonController->decodeKey($plid));
//     						$plid = isset($oNessusPluginDetail->nessus_plugin_id)?$oNessusPluginDetail->nessus_plugin_id:'';
    						$nessus_detail_id = isset($oNessusDetail->nessus_detail_id)?$oNessusDetail->nessus_detail_id:'';
    						$data['nessus_detail_id'] = $nessus_detail_id;
    						$data['origin_id'] = -1;
    						//insert data to ip_address table
    						$oNessusPlugin->insert($data);
    						$form->reset();
    						//update
    					} else {
    						$where['md5(nessus_plugin_id) = ?'] = $this->_CommonController->decodeKey($plid);
    						$oNessusPlugin->update($data, $where);
//     						$this->_redirect('/nessus/plugin/pid/'.$this->_pid.'/id/'.$id.'/plid/'.$plid);
//     						return false;
    					}
    					$this->_redirect('/nessus/plugin/pid/'.$this->_pid.'/id/'.$id.'/plid/'.$plid);
    					return false;
    				}
    			}
    		} else {
    			if ($plid!='') {
//     				$this->_CommonController->checkPermission('NESSUS', 'f_update', '/nessus/index/pid/'.$this->_pid);
    				$oDetail = $oNessusPlugin->loadDetail($plid);
    				$form->getElement('plugin_id')->setValue($oDetail->plugin_id);
    				$form->getElement('title')->setValue($oDetail->title);
    				$form->getElement('explanation')->setValue($oDetail->explanation);
    				$form->getElement('plugin_output')->setValue($oDetail->plugin_output);
    				$form->getElement('solution')->setValue($oDetail->solution);
    				$form->getElement('risk_level')->setValue($oDetail->risk_level);
    				$form->getElement('plid')->setValue($plid);
    				$form->getElement('pid')->setValue($this->_pid);
    				$form->getElement('id')->setValue($id);
    			}else {
    				$this->_CommonController->checkPermission('NESSUS', 'f_update', '/nessus/index/pid/'.$this->_pid);
    			}
    		}
    	} catch (Exception $e) {
    		// pass possible exceptions to log file
    		$this->_CommonController->writeLog($e->getMessage());
    	}
    	$form->getElement('pid')->setValue($this->_pid);
    	$form->getElement('id')->setValue($id);
    	$this->view->id = $id;
    	$this->view->form = $form;
    	$this->view->formProject = $this->_formProject;
    	$this->view->formNessus = $this->_formNessus;
    }

    /**
     * import action
     * **/
    public function importAction()
    {
        //check permission
        $this->_CommonController->checkPermission('NESSUS', 'f_import', '/nessus/index/pid/'.$this->_pid);
    	// action body
    	$request = $this->getRequest();
    	$form    = new Application_Form_Importfile();
    	try{
    		if ($this->getRequest()->isPost()) {
    			if ($form->isValid($request->getPost())) {
    				$ext = end(explode('.', $form->importFile->getFileName()));
//     				if ($ext=='nessus'){
	    				// where do you want to save the file?
	    				$desFolder = $form->importFile->getDestination();
	    				$pathFileName = $desFolder . $this->_filename . date('YmdHis') . '.' . $ext;//$this->_ext;
// 	    				echo $pathFileName;
	    				// add filter for renaming the uploaded picture
	    				$form->importFile->addFilter('Rename',
	    						array('target' => $pathFileName,
	    								'overwrite' => true));
	    				$this->_CommonController->writeLog(date('H:i:s'));
	    				if ($form->importFile->receive()) {
	    					if (file_exists($pathFileName)) {
	    					    $this->_CommonController->writeLog(date('H:i:s'));
	    						if ($this->parseImportNessusToDb($request, $pathFileName)==false) {
	    						    $form->importFile->addError(UNABLE_LOAD_XML_MSG);
	    						}
	    					}
	    					$this->_CommonController->writeLog(date('H:i:s'));
	    					$form->reset();
	    					$this->_redirect('/nessus/index/pid/'.$this->_pid);
	    					return false;
	    				}
//     				} else {
//     				    $form->importFile->addError(str_replace('xxx', end(explode('\\', $form->importFile->getFileName())), EXTENSION_FALSE_MSG));
//     				}
    			} else {
    				//get error of importFile
    				$element = $form->getElement('importFile');
    				$msg     = $element->getMessages();
    			}
    		}
    	} catch (Exception $e) {
    		// pass possible exceptions to log file
        	$this->_CommonController->writeLog($e->getMessage());
    	}
    	$this->view->form = $form;
    	$this->view->formProject = $this->_formProject;
    }
    
    /**
     * import data from nessus file to db
     * @param: $pathFileName - path of filename
     **/
    public function parseImportNessusToDb($request, $pathFileName){
    	$nessus = new Application_Model_Nessus();
    	$nessus_detail = new Application_Model_Nessusdetail();
    	$nessus_plugin = new Application_Model_Nessusplugin();
    	try{
    		if (file_exists($pathFileName)) {
    		    $importFile = $this->_CommonController->detectImportFileName($pathFileName);
    		    if (file_exists($importFile)) {
    		        try {
    		            $this->_CommonController->writeLog(date('H:i:s'));
    		            $xml = @simplexml_load_file($importFile);
    		            if ($xml===false) {
    		                //throw new Zend_Exception('Unable to parse XML from file');
    		                $this->_CommonController->writeLog(UNABLE_LOAD_XML_MSG);
    		                return false;
    		            }else {
    		                $this->_CommonController->writeLog(date('H:i:s'));
    		                $oProjectDetail = $this->_CommonModel->loadDetail('project', 'md5(project_id)', $this->_CommonController->decodeKey($this->_pid));
//     		                print_r($oProjectDetail);exit;
    		                $pid = isset($oProjectDetail->project_id)?$oProjectDetail->project_id:'';
    		                if ($pid!=''){
    		                    //delete old ipaddress of this project
    		                    $overwrite = $request->getPost('chk_overwrite', null);
    		                    if ($overwrite==1) {
//     		                    	$nessus->delete(array('project_id' => $pid));
									$nessus->deleteProject($pid);
    		                    }
    		                    $i=0;
		    		            foreach ($xml->Report->ReportHost as $ReportHost) {
		    		            	//     		        print_r($ReportHost);
		    		            	$ip_address =  $ReportHost['name'];
		    		            	$data = array();
		    		            	$data['project_id'] = $pid;
		    		            	$data['ip_address'] = $ip_address;
		    		            	//get host-ip
		    		            	foreach ($ReportHost->HostProperties->tag as $tagItem) {
		    		            		if ($tagItem['name']=='host-ip') {
		    		            			$data['host_ip'] = $tagItem[0];
		    		            		}
		    		            	}
		    		            	$data['user_create'] = $this->_username;
		    		            	$data['user_mod'] = $this->_username;
		    		            	$data['create_date'] = date('YmdHis');
		    		            	$data['mod_date'] = date('YmdHis');
		    		            	//insert data to nessus table and get nessus_id to insert to nessus_detail table
		    		            	try {
		    		            		$nessus_id = $nessus->insert($data);
// 		    		            		$nessus_detail_id_tmp = 0;
		    		            		foreach ($ReportHost->ReportItem as $ReportItem) {
		    		            			//print_r($ReportItem);
		    		            			$plugin_id = $ReportItem['pluginID'];
		    		            			$data = array();
		    		            			$data['nessus_id'] = $nessus_id;
		    		            			$data['port'] = $ReportItem['port'];
		    		            			$data['protocol'] = $ReportItem['protocol'];
		    		            			$data['service'] = $ReportItem['svc_name'];
		    		            			$oNessusDetail = $nessus_detail->checkDuplicate($data);
		    		            			if (!$oNessusDetail) {
		    		            				try {
		    		            					//insert data to nessus_detail table
		    		            					$nessus_detail_id = $nessus_detail->insert($data);
// 		    		            					$nessus_detail_id_tmp = $nessus_detail_id;
		    		            				}catch (Exception $e) {
		    		            					$this->_CommonController->writeLog($e->getMessage());
		    		            				}
		    		            			} else {
		    		            			    //find nessus_detail_id
		    		            			    $nessus_detail_id = $oNessusDetail->nessus_detail_id;
// 		    		            			    $nessus_detail_id = $nessus_detail_id_tmp;
		    		            			}
		    		            			
		    		            			//check and insert data to nessus plugin
// 		    		            			if ($plugin_id!=0) {
		    		            				$data = array();
		    		            				$data['nessus_detail_id'] = $nessus_detail_id;
		    		            				$data['plugin_id'] = $plugin_id;
		    		            				$data['title'] = $ReportItem['pluginName'];
		    		            				$data['risk_level'] = $ReportItem['severity'];
		    		            				$data['explanation'] = $ReportItem->description;
		    		            				$data['plugin_output'] = $ReportItem->plugin_output!=null?$ReportItem->plugin_output:"";
		    		            				$data['solution'] = $ReportItem->solution;
// 		    		            				if ($this->_CommonController->checkDuplicate('nessus_plugin', 'plugin_id', $plugin_id, 'nessus_detail_id', $nessus_detail_id)) {
		    		            					try {
		    		            					    if ($ip_address == '192.168.180.105' && $plugin_id=='11936'){
		    		            					        $sData = 'INSERT INTO `nessus_plugin` (`nessus_detail_id`, `plugin_id`, `title`, `risk_level`, `explanation`, `plugin_output`, `solution`) VALUES ("' . implode('","', $data) . '")';
		    		            					        $this->_CommonController->writeLog('Plugin ID aaaaaaa ===== ' . $sData);
		    		            					    }
		    		            						$nessus_plugin_id = $nessus_plugin->insert($data);
		    		            						//create a backup record
		    		            						$data['origin_id'] = $nessus_plugin_id;
		    		            						if ($ip_address == '192.168.180.105' && $plugin_id=='11936'){
		    		            							$sData = 'INSERT INTO `nessus_plugin` (`nessus_detail_id`, `plugin_id`, `title`, `risk_level`, `explanation`, `plugin_output`, `solution`, `origin_id`) VALUES ("' . implode('","', $data) . '")';
		    		            							$this->_CommonController->writeLog($i . ' Plugin ID aaaaaaa ===== ' . $sData);
		    		            						}
		    		            						$nessus_plugin->insert($data);
		    		            						$i++;
		    		            					}catch (Exception $e) {
		    		            						$this->_CommonController->writeLog('Plugin ID ' . $plugin_id . ': ' . $e->getMessage());
		    		            					}
// 		    		            				}
// 		    		            			}
		    		            		}
		    		            	}catch (Exception $e) {
		    		            		$this->_CommonController->writeLog($ip_address . ' ' . $e->getMessage());
		    		            	}
		    		            }
    		            	}
    		            	$this->_CommonController->writeLog(date('H:i:s'));
    		            }
    		        }catch (Exception $e) {
						$this->_CommonController->writeLog($e->getMessage());
	    		    }
    		    }
    		    $this->_CommonController->checkToDeleteImportFile($pathFileName, $importFile);
    		}
    	}catch (Exception $e) {
    		$this->_CommonController->writeLog($e->getMessage());
    	}
    	return true;
    }
    
	/**
     * export page
     * **/
    public function exportAction()
    {
        //check permission
        $this->_CommonController->checkPermission('NESSUS', 'f_export', '/nessus/index/pid/'.$this->_pid);
        try {
            $this->_CommonController->writeLog('export:' . date('H:i:s'));
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            //store search param in session for pagination
            $search = new Zend_Session_Namespace('psearch');
            $arrCondition = $search->psearch;
            $arrCondition['pid'] = $this->_pid;
        	$oNessus = new Application_Model_Nessus();
        	$data[] = array('IP Address', 'Host IP', 'Port', 'State', 'Protocol', 'Service', 'Mnemonics', 'Plugin ID', 'Title', 'Explanation', 'Plugin Output', 'Risk Level', 'Solution');
        	$result = $oNessus->loadList($arrCondition, true);
        	$data = array_merge($data, $result);
        	$this->_CommonController->writeLog('export:' . date('H:i:s'));
			$this->_CommonController->exportExcel($data, 'Nessus', 'nessus');
			$this->_CommonController->writeLog('export:' . date('H:i:s'));
        } catch (Exception $e) {
        	// pass possible exceptions to log file
        	$this->_CommonController->writeLog($e->getMessage());
        }
    }

}

