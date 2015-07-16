<?php

/**
 * ProjectController
 * 
 * @author
 * @version 
 */
// includes
include_once APPLICATION_PATH . '/controllers/CommonController.php';
class ProjectController extends Zend_Controller_Action
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
	    $oIpAddressFunUser = $this->_CommonController->getFunctionUser(IPADDRESS);
	    $oNmapFunUser = $this->_CommonController->getFunctionUser(NMAP);
	    $oNessusFunUser = $this->_CommonController->getFunctionUser(NESSUS);
	    Zend_Registry::set('CLIENT', $oClientFunUser);
	    Zend_Registry::set('PROJECT', $oProjectFunUser);
	    Zend_Registry::set('IPADDRESS', $oIpAddressFunUser);
	    Zend_Registry::set('NMAP', $oNmapFunUser);
	    Zend_Registry::set('NESSUS', $oNessusFunUser);
    }
    
    /**
     * The default action - show the home page
     */
    public function indexAction()
    {
        $this->_CommonController->checkPermission('PROJECT', 'f_view', '/client/index');
        // action body
        $request = $this->getRequest();
        $form    = new Application_Form_Project();
        try {
            $form->isValid($request->getPost());
            //clear old session
            $bNewSearch = $this->_getParam("ns", null);
            if ($bNewSearch!=null) {
            	$this->_CommonController->detroySession('psearch');
            }
            $currentPage = $this->_getParam("page", 1);
            //count per page
		    $countperpage = $this->_getParam("cpage", COUNTPERPAGE);
		    $this->view->countperpage = $countperpage;
			$paginator = $this->_CommonController->loadProjectList($request, $form, $currentPage, $countperpage, $this->_username);
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
        $form    = new Application_Form_Project();
//         $oCommonModel = new Application_Model_Common();
        $oClientModel = new Application_Model_Client();
        $oUtils = new Utils();
        $oProject = new Application_Model_Project();
        $pid = $this->_getParam("pid", null);
        $cid = $this->_getParam("cid", null);
        try {
            //init values
            $form->getElement('user_create')->setValue($this->_username);
            //load and set data to client options
//             $fieldList = array('client_id', 'client_name');
            $arrCondition = array('username' => $this->_username);
            $result = $oClientModel->loadList($arrCondition);//$oCommonModel->loadList('client', $fieldList, null, null, 'client_id ASC');
            $arrData = $oUtils->convertData2Option($result, array('client_id', 'client_name'));
            $form->client_code->addMultiOptions($arrData);
            //save button is clicked
            if ($this->getRequest()->isPost()) {
            	if ($form->isValid($request->getPost())) {
            	    if ($this->_CommonController->saveProject($this->getRequest(), $form)==false) {
//             	        if ($cid!=''){
//             	        	$this->_redirect('/project/detail/cid/'.$cid);
//             	        	return false;
//             	        }
// 	            	    if ($pid!="") {
// 	            	        $this->_redirect('/project/detail/pid/' . $pid);
// 	            	        return false;
// 	            	    }
            	        $this->_redirect('/project/index/');
            	        return false;
            	    }
            	}
            } 
//          }  else {
                //load current project
	            if ($cid!=null) {
	            	$oClientDetail = $oClientModel->loadDetail($cid);
	            	$form->getElement('client_code')->setValue(array($oClientDetail['client_id'], $oClientDetail['client_name']));
	            	$form->getElement('cid')->setValue($cid);
	            }
                if ($pid!=""){
                    $this->_CommonController->checkPermission('PROJECT', 'f_update', '/project/index');
	                $oProject = new Application_Model_Project();
	                $oDetail = $oProject->loadDetail($pid);
// 	                print_r($oDetail);exit;
	            	$form->getElement('client_code')->setValue(array($oDetail->client_id, $oDetail->client_name));
	            	$form->getElement('pid')->setValue($pid);
	            	$this->view->oProject = $oDetail;
                }else {
                    $this->_CommonController->checkPermission('PROJECT', 'f_insert', '/project/index');
                }
           	    $form->getElement('user_create')->setValue($this->_username);
//             }
        } catch (Exception $e) {
           	// pass possible exceptions to log file
        	$this->_CommonController->writeLog($e->getMessage());
        }
        $this->view->project_id = $request->getPost('project_code', null);
        $this->view->formProject = $form;
    }
       
    /**
     * get project list 
     */
    public function projectAction()
    {
    	$id = $this->_getParam('id');
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$oCommonModel = new Application_Model_Common();
    	$fieldList = array('project_id', 'project_name');
    	$result = $oCommonModel->loadList('project', $fieldList, 'client_id', $id, 'project_id ASC');
    	$result = array_merge(array(0=>array('project_id'=>'', 'project_name'=>'')),$result);
    	echo '{"projects":'.Zend_Json::encode($result).'}';
    }
    
    /**
     * get project list
     */
    public function projectdetailAction()
    {
    	$id = $this->_getParam('id');
    	$this->_helper->layout->disableLayout();
    	$this->_helper->viewRenderer->setNoRender(true);
    	$oCommonModel = new Application_Model_Common();
    	$result = $oCommonModel->loadDetail('project', 'project_id', $id);
    	echo Zend_Json::encode($result);
    }
    

}
?>