<?php
// includes
include_once APPLICATION_PATH . '/controllers/CommonController.php';
class ClientController extends Zend_Controller_Action
{
    protected $_username = "";
    protected $_CommonController = null;
    protected $_CommonModel = null;
    
    public function preDispatch() {
		$intStatus = $this->_CommonController->checkLogon();
    	if ($intStatus==0){
    		$this->_redirect('/index/logout');
    	}else if ($intStatus==1) {
    	    $this->_redirect('/user/changepass');
    	}
    }
    
    public function init()
    {
    	/* Initialize action controller here */
    	$this->_CommonModel = new Application_Model_Common();
    	$this->_CommonController = new CommonController();
    	 
    	//get user information when user is logon
    	$infoUser = $this->_CommonModel->loadInfoUser();
    	if ($infoUser!=null) {
    		$this->_username = $infoUser->username;
    	}
    	$oClientFunUser = $this->_CommonController->getFunctionUser(CLIENT);
    	$oProjectFunUser = $this->_CommonController->getFunctionUser(PROJECT);
    	Zend_Registry::set('CLIENT', $oClientFunUser);
    	Zend_Registry::set('PROJECT', $oProjectFunUser);
    }
    
    public function indexAction()
    {
        //check permission
        $this->_CommonController->checkPermission('CLIENT', 'f_view', '/index/logout');
        // action body
        $request = $this->getRequest();
        $form    = new Application_Form_Client();
        try {
        	$oClient = new Application_Model_Client();
        	$hidDel = Zend_Filter::filterStatic($request->getPost('hidDel', null), 'StripTags');
        	//delete items are selected
        	if ($hidDel!=""){
        		$oClient->deleteItems($hidDel);
        	}
        	//clear old session
        	$bNewSearch = $this->_getParam("ns", null);
        	if ($bNewSearch!=null) {
        		$this->_CommonController->detroySession('psearch');
        	}
        	//get post values
        	$arrCondition = array();
        	//store search param in session for pagination
        	$search = new Zend_Session_Namespace('psearch');
        	//get search param
        	$buttonsearch = Zend_Filter::filterStatic($request->getPost('buttonsearch', null), 'StripTags');
        	if ($buttonsearch=='Search'){
        		$client_name_search = Zend_Filter::filterStatic($request->getPost('client_name_search', null), 'StripTags');
        		$email_search = Zend_Filter::filterStatic($request->getPost('email_search', null), 'StripTags');
        		$address_search = Zend_Filter::filterStatic($request->getPost('address_search', null), 'StripTags');
        		$phone_search = Zend_Filter::filterStatic($request->getPost('phone_search', null), 'StripTags');
        		$arrCondition = array('client_name' => $client_name_search, 'email' => $email_search, 'address' => $address_search, 'phone' => $phone_search);
        		$search->psearch = $arrCondition;
        	}else{
        		$arrCondition = $search->psearch;
        	}
        	$arrCondition['username'] = $this->_username;
        	//set search values to search box
        	$form->getElement('client_name_search')->setValue(isset($arrCondition['client_name'])?$arrCondition['client_name']:'');
        	$form->getElement('email_search')->setValue(isset($arrCondition['email'])?$arrCondition['email']:'');
        	$form->getElement('address_search')->setValue(isset($arrCondition['address'])?$arrCondition['address']:'');
        	$form->getElement('phone_search')->setValue(isset($arrCondition['phone'])?$arrCondition['phone']:'');
        	//count per page
        	$arrCondition = $search->psearch;
        	$cpage = $arrCondition['cpage'];
        	$page = $arrCondition['page'];
        	$currentPage = $this->_getParam("page", $page!=''?$page:1);
		    $countperpage = $this->_getParam("cpage", $cpage!=''?$cpage:COUNTPERPAGE);
		    $this->view->countperpage = $countperpage;
		    //save current page & count per page to session
		    $arrCondition['cpage'] = $countperpage;
		    $arrCondition['page'] = $currentPage;
		    $search->psearch = $arrCondition;
        	// Object of Zend_Paginator
        	$result = $oClient->loadList($arrCondition);
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
    }

	/**
     * The detail action
     */
    public function detailAction()
    {
        // action body
        $request = $this->getRequest();
        $form    = new Application_Form_Client();
        $oClient = new Application_Model_Client();
        $cid = $this->_getParam("cid", null);
        $buttonsearch = $request->getPost('buttonsearch', null);
        $btnDelete = $request->getPost('act', null);
        $bError = false;
        try {
            if ($buttonsearch=="" && $btnDelete=="") {
	            //save button is clicked
	            if ($this->getRequest()->isPost()) {
	            	if ($form->isValid($request->getPost())) {
	            	    $result = $this->_CommonController->saveClient($this->getRequest(), $form);
	            	    if ($result==false) {
		            	    if ($cid!="") {
		            	        $this->_redirect('/client/index');
		            	    } else {
		            	        $this->_redirect('/client/index/page/1');
		            	    }
		            	    return false;
	            	    }
	            	}else {
	            	    $bError = true;
	            	}
	            } 
            }
			if ($cid!='') {
			    //$this->_CommonController->checkPermission('CLIENT', 'f_update', '/client/index');
			    if ($bError==false){
					$oDetail = $oClient->loadDetail($cid);
					$form->getElement('client_name')->setValue($oDetail['client_name']);
					$form->getElement('email')->setValue($oDetail['email']);
					$form->getElement('address')->setValue($oDetail['address']);
					$form->getElement('phone')->setValue($oDetail['phone']);
					$form->getElement('cid')->setValue($cid);
					$form->getElement('old_client_name')->setValue($oDetail['client_name']);
			    }
			} else {
			    $this->_CommonController->checkPermission('CLIENT', 'f_insert', '/client/index');
			}
			//load project list
			$currentPage = $this->_getParam("page", 1);
			//count per page
		    $countperpage = $this->_getParam("cpage", COUNTPERPAGE);
		    $this->view->countperpage = $countperpage;
			$formProject    = new Application_Form_Project();
			$paginator = $this->_CommonController->loadProjectList($request, $formProject, $currentPage, $countperpage, $this->_username, $cid);
			// assign to view
			$this->view->paginator = $paginator;
			$this->view->formProject = $formProject;
        } catch (Exception $e) {
           	// pass possible exceptions to log file
        	$this->_CommonController->writeLog($e->getMessage());
        }
        $this->view->cid = $cid;
        $this->view->formClient = $form;
    }
}

