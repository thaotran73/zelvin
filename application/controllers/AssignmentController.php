<?php
// includes
include_once APPLICATION_PATH . '/controllers/CommonController.php';
class AssignmentController extends Zend_Controller_Action
{
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
		
		//get user information when user is logon
		$infoUser = $this->_CommonModel->loadInfoUser();
		$infoProject = $this->_CommonModel->loadDetail('project', 'md5(project_id)', $this->_CommonController->decodeKey($pid));
		//admin or owner of project only
		if ($infoUser->permission_type!=ADMIN && $infoProject->user_create!=$this->_username && $this->getRequest()->getActionName()=='detail') {
			$this->_redirect('/assignment/index/pid/' . $pid);
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
    	$oAssignmentFunUser = $this->_CommonController->getFunctionUser(ASSIGNMENT);
    	Zend_Registry::set('PROJECT', $oProjectFunUser);
    	Zend_Registry::set('ASSIGNMENT', $oAssignmentFunUser);
    }

	/**
	 * search assignment page
	 * **/
	public function indexAction()
	{
	    // action body
	    $request = $this->getRequest();
	    $form    = new Application_Form_Assignment();
	    $buttonsearch = $request->getPost('buttonsearch', null);
	    $btnDelete = $request->getPost('btnDelete', null);
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
	        
		    $oProjectIpUser = new Application_Model_Projectipuser();
		    $hidDel = Zend_Filter::filterStatic($request->getPost('hidDel', null), 'StripTags');
		    //delete items are selected
		    if ($hidDel!=""){
				$oProjectIpUser->deleteItems($hidDel);
		    }
		    //clear old session
		    $bNewSearch = $this->_getParam("ns", null);
		    if ($bNewSearch!=null) {
		    	$this->_CommonController->detroySession('psearch');
		    }
		    //get post values
		    $pid = $this->_getParam("pid", 1);
		    //store search param in session for pagination
		    $search = new Zend_Session_Namespace('psearch');
		    //get search param
		    $buttonsearch = Zend_Filter::filterStatic($request->getPost('buttonsearch', null), 'StripTags');
		    if ($buttonsearch=='Search'){
			    $ip_address_list_search = Zend_Filter::filterStatic($request->getPost('ip_address_list_search', null), 'StripTags');
			    $mnemonics_list_search = Zend_Filter::filterStatic($request->getPost('mnemonics_list_search', null), 'StripTags');
			    $username_assigned_searchh = Zend_Filter::filterStatic($request->getPost('username_assigned_search', null), 'StripTags');
			    $arrCondition = array('ip_address_list' => $ip_address_list_search, 'mnemonics_list' => $mnemonics_list_search,'username_assigned' => $username_assigned_searchh);
			    $search->psearch = $arrCondition;
		    }else{
	        	$arrCondition = $search->psearch;
			}
			$arrCondition['pid'] = $pid;
			//set search values to search box
			$form->getElement('ip_address_list_search')->setValue(isset($arrCondition['ip_address_list'])?$arrCondition['ip_address_list']:'');
			$form->getElement('mnemonics_list_search')->setValue(isset($arrCondition['mnemonics_list'])?$arrCondition['mnemonics_list']:'');
			$form->getElement('username_assigned_search')->setValue(isset($arrCondition['username_assigned'])?$arrCondition['username_assigned']:'');
		    $currentPage = $this->_getParam("page", 1);
		    //count per page
		    $countperpage = $this->_getParam("cpage", COUNTPERPAGE);
		    $this->view->countperpage = $countperpage;
		    // Object of Zend_Paginator
			$result = $oProjectIpUser->loadList($arrCondition);
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
		$form    = new Application_Form_Assignment();
		$formProject    = new Application_Form_Project();
		$id = $this->_getParam("id", null);
		$oUser = new Application_Model_User();
		$oUtils = new Utils();
		$oProject = new Application_Model_Project();
		$oProjectIpUser = new Application_Model_Projectipuser();
		$pid = $this->_getParam("pid", null);
		$buttonsearch = $request->getPost('buttonsearch', null);
		$btnDelete = $request->getPost('btnDelete', null);
		try{
		    //load users to username options
		    $arrCondition = array();
		    $result = $oUser->loadList($arrCondition);//$oCommonModel->loadList('client', $fieldList, null, null, 'client_id ASC');
		    $arrData = $oUtils->convertData2Option($result, array('username', 'username'), false);
		    $form->username->addMultiOptions($arrData);
		    $range_type = 0;
		    if ($buttonsearch=="" && $btnDelete=="") {
		    	//save button is clicked
				if ($this->getRequest()->isPost()) {
				    $arrUserAssgined =  $request->getPost('username_assigned', null);
				    $username_list = '';
				    for ($i=0; $i<count($arrUserAssgined); $i++) {
				    	$arrTmp[$arrUserAssgined[$i]] = $arrUserAssgined[$i];
				    	if ($username_list!=''){
				    		$username_list .= ', ' . $arrUserAssgined[$i];
				    	}else {
				    		$username_list .= $arrUserAssgined[$i];
				    	}
				    }
				    $form->username_assigned->addMultiOptions($arrTmp);
					if ($form->isValid($request->getPost())) {
					    $bError = false;
					    $range_type = $request->getPost('range_type', 0);
					    $ip_address_list = Zend_Filter::filterStatic($request->getPost('ip_address_list', null), 'StripTags');
					    $mnemonics_list = Zend_Filter::filterStatic($request->getPost('mnemonics_list', null), 'StripTags');
					    if ($range_type==IPADDRESS_RANGE) {
					        if ($ip_address_list==null) {
						        $form->ip_address_list->addError(ENTER_DATA);
						        $bError = true;
					        } 
					    } else if ($range_type==MNEMONICS_RANGE) {
					        if ($mnemonics_list==null) {
					        	$form->mnemonics_list->addError(ENTER_DATA);
					        	$bError = true;
					        }
				        }
					    if ($bError==false) {
// 					        $arrUserAssgined =  $request->getPost('username_assigned', null);
// 					        $username_list = '';
// 					        for ($i=0; $i<count($arrUserAssgined); $i++) {
// 					        	$arrTmp[$arrUserAssgined[$i]] = $arrUserAssgined[$i];
// 					        	if ($username_list!=''){
// 					        		$username_list .= ', ' . $arrUserAssgined[$i];
// 					        	}else {
// 					        		$username_list .= $arrUserAssgined[$i];
// 					        	}
// 					        }
// 					        $form->username_assigned->addMultiOptions($arrTmp);
					        //get post values
					        $submitbutton = Zend_Filter::filterStatic($request->getPost('submitbutton', null), 'StripTags');
					        //get search param
					        if ($submitbutton=='Save'){
					        	$oProjectDetail = $this->_CommonModel->loadDetail('project', 'md5(project_id)', $this->_CommonController->decodeKey($this->_pid));
					        	$pid = isset($oProjectDetail->project_id)?$oProjectDetail->project_id:'';
					        	//save data to project_ip_user table
					        	if ($range_type==IPADDRESS_RANGE) {
					        	    $mnemonics_list = '';
					        	} else {
					        	    $ip_address_list = '';
					        	}
					        	$data = array('project_id' => $pid, 'range_type' => $range_type,'ip_address_list' => $ip_address_list, 'mnemonics_list' => $mnemonics_list, 'username_list' => $username_list);
					        	if ($id=="") {
					        		$project_ip_user_id = $oProjectIpUser->insert($data);
					        	}else {
					        		$where['md5(project_ip_user_id) = ?'] = $this->_CommonController->decodeKey($id);
					        		$oProjectIpUser->update($data, $where);
					        	}
					        	//save data to project_ip_user_detail table
					        	$this->saveProjectIpUserDetail($id, $project_ip_user_id, $arrUserAssgined, $range_type, $ip_address_list, $mnemonics_list, ',');
					        	//check insert or update
					        	$id = Zend_Filter::filterStatic($request->getPost('id', null), 'StripTags');
					        	$this->_redirect('/assignment/index/pid/'.$this->_pid);
					        	return false;
					        }
					    }
					}
				} else {
					if ($id!='') {
						$oDetail = $oProjectIpUser->loadDetail($id);
						$range_type = $oDetail['range_type'];
						$form->getElement('ip_address_list')->setValue($oDetail['ip_address_list']);
						$form->getElement('mnemonics_list')->setValue($oDetail['mnemonics_list']);
						$arrUserAssgined = explode(',', $oDetail['username_list']);
						for ($i=0; $i<count($arrUserAssgined); $i++) {
							$arrTmp[$arrUserAssgined[$i]] = $arrUserAssgined[$i];
						}
						$form->username_assigned->addMultiOptions($arrTmp);
						$form->getElement('id')->setValue($id);
					}else {
						$this->_CommonController->checkPermission('ASSIGNMENT', 'f_insert', '/assignment/index/pid/'.$this->_pid);
						$arrTmp = array($this->_username => $this->_username);
						$form->username_assigned->addMultiOptions($arrTmp);
					}
				}
		    }
		} catch (Exception $e) {
			// pass possible exceptions to log file
        	$this->_CommonController->writeLog($e->getMessage());
		}
		$this->view->range_type = $range_type;
		$this->view->id = $id;
		$form->getElement('pid')->setValue($this->_pid);
		$this->view->form = $form;
		$this->view->formProject = $this->_formProject;
	}

	/**
	 * save data to project_ip_user_detail table
	 * **/
	public function saveProjectIpUserDetail($id, $project_ip_user_id, $arrUserAssgined, $range_type, $ip_address_list, $mnemonics_list, $separator){
	    $oProjectIpUser = new Application_Model_Projectipuser();
	    $oProjectIpUserDetail = new Application_Model_Projectipuserdetail();
	    if ($id!="") {
		    //delete old data of this project_ip_user_detail
		    $where['md5(project_ip_user_id) = ?'] = $this->_CommonController->decodeKey($id);
		    $oProjectIpUserDetail->delete($where);
		    //find real project_ip_user_id
		    $oDetail = $oProjectIpUser->loadDetail($id);
		    $project_ip_user_id = $oDetail['project_ip_user_id'];
	    }
	    if ($range_type==IPADDRESS_RANGE) {
		    if ($ip_address_list!='' && $ip_address_list != null){
		        for ($i=0; $i<count($arrUserAssgined); $i++) {
			    	$arrIpList = explode($separator, $ip_address_list);
			    	for ($j=0; $j<count($arrIpList); $j++){
			    	    $data = array('project_ip_user_id' => $project_ip_user_id, 'username' => trim($arrUserAssgined[$i]));
			    	    $arrItems = explode('-', $arrIpList[$j]);
			    	    if (isset($arrItems[0])) {
			    	    	$data['ip_address_from'] = trim($arrItems[0]);
			    	    }
			    	    if (isset($arrItems[1])) {
			    	    	$data['ip_address_to'] = trim($arrItems[1]);
			    	    } else {
			    	        $data['ip_address_to'] = trim($arrItems[0]);
			    	    }
			    	    $oProjectIpUserDetail->insert($data);	    	    
			    	}
		        }
		    }
	    } else if ($range_type==MNEMONICS_RANGE) {
	        if ($mnemonics_list!='' && $mnemonics_list != null){
	        	for ($i=0; $i<count($arrUserAssgined); $i++) {
	        		$arrMnemonicsList = explode($separator, $mnemonics_list);
	        		for ($j=0; $j<count($arrMnemonicsList); $j++){
	        			$data = array('project_ip_user_id' => $project_ip_user_id, 'username' => trim($arrUserAssgined[$i]));
	        			$arrItems = explode('-', $arrMnemonicsList[$j]);
	        			if (isset($arrItems[0])) {
	        				$data['mnemonics_from'] = trim($arrItems[0]);
	        			}
	        			if (isset($arrItems[1])) {
	        				$data['mnemonics_to'] = trim($arrItems[1]);
	        			} else {
	        				$data['mnemonics_to'] = trim($arrItems[0]);
	        			}
	        			$oProjectIpUserDetail->insert($data);
	        		}
	        	}
	        }
	    }
	}

}

