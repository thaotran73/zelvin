<?php
include_once APPLICATION_PATH . '/controllers/CommonController.php';
class IndexController extends Zend_Controller_Action
{
    protected $_CommonController = null;
    
    public function preDispatch() {
        if ($this->_CommonController->checkLogon()==1 && $this->getRequest()->getActionName()!='logout'){
        	$this->_redirect('/user/changepass');
        }
    }
    
    public function init()
    {
        /* Initialize action controller here */
        $this->_CommonController = new CommonController();
    }
    
    /**
     * login action
     * **/
    public function indexAction()
    {	
        // action body
        $request = $this->getRequest();
        $form    = new Application_Form_Index();
        $this->view->error = "";
        try {       
            //init Zend Autho
            $auth = Zend_Auth::getInstance ();
            //check if loged on, redirect to client page 
            if ($auth->hasIdentity()) {
           		$this->_redirect('/client/index');
			}
            if ($this->getRequest()->isPost()) {
            	if ($form->isValid($request->getPost())) {
            	    $uname = Zend_Filter::filterStatic($request->getPost('username', null), 'StripTags');
            	    $paswd = $request->getPost('password', null);
            	    $oUser = new Application_Model_User();
            	    $oUserDetail = $oUser->loadDetailFromUsername($uname);
            	    $flag = $oUser->login($uname, $paswd);
            	    if ($flag == true) {
            	        if ($oUserDetail->checked_user==0) {
//             	            $auth = Zend_Auth::getInstance();
            	            $auth->clearIdentity();
            	            //detroy fuser session
            	            $this->_CommonController->detroySession('fuser');
            	        	$this->view->error = str_replace('xxx', $uname, USERNAME_LOCKED_MSG);
            	        }else {
            	            $data = array();
            	            $data['count_time'] = 0;
            	            $where['username = ?'] = $uname;
            	            $oUser->update($data, $where);
            	    		$this->_redirect('/client/index');
            	        }
            	    }else {
            	        $count_time = $oUserDetail->count_time;
            	        $data = array();
            	        $data['count_time'] = $count_time + 1;
            	        if ($count_time<5) {
	           	        	$this->view->error = USERNAME_PASSWORD_INCORRECT_MSG;
            	        }else {
            	            $data['checked_user'] = 0;
            	            $this->view->error = str_replace('xxx', $uname, USERNAME_LOCKED_MSG);
            	        }
            	        $where['username = ?'] = $uname;
            	        $oUser->update($data, $where);
            	    }
            	}
            }
        } catch (Exception $e) {
            // pass possible exceptions to log file
            $this->_CommonController->writeLog($e->getMessage());
        }
        $this->view->form = $form;
    }

	public function logoutAction() {
	    $auth = Zend_Auth::getInstance();
	    $auth->clearIdentity();
	    //detroy fuser session
	    $oCommon = new CommonController();
	    $oCommon->detroySession('fuser');
	    unset($_COOKIE['token']);
	    setcookie('token', ' ');
	    $this->_redirect('/index/index');
	}
	
	/**
	 * forgot password action
	 * **/
	public function forgotpassAction()
	{
		// action body
		$request = $this->getRequest();
		$form    = new Application_Form_Forgotpass();
		try {
			if ($this->getRequest()->isPost()) {
				if ($form->isValid($request->getPost())) {
					//get post values
					$submitbutton = Zend_Filter::filterStatic($request->getPost('submitbutton', null), 'StripTags');
					//get search param
					if ($submitbutton=='Save'){
						$email = Zend_Filter::filterStatic($request->getPost('email', null), 'StripTags');
						$oUser = new Application_Model_User();
						$oDetail = $oUser->forgotPass($email);
						if ($oDetail) {
						    $where['username = ?'] = $oDetail->username;
						    $new_password = $this->_CommonController->generateRandomString();
						    $data['password'] = md5($new_password);
						    $oUser->update($data, $where);
						    $to = $email;
						    $username = $oDetail->username;
						    $password = $new_password;
						    $subject = "[Zelvin] Forgot Password";
						    $bodytext = "Dear $username, \n You recently requested that your Zelvin account password be reset.\n The new temporary password for your account is: $password";
						    $this->_CommonController->sendMail($to, $subject, $bodytext);
						    $form->email->addError(EMAIL_NOT_EXISTED);
// 						    $this->_redirect('/index/index');
						} else {
// 							$form->email->addError(str_replace('xxx', $email, NOT_EXISTED_MSG));
							$form->email->addError(EMAIL_NOT_EXISTED);
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
    
}

