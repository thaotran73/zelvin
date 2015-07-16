<?php
// includes
include_once APPLICATION_PATH . '/controllers/CommonController.php';
class NmapController extends Zend_Controller_Action
{

    protected  $_filename = "/nmap_";
    protected $_username = "";
    protected $_CommonController = null;
    protected $_CommonModel = null;
    protected $_formProject = null;
    protected $_pid = '';
    
    public function preDispatch() {
    	
        $pid = $this->_getParam("pid");
    	$intStatus = $this->_CommonController->checkLogon(false);
		if ($intStatus==0 || !$pid){
			$this->_redirect('/index/logout');
		}else if ($intStatus==1) {
			$this->_redirect('/user/changepass');
		}
		
		$this->_formProject    = new Application_Form_Project();
		//get client_id
		$pid = $this->_getParam("pid");
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
	    $oNmapFunUser = $this->_CommonController->getFunctionUser(NMAP);
	    Zend_Registry::set('PROJECT', $oProjectFunUser);
	    Zend_Registry::set('NMAP', $oNmapFunUser);
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
            
	    	$oNmap = new Application_Model_Nmap();
	    	$oNmapDetail = new Application_Model_Nmapdetail();
        	$hidDel = Zend_Filter::filterStatic($request->getPost('hidDel', null), 'StripTags');
		    //delete items are selected
		    if ($hidDel!=""){
		        //delete items of nmap_detail table
				$arrNmapID = $oNmapDetail->deleteItems($hidDel);
				if (count($arrNmapID)>0) {
				    $nmapIDList = implode(',', $arrNmapID);
				    $oNmap->deleteItems($nmapIDList);
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
	        $result = $oNmap->loadList($arrCondition);
	        $paginator = Zend_Paginator::factory($result);
	        // set the number of counts in a page
	        $paginator->setItemCountPerPage($countperpage);
	        // set the current page number
	        $paginator->setCurrentPageNumber($currentPage);
	        // assign to view
	        $this->view->paginator = $paginator;
        } catch (Exception $e) {
        	// pass possible exceptions to the view
        	$this->_CommonController->writeLog($e->getMessage());
        }
        $this->view->page = $currentPage;
        $this->view->st = ($st=='' || $st==2) ? 1 : 2;
        $this->view->form = $form;
        $this->view->formProject = $this->_formProject;
    }

    /**
     * import action
     * **/
    public function importAction()
    {
        //check permission
        $this->_CommonController->checkPermission('NMAP', 'f_import', '/nmap/index/pid/'.$this->_pid);
        // action body
        $request = $this->getRequest();
        $form    = new Application_Form_Importfile();
        try{
            if ($this->getRequest()->isPost()) {
            	if ($form->isValid($request->getPost())) {
            	    $ext = end(explode('.', $form->importFile->getFileName()));
//             	    if ($ext=='nmap'){
	            	    // where do you want to save the file?
	            	    $desFolder = $form->importFile->getDestination();
	            	    $pathFileName = $desFolder . $this->_filename . date('YmdHis') . '.' . $ext;//$this->_ext;
	            	    // add filter for renaming the uploaded file
	            	    $form->importFile->addFilter('Rename',
	            	    		array('target' => $pathFileName,
	            	    				'overwrite' => true));
	            		if ($form->importFile->receive()) {
	            		    if (file_exists($pathFileName)) {
	            		        $this->parseImportNmapToDb($request, $pathFileName);
	            		    }
	            		    $form->reset();
	            		    $this->_redirect('/nmap/index/pid/'.$this->_pid);
	            		    return false;
	            		}
//             	    }else{
//             	        $form->importFile->addError(str_replace('xxx', end(explode('\\', $form->importFile->getFileName())), EXTENSION_FALSE_MSG));
//             	    }
            	} else {
            	    //get error of importFile
            		$element = $form->getElement('importFile');
            		$msg     = $element->getMessages();
            	}
            }
        } catch (Exception $e) {
        	// pass possible exceptions to the view
        	$this->_CommonController->writeLog($e->getMessage());
        }
        $this->view->form = $form;
        $this->view->formProject = $this->_formProject;
    }
    
    /**
     * import data from nmap file to db
     * @param: $pathFileName - path of filename
     **/
    public function parseImportNmapToDb($request, $pathFileName){
        $nmap = new Application_Model_Nmap();
        $nmap_detail = new Application_Model_Nmapdetail();
        try{
	        if (file_exists($pathFileName)) {
				$importFile = $this->_CommonController->detectImportFileName($pathFileName);
				if (file_exists($importFile)) {
				    $oProjectDetail = $this->_CommonModel->loadDetail('project', 'md5(project_id)', $this->_CommonController->decodeKey($this->_pid));
				    $pid = isset($oProjectDetail->project_id)?$oProjectDetail->project_id:'';
				    if ($pid!=''){
				        //delete old ipaddress of this project
				        $overwrite = $request->getPost('chk_overwrite', null);
				        if ($overwrite==1) {
// 				        	$nmap->delete(array('project_id' => $pid));
				            $nmap->deleteProject($pid);
				        }
			            $lines = file($importFile);
			            foreach($lines as $line_num => $line){
// 			            	if (strpos($line, 'Host:') !== false && strpos($line, 'Status') == false){
			                if (strpos($line, 'Host:') !== false){
			            	    $ip_address = substr($line, 6, strpos($line, '(')-6);
								$data = array();
			            	    $data['project_id'] = $pid;
			            	    $data['ip_address'] = $ip_address;
			            	    $data['user_create'] = $this->_username;
			            	    $data['user_mod'] = $this->_username;
			            	    $data['create_date'] = date('YmdHis');
			            	    $data['mod_date'] = date('YmdHis');
		            	        //insert data to nmap table and get nmap_id to insert to nmap_detail table 
		            	        try {
		            	            $nmap_id = $nmap->insert($data);
		            	            if (strpos($line, 'Ports:') !== false){
		            	            	$ports = substr($line, strpos($line, 'Ports:')+7);
		            	            	$items = explode(",", $ports);
		            	            	foreach($items as $item){
		            	            		$value = split('((/)|(//)|(///))', $item);
		            	            		if (count($value)>=4) {
		            	            			$data = array();
		            	            			$data['nmap_id'] = $nmap_id;
		            	            			$data['port'] = $value[0];
		            	            			$data['state'] = $value[1];
		            	            			$data['protocol'] = $value[2];
		            	            			$data['service'] = $value[3];
		            	            			//insert data to nmap_detail table
		            	            			$nmap_detail->insert($data);
		            	            		}
		            	            	}
		            	            }
	            	            }catch (Exception $e) {
	            	            	$this->_CommonController->writeLog('Line ' . $line_num . ': ' . $ip_address . ' ' . $e->getMessage());
	            	            }	            	        
			            	}
			            }
				    }
				    $this->_CommonController->checkToDeleteImportFile($pathFileName, $importFile);
	            }
	        }
        }catch (Exception $e) {
        	$this->_CommonController->writeLog($e->getMessage());
        }        
    }
    
	/**
     * export page
     * **/
    public function exportAction()
    {
        //check permission
        $this->_CommonController->checkPermission('NMAP', 'f_export', '/nmap/index/pid/'.$this->_pid);
        try {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            //store search param in session for pagination
            $search = new Zend_Session_Namespace('psearch');
            $arrCondition = $search->psearch;
            $arrCondition['pid'] = $this->_pid;
        	$oNmap = new Application_Model_Nmap();
        	$data[] = array('Ip Address', 'Port', 'State', 'Protocol', 'Service', 'Mnemonics');
        	$result = $oNmap->loadList($arrCondition, true);
        	$data = array_merge($data, $result);
			$this->_CommonController->exportExcel($data, 'Nmap', 'nmap');
        } catch (Exception $e) {
        	// pass possible exceptions to the log
        	$this->_CommonController->writeLog($e->getMessage());
        }
    }
}

