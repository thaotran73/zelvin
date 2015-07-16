<?php
// includes
include_once APPLICATION_PATH . '/controllers/CommonController.php';
include_once APPLICATION_PATH . '../../library/Spreadsheet/reader.php';

class IpaddressController extends Zend_Controller_Action
{
	protected  $_filename = "/spreadsheet_";
	protected  $_ext = ".xls";
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
	    $oIpAddressFunUser = $this->_CommonController->getFunctionUser(IPADDRESS);
	    Zend_Registry::set('PROJECT', $oProjectFunUser);
	    Zend_Registry::set('IPADDRESS', $oIpAddressFunUser);
	}

	/**
	 * search ip address page
	 * **/
	public function indexAction()
	{
	    // action body
	    $request = $this->getRequest();
	    $form    = new Application_Form_Ipaddress();
	    $buttonsearch = $request->getPost('buttonsearch', null);
	   	$btnDelete = $request->getPost('act', null);
	    $st = $this->_getParam("st", '');
	    try {
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
	        
		    $oIpAddress = new Application_Model_Ipaddress();
		    $hidDel = Zend_Filter::filterStatic($request->getPost('hidDel', null), 'StripTags');
		    //delete items are selected
		    if ($hidDel!=""){
				$oIpAddress->deleteItems($hidDel);
		    }
		    //clear old session
		    $bNewSearch = $this->_getParam("ns", null);
		    if ($bNewSearch!=null) {
		    	$this->_CommonController->detroySession('psearch');
		    }
		    //get post values
		    $pid = $this->_getParam("pid", 1);
		    //get post values
		    $arrCondition = array();
		    //store search param in session for pagination
		    $search = new Zend_Session_Namespace('psearch');
		    //get search param
		    $buttonsearch = Zend_Filter::filterStatic($request->getPost('buttonsearch', null), 'StripTags');
		    if ($buttonsearch=='Search'){
			    $ip_address_search = Zend_Filter::filterStatic($request->getPost('ip_address_search', null), 'StripTags');
			    $mnemonics_search = Zend_Filter::filterStatic($request->getPost('mnemonics_search', null), 'StripTags');
			    $url_search = Zend_Filter::filterStatic($request->getPost('url_search', null), 'StripTags');
			    $owner_name_search = Zend_Filter::filterStatic($request->getPost('owner_name_search', null), 'StripTags');
			    $arrCondition = array('ip_address' => $ip_address_search, 'mnemonics' => $mnemonics_search, 'url' => $url_search, 'owner_name' => $owner_name_search);
			    $search->psearch = $arrCondition;
		    }else{
	        	$arrCondition = $search->psearch;
			}
			$arrCondition['pid'] = $pid;
			//set search values to search box
			$form->getElement('ip_address_search')->setValue(isset($arrCondition['ip_address'])?$arrCondition['ip_address']:'');
			$form->getElement('mnemonics_search')->setValue(isset($arrCondition['mnemonics'])?$arrCondition['mnemonics']:'');
			$form->getElement('url_search')->setValue(isset($arrCondition['url'])?$arrCondition['url']:'');
			$form->getElement('owner_name_search')->setValue(isset($arrCondition['owner_name'])?$arrCondition['owner_name']:'');
			if ($st!='') {
				$arrCondition['st'] = $st==1 ? 'ASC' : 'DESC';
			}
		    $currentPage = $this->_getParam("page", 1);
		    //count per page
		    $countperpage = $this->_getParam("cpage", COUNTPERPAGE);
		    $this->view->countperpage = $countperpage;
		    // Object of Zend_Paginator
			$result = $oIpAddress->loadList($arrCondition);
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
	
	/**
	 * detail action page
	 * **/
	public function detailAction()
	{
		//detroy search session condition
		$this->_CommonController->detroySession('psearch');
		// action body
		$request = $this->getRequest();
		$form    = new Application_Form_Ipaddress();
// 		$formProject    = new Application_Form_Project();
		$oIpAddress = new Application_Model_Ipaddress();
		$id = $this->_getParam("id", null);
		try{
			if ($this->getRequest()->isPost()) {
				if ($form->isValid($request->getPost())) {
					//get post values
					$submitbutton = Zend_Filter::filterStatic($request->getPost('submitbutton', null), 'StripTags');
					//get search param
					if ($submitbutton=='Save'){
						$ip_address = Zend_Filter::filterStatic($request->getPost('ip_address', null), 'StripTags');
						$mnemonics = Zend_Filter::filterStatic($request->getPost('mnemonics', null), 'StripTags');
						$url = Zend_Filter::filterStatic($request->getPost('url', null), 'StripTags');
						$owner_name = Zend_Filter::filterStatic($request->getPost('owner_name', null), 'StripTags');
						$data = array('ip_address' => $ip_address, 'mnemonics' => $mnemonics, 'url' => $url, 'owner_name' => $owner_name, 'user_mod' => $this->_username, 'mod_date' => date('YmdHis'));
						//check insert or update
						$id = Zend_Filter::filterStatic($request->getPost('id', null), 'StripTags');
						//insert
						if ($id=="") {
						    $oProjectDetail = $this->_CommonModel->loadDetail('project', 'md5(project_id)', $this->_CommonController->decodeKey($this->_pid));
						    $pid = isset($oProjectDetail->project_id)?$oProjectDetail->project_id:'';
							$data['project_id'] = $pid;
							$data['user_create'] =  $this->_username;
							$data['create_date'] = date('YmdHis');
							//insert data to ip_address table
							$oIpAddress->insert($data);
							$form->reset();
							//update
						} else {
							$where['md5(ip_address_id) = ?'] = $this->_CommonController->decodeKey($id);
							$oIpAddress->update($data, $where);
// 							$this->_redirect('/ipaddress/index/pid/'.$this->_pid);
// 							return false;
						}
						$this->_redirect('/ipaddress/index/pid/'.$this->_pid);
						return false;
					}
				}
			} else {
				if ($id!='') {
// 				    $this->_CommonController->checkPermission('IPADDRESS', 'f_update', '/ipaddress/index/pid/'.$this->_pid);
					$oDetail = $oIpAddress->loadDetail($id);
					$form->getElement('ip_address')->setValue($oDetail['ip_address']);
					$form->getElement('url')->setValue($oDetail['url']);
					$form->getElement('mnemonics')->setValue($oDetail['mnemonics']);
					$form->getElement('owner_name')->setValue($oDetail['owner_name']);
					$form->getElement('id')->setValue($id);
				}else {
					$this->_CommonController->checkPermission('IPADDRESS', 'f_insert', '/ipaddress/index/pid/'.$this->_pid);
				}
			}
		} catch (Exception $e) {
			// pass possible exceptions to log file
        	$this->_CommonController->writeLog($e->getMessage());
		}
		$this->view->id = $id;
		$form->getElement('pid')->setValue($this->_pid);
		$this->view->form = $form;
		$this->view->formProject = $this->_formProject;
	}

	/**
	 * import action
	 * **/
	public function importAction()
    {
        //check permission
        $this->_CommonController->checkPermission('IPADDRESS', 'f_import', '/ipaddress/index/pid/'.$this->_pid);
        // action body
        $request = $this->getRequest();
        $form    = new Application_Form_Importfile();
        try{
            if ($this->getRequest()->isPost()) {
            	if ($form->isValid($request->getPost())) {
            	    $ext = end(explode('.', $form->importFile->getFileName()));
//             	    if ($ext=='xls'){
	            	    // where do you want to save the file?
	            	    $desFolder = $form->importFile->getDestination();
	            	    $fName = $this->_filename . date('YmdHis') . '.' . $ext;
	            	    $pathFileName = $desFolder . $fName;
	            	    // add filter for renaming the uploaded picture
	            	    $form->importFile->addFilter('Rename',
	            	    		array('target' => $pathFileName,
	            	    				'overwrite' => true));
	            		if ($form->importFile->receive()) {
	            		    if (file_exists($pathFileName)) {
	            		        if (!$this->parseImportIpAddressToDb($request, $pathFileName)) {
	            		            $form->importFile->addError(str_replace('xxx', end(explode('\\', substr($fName, 1))), EXTENSION_FALSE_MSG));
	            		        }
	            		    }
	            		    $form->reset();
	            		    $this->_redirect('/ipaddress/index/pid/'.$this->_pid);
	            		    return false;
	            		}
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
     * import data from spreadsheet file to db
     * @param: $pathFileName - path of filename
     **/
    public function parseImportIpAddressToDb($request, $pathFileName){
        $oIpAddress = new Application_Model_Ipaddress();
        try{
	        if (file_exists($pathFileName)) {
	            $importFile = $this->_CommonController->detectImportFileName($pathFileName);
	            if (file_exists($importFile)) {
	                $oProjectDetail = $this->_CommonModel->loadDetail('project', 'md5(project_id)', $this->_CommonController->decodeKey($this->_pid));
	                $pid = isset($oProjectDetail->project_id)?$oProjectDetail->project_id:'';
	                if ($pid!=''){
	                    //delete old ipaddress of this project
		                $overwrite = $request->getPost('chk_overwrite', null);
		                $extension = pathinfo($importFile, PATHINFO_EXTENSION);
		                if ('.' .$extension == $this->_ext) {
		                    //delete old ipaddress of this project
		                    if ($overwrite==1) {
// 		                    	$oIpAddress->delete(array('project_id' => $pid));
		                        $oIpAddress->deleteProject($this->_pid);
		                    }
				            $dataSpreadsheet = new Spreadsheet_Excel_Reader();
				            // Set output Encoding.
				            $dataSpreadsheet->setOutputEncoding('CP1251');
				            $dataSpreadsheet->read($importFile);
				            for ($i = 2; $i <= $dataSpreadsheet->sheets[0]['numRows']; $i++) {
				                $data = array();
			            	    $data['project_id'] = $pid;
			            	    if (isset($dataSpreadsheet->sheets[0]['cells'])) {
				            	        $ip_address = isset($dataSpreadsheet->sheets[0]['cells'][$i][1])?trim($dataSpreadsheet->sheets[0]['cells'][$i][1]):'';
				            	        $url = isset($dataSpreadsheet->sheets[0]['cells'][$i][2])?trim($dataSpreadsheet->sheets[0]['cells'][$i][2]):'';
				            	        $mnemonics = isset($dataSpreadsheet->sheets[0]['cells'][$i][3])?trim($dataSpreadsheet->sheets[0]['cells'][$i][3]):'';
				            	        $owner_name = isset($dataSpreadsheet->sheets[0]['cells'][$i][4])?trim($dataSpreadsheet->sheets[0]['cells'][$i][4]):'';
					            	    $data['ip_address'] = $ip_address;
					            	    $data['url'] = $url;
					            	    $data['mnemonics'] = $mnemonics;
					            	    $data['owner_name'] = $owner_name;
					            	    $this->saveIPAddress($oIpAddress, $data, $i);
			            	    } else {
			            	        $this->_CommonController->writeLog('Line ' . $i . ': data invalid.');
			            	    }
				            }
			            }else if (in_array($extension, array('txt', 'csv'))) {
			                //delete old ipaddress of this project
			                if ($overwrite==1) {
// 			                	$oIpAddress->delete(array('project_id' => $pid));
			                    $oIpAddress->deleteProject($this->_pid);
			                }
				  	        //text file (txt or csv)
			                if (($handle = fopen($importFile, "r")) !== FALSE) {
			                    $row = 1;
			                	while (($line = fgetcsv($handle, 1000, ",")) !== FALSE) {
			                	    $num = count($line);
			                	    $data = array();
			                	    $data['project_id'] = $pid;
			                	    if ($num > 1){
				                		for ($c=0; $c < $num; $c++) {
				                			if ($c == 0) {
				                				$data['ip_address'] = $line[$c];
				                			} else if ($c == 1) {
				                				$data['url'] = $line[$c];
			                				} else if ($c == 2) {
			                					$data['mnemonics'] = $line[$c];
		                					} else if ($c == 3) {
		                						$data['owner_name'] = $line[$c];
				                			}
				                		}
			                	    } else {
			                	        $tmpLine = $line[0];
			                	        $ip_address = "";
			                	        if (strpos($tmpLine, 'Host:') !== false ){
			                	            if (strrpos($tmpLine, 'Status: Up') !== false) {
			                	            	$ip_address = substr($tmpLine, 6, strpos($tmpLine, '(')-6);
			                	            }
			                	        } else {
			                	            if (strpos($tmpLine, 'Nmap') == false) {
			                	            	$ip_address = $tmpLine;
			                	            }
			                	        }
			                	        $data['ip_address'] = $ip_address;
			                	    }
			                	    if ($ip_address != '') {
	 			                		$this->saveIPAddress($oIpAddress, $data, $row);
			                	    }
			                		$row++;
			                	}
			                	fclose($handle);
			                }
			            } else {
			                return false;
		                }
	                }
					$this->_CommonController->checkToDeleteImportFile($pathFileName, $importFile);
	            }
	        }
	        return true;
        }catch (Exception $e) {
        	$this->_CommonController->writeLog($e->getMessage());
        }        
    }
    
    public function saveIPAddress($oIpAddress, $data, $i){
        $data['user_create'] = $this->_username;
        $data['user_mod'] = $this->_username;
        $data['create_date'] = date('YmdHis');
        $data['mod_date'] = date('YmdHis');
        //insert data to ip_address table
        try {
        	if (trim($data['ip_address'])!=''){
        		$oIpAddress->insert($data);
        	} else {
        		$this->_CommonController->writeLog('Line ' . $i . ': data not empty.');
        	}
        }catch (Exception $e) {
        	$this->_CommonController->writeLog('Line ' . $i . ': ' . $e->getMessage());
        }
    }
    
    /**
     * export page
     * **/
    public function exportAction()
    {
        //check permission
        $this->_CommonController->checkPermission('IPADDRESS', 'f_export', '/ipaddress/index/pid/'.$this->_pid);
        try {
            $this->_helper->layout->disableLayout();
            $this->_helper->viewRenderer->setNoRender(true);
            //store search param in session for pagination
            $search = new Zend_Session_Namespace('psearch');
            $arrCondition = $search->psearch;
            $arrCondition['pid'] = $this->_pid;
        	$oIpAddress = new Application_Model_Ipaddress();
        	$data[] = array('IP Address', 'URL', 'Mnemonic', 'Owner');
        	$result = $oIpAddress->loadList($arrCondition, true);
        	$data = array_merge($data, $result);
			$this->_CommonController->exportExcel($data, 'Ip Address', 'ipaddress');
        } catch (Exception $e) {
        	// pass possible exceptions to log file
        	$this->_CommonController->writeLog($e->getMessage());
        }
    }
}