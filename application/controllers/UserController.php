<?php
include_once APPLICATION_PATH . '/controllers/CommonController.php';
class UserController extends Zend_Controller_Action
{

	protected $_username = "";
    protected $_CommonController = null;
    protected $_CommonModel = null;
    
    public function preDispatch() {
        $intStatus = $this->_CommonController->checkLogon();
    	if ($intStatus==0){
    		$this->_redirect('/index/logout');
    	}else if ($intStatus==1 && $this->getRequest()->getActionName()!='changepass') {
    	    $this->_redirect('/user/changepass');
    	}
    	//get user information when user is logon
    	$infoUser = $this->_CommonModel->loadInfoUser();
    	//admin only
    	if ($infoUser->permission_type!=ADMIN && ($this->getRequest()->getActionName()=='index' || $this->getRequest()->getActionName()=='detail')) {
    		$this->_redirect('/client/index');
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
    	$oUserFunUser = $this->_CommonController->getFunctionUser(USER);
    	Zend_Registry::set('USER', $oUserFunUser);
    }

    public function indexAction()
    {
        //check permission
        $this->_CommonController->checkPermission('USER', 'f_view', '/index/logout');
        // action body
        $request = $this->getRequest();
        $form    = new Application_Form_User();
        try {
        	$oUser = new Application_Model_User();
        	$hidDel = Zend_Filter::filterStatic($request->getPost('hidDel', null), 'StripTags');
        	//delete items are selected
        	if ($hidDel!=""){
        		$oUser->deleteItems($hidDel);
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
        		$username_search = Zend_Filter::filterStatic($request->getPost('username_search', null), 'StripTags');
        		$email_search = Zend_Filter::filterStatic($request->getPost('email_search', null), 'StripTags');
        		$address_search = Zend_Filter::filterStatic($request->getPost('address_search', null), 'StripTags');
        		$phone_search = Zend_Filter::filterStatic($request->getPost('phone_search', null), 'StripTags');
        		$arrCondition = array('username' => $username_search, 'email' => $email_search, 'address' => $address_search, 'phone' => $phone_search);
        		$search->psearch = $arrCondition;
        	}else{
        		$arrCondition = $search->psearch;
        	}
        	//set search values to search box
        	$form->getElement('username_search')->setValue($arrCondition['username']);
        	$form->getElement('email_search')->setValue($arrCondition['email']);
        	$form->getElement('address_search')->setValue($arrCondition['address']);
        	$form->getElement('phone_search')->setValue($arrCondition['phone']);
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
        	$result = $oUser->loadList($arrCondition);
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
    
    public function detailAction()
    {
    	// action body
        $request = $this->getRequest();
        $form    = new Application_Form_User();
        $oUser = new Application_Model_User();
        $oFunctionUser = new Application_Model_Functionuser();
        $id = $this->_getParam("id", null);
//         $isLoad = $this->_getParam("isLoad", false);
        try{
        	if ($this->getRequest()->isPost()) {
        		if ($form->isValid($request->getPost())) {   
        			//get post values
        			$submitbutton = $request->getPost('submitbutton', null);
        			//get search param
        			if ($submitbutton=='Save'){
        			    $permission_error = $request->getPost('chkPermission', null);
        				$username = Zend_Filter::filterStatic($request->getPost('username', null), 'StripTags');
        				$email = Zend_Filter::filterStatic($request->getPost('email', null), 'StripTags');
        				$password = $this->_CommonController->generateRandomString();
        				$address = Zend_Filter::filterStatic($request->getPost('address', null), 'StripTags');
        				$phone = Zend_Filter::filterStatic($request->getPost('phone', null), 'StripTags');
        				$permission_type = $request->getPost('permission_type', null);
        				$data = array('username' => $username, 'email' => $email, 'address' => $address, 'phone' => $phone, 'permission_type' => $permission_type);
        				$old_username = Zend_Filter::filterStatic($request->getPost('old_username', null), 'StripTags');
        				$old_email = Zend_Filter::filterStatic($request->getPost('old_email', null), 'StripTags');
        				$bError = false;
        				//check duplicate username
        				if (($old_username!=null && $old_username!=$username) || $old_username==null) {
        					//check duplicate
        					if (!$this->_CommonController->checkDuplicate('users', 'username', $username)) {
        						$form->username->addError(str_replace('xxx', 'username', EXISTED_MSG));
        						$bError = true;
        					}
        				}
        				//check duplicate email
        				if (($old_email!=null && $old_email!=$email) || $old_email==null) {
        					//check duplicate
        					if (!$this->_CommonController->checkDuplicate('users', 'email', $email)) {
        						$form->email->addError(str_replace('xxx', 'email', EXISTED_MSG));
        						$bError = true;
        					}
        				}
        				if ($permission_error!="") {
        				    $this->view->permission_error = $permission_error;
        				    $bError = true;
        				}
        				if ($bError==false) {
	        				//check insert or update
	//         				$id = $request->getPost('id', null);
        				    $wherefu['username = ?'] = $username;
        				    $oFunctionUser->delete($wherefu);
	        				//insert
	        				if ($id=="") {
	        					//insert data to user table
	        					$data['password'] = md5($password);
	        					$oUser->insert($data);
	        					$form->reset();
	        					//update
	        				} else {
	        					$where['md5(user_id) = ?'] = $this->_CommonController->decodeKey($id);
	        					$oUser->update($data, $where);
	        				}
	        				//update function_user table
	        				$functionList = $oFunctionUser->loadListFunction();
	        				foreach ($functionList as $item) {
	        				    $dataFunctionUser = $this->getDataStatus($request, $username, $item['function_id']);
	        				    $oFunctionUser->insert($dataFunctionUser);
	        				}
	        				if ($id=="") {
	        				    $data['password'] = $password;
	        				    $this->sendMailUser($data);
	         					$form->reset();
	         					$this->_redirect('/user/index/page/1');
	        				} else {
	        				    $this->_redirect('/user/index');
	        				}
	        				return false;
        				}
        			}
        		} else {
        		    $form->getElement('isLoad')->setValue(true);
        		}
        	} else {
        		if ($id!='') {
//         		    $this->_CommonController->checkPermission('USER', 'f_update', '/user/index');
        			$oDetail = $oUser->loadDetail($id);
        			$form->getElement('username')->setValue($oDetail->username);
        			$form->getElement('email')->setValue($oDetail->email);
        			$form->getElement('address')->setValue($oDetail->address);
        			$form->getElement('phone')->setValue($oDetail->phone);
        			$form->getElement('permission_type')->setValue(array($oDetail->permission_type, $oDetail->permission_type));
        			$form->getElement('id')->setValue($id);
        			$form->getElement('old_username')->setValue($oDetail->username);
        			$form->getElement('old_email')->setValue($oDetail->email);
        		} else {
        			$this->_CommonController->checkPermission('USER', 'f_insert', '/user/index');
        		}
        	}
            //load project list
            if ($id!=""){
            	$oUserItem = $oUser->loadDetail($id);
            }
            $result = $oFunctionUser->loadList(isset($oUserItem->username)?$oUserItem->username:'');
            // assign to view
            $this->view->result = $result;
        } catch (Exception $e) {
        	// pass possible exceptions to log file
        	$this->_CommonController->writeLog($e->getMessage());
        }
        $this->view->id = $id;
        $this->view->form = $form;
    }
    
    private function sendMailUser($oInfo){
        $oCommon = new CommonController();
        $siteURL = $oCommon->siteURL();
		$to = trim($oInfo['email']);
		$username = $oInfo['username'];
		$password = $oInfo['password'];
		$subject = "[Zelvin] User Information";
        $bodytext = "Please use this infomation to access to Zelvin's website (". $siteURL . "/zelvin) as below: \n Username: $username \n Password: $password\nPlease change your password after the first time logon to the system.";
        $this->_CommonController->sendMail($to, $subject, $bodytext);
    }
    
    private function getDataStatus($request, $username, $function_id){
        $oFunctionUser = new Application_Model_Functionuser();
        $oFunctionDetail = $oFunctionUser->loadDetailFunction($function_id);
        $function_name = $oFunctionDetail->name;
        $data = array();
        $data['username'] = $username;
        $data['function_id'] = $function_id;
        $data['f_insert'] = $request->getPost('chk_insert_'.$function_name, 0);
        $data['f_update'] = $request->getPost('chk_update_'.$function_name, 0);
        $data['f_delete'] = $request->getPost('chk_delete_'.$function_name, 0);
        $data['f_import'] = $request->getPost('chk_import_'.$function_name, 0);
        $data['f_export'] = $request->getPost('chk_export_'.$function_name, 0);
        $data['f_view'] = $request->getPost('chk_view_'.$function_name, 0);
		return $data;
    }
    
    public function changepassAction()
    {
    	// action body
        $request = $this->getRequest();
        $form    = new Application_Form_Password();
        try {
        	if ($this->getRequest()->isPost()) {
        		if ($form->isValid($request->getPost())) {
        			//get post values
        			$submitbutton = $request->getPost('submitbutton', null);
        			//get search param
        			if ($submitbutton=='Save'){
        			    $password = $request->getPost('password', null);
        				$new_password = $request->getPost('new_password', null);
        				if (!$this->_CommonController->checkPolicyPassword($new_password)) {
	        				$oUser = new Application_Model_User();
	        				$oDetail = $oUser->changePass($password);
	        				if ($oDetail) {
	        				    $where['username = ?'] = $this->_username;
	        				    $data['password'] = md5($new_password);
	        				    $data['active_password'] = 1;
	        				    $oUser->update($data, $where);
	        					$this->_redirect('/index/index');
	        					return false;
	        				} else {
	        					$form->password->addError(str_replace('xxx', 'Old password \'' . $password . '\'', INCORRECT_MSG));
	        				}
        				} else {
        				    $form->new_password->addError(str_replace('xxx', '\''.$new_password.'\'', PASSWORD_INVALID_MSG));
        				}
        			}
        		}
        	}
        } catch (Exception $e) {
            // pass possible exceptions to log file
            $this->_CommonController->writeLog($e->getMessage());
        }
        $this->view->form = $form;
    }
    
    public function adchangepassAction()
    {
    	// action body
        $request = $this->getRequest();
    	$formUser    = new Application_Form_User();
    	$form    = new Application_Form_Adchangepass();
    	$oUser = new Application_Model_User();
    	$uid = $this->_getParam("uid", null);
        try {
        	if ($this->getRequest()->isPost()) {
        		if ($form->isValid($request->getPost())) {
        		//get post values
    				$submitbutton = $request->getPost('submitbutton', null);
    				//get search param
    				if ($submitbutton=='Save'){
    					$new_password = $request->getPost('new_password', null);
    					if (!$this->_CommonController->checkPolicyPassword($new_password)) {
    					    $infoUser = $this->_CommonModel->loadInfoUser();
    						if ($infoUser->permission_type==ADMIN){
								$where['md5(user_id) = ?'] = $this->_CommonController->decodeKey($uid);
        				        $data['password'] = md5($new_password);
        				        //set the first time
        				        $data['active_password'] = 0;
        				        //reset logon time
        				        $data['count_time'] = 0;
        				        //unlock user
        				        $data['checked_user'] = 1;
        				        $oUser->update($data, $where);
        				        //get user info
        				        $oUserDetail = $this->_CommonModel->loadDetail('users', 'md5(user_id)', $this->_CommonController->decodeKey($uid));
        				        //send mail
        				        $data = array();
        				        $data['email'] = $oUserDetail->email;
        				        $data['username'] = $oUserDetail->username;
        				        $data['password'] = $new_password;
        				        $this->sendMailUser($data);
        				        $this->_redirect('/user/index');
        				        return false;
							}
    					} else {
    						$form->new_password->addError(str_replace('xxx', '\''.implode(' ', $this->_CommonController->arrSpecialChars).'\'', PASSWORD_INVALID_MSG));
    					}
    				}
        		}
        	}
        	$oDetail = $oUser->loadDetail($uid);
        	$formUser->getElement('username')->setValue($oDetail->username)->setAttribs(array('readonly' => true));
        	$formUser->getElement('email')->setValue($oDetail->email)->setAttribs(array('readonly' => true));
        	$formUser->getElement('address')->setValue($oDetail->address)->setAttribs(array('readonly' => true));
        	$formUser->getElement('phone')->setValue($oDetail->phone)->setAttribs(array('readonly' => true));
        	$formUser->getElement('permission_type_view')->setValue($oDetail->permission_type==1?'Admin':'Agent')->setAttribs(array('readonly' => true));
        } catch (Exception $e) {
            // pass possible exceptions to log file
            $this->_CommonController->writeLog($e->getMessage());
        }
        $this->view->form = $form;
        $this->view->formUser = $formUser;
        $this->view->uid = $uid;
    }

}

