<?php
/**
 * common classes
 * **/
class CommonController
{
    protected  $_ext = "zip";
    protected $_arrSpecialChars = array('%');
    protected $_arrReplacementChars = array("\\%");
    
    public $arrSpecialChars = array('!', '"', '#', '$', '%', '&', '\'', '(', ')', '*', '+', ',', '-', '.', '/', ':', ';', '<', '=', '>', '?', '@', '[', '\\', ']', '^', '_', '`', '{', '|', '}', '~');

    /**
     * load user logon
     * **/
    public function loadUserLogon(){
        $auth = Zend_Auth::getInstance();
        if ($auth->hasIdentity()) {
        	$infoUser = $auth->getIdentity();
        	return $infoUser;
        }
        return null;
    }
    
    /**
     * check logon
     * @param: none
     * @return:  0: not logon
     * 			 1: first time - need to change password
     * 			 2: ok
     * **/
    public function checkLogon(){
      
        $request = new Zend_Controller_Request_Http();
        $tk = $request->getCookie('token');
        $auth = Zend_Auth::getInstance();
        if (!$auth->hasIdentity()) {
	    	return 0;
        }
        $infoUser = $this->loadUserLogon();
        if ($infoUser->client_ip != Zend_Controller_Front::getInstance()->getRequest()->getClientIp()) {
            return 0;
        }
        if ($tk==null ||  $infoUser->token!=$tk) {
            return 0;
        }
        $oUser = new Application_Model_User();
        $userDetail = $oUser->loadDetail($this->encodeKey($infoUser->user_id));
        if (isset($userDetail->active_password) && $userDetail->active_password==0) {
            return 1;
        }
        return 2;
    }

    /**
     * check permission
     * **/
    public  function checkPermission($funUser, $funtion_type, $url=null){
        $bReturn = false;
        $oFunUser= Zend_Registry::get($funUser);
        if ($oFunUser[$funtion_type]==1){
            $bReturn = true;
        }
        if ($url!=null && $bReturn==false){
           	header('Location: ' . '/zelvin' . $url);
        }
        return $bReturn;
    }
    
    /**
     * get functions of an user
     * **/
    public function getFunctionUser($function_id){
        $oCommonModel = new Application_Model_Common();
        $listFunUser = $oCommonModel->loadFunctionUser();
        foreach ($listFunUser as $value) {
            if ($value['function_id']==$function_id) {
                return $value;
            }
        }
        return null;
    }
    
    /**
     * detroy a session
     * **/
    public function detroySession($sess_name){
    	Zend_Session::start();
    	if (Zend_Session::namespaceIsset($sess_name)):
    		Zend_Session::namespaceUnset($sess_name);
    	endif;
    }
    
    /**
     * export page
     * **/
    public function exportExcel($data, $title, $filename)
    {
    	require_once 'Spreadsheet/Excel/Writer.php';
    	//file name
    	$fileName = date('YmdHis').'_'.$filename.'.xls';
    	// Creating a workbook
    	$workbook = new Spreadsheet_Excel_Writer(exportdir.$fileName);
    	// Creating a worksheet
    	$worksheet =& $workbook->addWorksheet($title);
    	
		$iRow = 0;
    	foreach ($data as $row=>$item) {
    		$iCol = 0;
    	    foreach ($item as $col=>$value) {
    	    	$worksheet->write($iRow, $iCol, $value);
				$iCol++;
    	    }
    	    $iRow++;
    	}    	
    	// Let's send the file
    	$workbook->close();   	
    	$fName = substr($fileName, 0, strrpos($fileName, '.'));
    	$zipFile = $fName . '.zip';
    	exec('zip -j -r ' . exportdir.$fName . '.zip ' . exportdir.$fileName);
    	// http headers for zip downloads
    	header("Pragma: public");
    	header("Expires: 0");
    	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    	header("Cache-Control: public");
    	header("Content-Description: File Transfer");
    	header("Content-type: application/octet-stream");
    	header("Content-Disposition: attachment; filename=\"".$zipFile."\"");
    	header("Content-Transfer-Encoding: binary");
    	header("Content-Length: ".filesize(exportdir.$zipFile));
    	ob_end_flush();
    	@readfile(exportdir.$zipFile);
    	exit;
    }
    
    /**
     * create new/edit data of a project
     * **/
    public function saveProject($request, $form) {
    	$oProject = new Application_Model_Project();
    	//get post values
    	$submitbutton = $request->getPost('submitbutton', null);
    	$bError = false;
    	//get search param
    	if ($submitbutton=='Save'){
    	    $client_id = $request->getPost('client_code', null);
    		$project_name = $request->getPost('project_name', null);
    		$start_date = $request->getPost('start_date', null);
    		$date = date_create($start_date);
    		$start_date_string = $date->format('Ymd');
    		$user_create = Zend_Filter::filterStatic($request->getPost('user_create', null), 'StripTags');
    		$description = Zend_Filter::filterStatic($request->getPost('description', null), 'StripTags');
    		if (strlen($description)>900) {
    			$form->description->addError(str_replace('xxx', '\'' . substr($description, 0, 30) . '...\'', MAX_LEN));
    			$bError = true;
    		}
    		$data = array('client_id' => $client_id, 'project_name' => $project_name, 'start_date' => $start_date_string, 'description' => $description, 'user_mod' => $user_create, 'mod_date' => date('YmdHis'));
    		//check insert or update
    		$pid = $request->getPost('pid', null);
    		$old_client_code = $request->getPost('old_client_code', null);
    		$old_project_name = $request->getPost('old_project_name', null);
    		if (($old_client_code!=null && $old_project_name!=null 
    		        && ($old_client_code!=$this->encodeKey($client_id) 
    		        || $old_project_name!=$project_name)) 
    		        || ($old_client_code==null && $old_project_name==null)) {
    			//check duplicate
    			if (!$this->checkDuplicate('project', 'client_id', $client_id, 'project_name', $project_name)) {
    				$form->project_name->addError(str_replace('xxx', 'project name', EXISTED_MSG));
    				$bError = true;
    			}
    		}
    		if ($bError==false) {
	    		//insert
	    		if ($pid=="") {
	    			$data['user_create'] = $user_create;
	    			$data['create_date'] = date('YmdHis');
	    			//insert data to project table
	    			$oProject->insert($data);
	    			$form->reset();
	    			//update
	    		} else {
	    			$where['md5(project_id) = ?'] = $this->decodeKey($pid);
	    			$oProject->update($data, $where);
	    		}
    		}
    	}
    	return $bError;
    }
    
    /**
     * create new/ edit data of a client
     * **/
    public function saveClient($request, $form) {
    	$oClient = new Application_Model_Client();
    	//get post values
    	$submitbutton = $request->getPost('submitbutton', null);
    	$bError = false;
    	//get search param
    	if ($submitbutton=='Save'){
    		$client_name = Zend_Filter::filterStatic($request->getPost('client_name', null), 'StripTags');
    		$email = Zend_Filter::filterStatic($request->getPost('email', null), 'StripTags');
    		$address = Zend_Filter::filterStatic($request->getPost('address', null), 'StripTags');
    		$phone = Zend_Filter::filterStatic($request->getPost('phone', null), 'StripTags');
    		$data = array('client_name' => $client_name, 'email' => $email, 'address' => $address, 'phone' => $phone);
    		$old_client_name = Zend_Filter::filterStatic($request->getPost('old_client_name', null), 'StripTags');
    		if (($old_client_name!=null && $old_client_name!=$client_name) || $old_client_name==null) {
    		    //check duplicate
    		    if (!$this->checkDuplicate('client', 'client_name', $client_name)) {
    		        $form->client_name->addError(str_replace('xxx', 'client name', EXISTED_MSG));
    		        $bError = true;
    		    }
    		}
    		if ($bError==false) {
	    		//check insert or update
	    		$cid = $request->getPost('cid', null);
	    		//insert
	    		if ($cid=="") {
	    			//insert data to client table
	    			$oClient->insert($data);
	    			$form->reset();
	    			//update
	    		} else {
	    			$where['md5(client_id) = ?'] = $this->decodeKey($cid);
	    			$oClient->update($data, $where);
	    		}
    		}
    	}
    	return $bError;
    }   
    
    /**
     * load list of projects
     * **/
    public function loadProjectList($request, $form, $currentPage, $countperpage, $uname=null, $cid=null) {
        $oProject = new Application_Model_Project();
        $hidDel = Zend_Filter::filterStatic($request->getPost('hidDel', null), 'StripTags');
        //delete items are selected
        if ($hidDel!=""){
        	$oProject->deleteItems($hidDel);
        }
        //get post values
        $arrCondition = array();
        //store search param in session for pagination
        $search = new Zend_Session_Namespace('psearch');
        //get search param
        $buttonsearch = Zend_Filter::filterStatic($request->getPost('buttonsearch', null), 'StripTags');
        if ($buttonsearch=='Search'){
        	$client_name_search = Zend_Filter::filterStatic($request->getPost('client_name_search', null), 'StripTags');
        	$project_name_search = Zend_Filter::filterStatic($request->getPost('project_name_search', null), 'StripTags');
        	$start_date_search = Zend_Filter::filterStatic($request->getPost('start_date_search', null), 'StripTags');
        	$arrCondition = array('client_name' => $client_name_search, 'project_name' => $project_name_search, 'start_date' => $start_date_search);
        	$search->psearch = $arrCondition;
        }else{
        	$arrCondition = $search->psearch;
        }
        $arrCondition['cid'] = $cid;
        $arrCondition['username'] = $uname;
        //set search values to search box
        $form->getElement('client_name_search')->setValue(isset($arrCondition['client_name'])?$arrCondition['client_name']:'');
        $form->getElement('project_name_search')->setValue(isset($arrCondition['project_name'])?$arrCondition['project_name']:'');
        $form->getElement('start_date_search')->setValue(isset($arrCondition['start_date'])?$arrCondition['start_date']:'');
        // Object of Zend_Paginator
        $result = $oProject->loadList($arrCondition);
        $paginator = Zend_Paginator::factory($result);
        // set the number of counts in a page
        $paginator->setItemCountPerPage($countperpage);
        // set the current page number
        $paginator->setCurrentPageNumber($currentPage);
        return $paginator;
    }
    
    /**
     * check duplicate data of a table
     * **/
    public function checkDuplicate($tablename, $fieldname1, $value1, $fieldname2="", $value2="") {
        $oCommonModel = new Application_Model_Common();
        $result = $oCommonModel->checkDuplicate($tablename, $fieldname1, $value1, $fieldname2, $value2);
        if (!$result) {
            return true;
        }
        return false;
    }
    
    /**
     * send an email
     * **/
    public function sendMail($to, $subject, $bodytext){
	    try {
	        $tr = new Zend_Mail_Transport_Smtp(mail_server, 
	                							array(
		            	    			    		'username' => mail_username,
		            	    			    		'password' => mail_password,
		            	    			    		'port'     => mail_port
	                							)
			);
	        Zend_Mail::setDefaultTransport($tr);
	        $mail = new Zend_Mail ();
	        $mail->setFrom ('root@localhost', 'Zelvin');
	        $mail->addTo ($to);
	        $mail->setSubject ($subject);
	        $mail->setBodyText ($bodytext);
	        return $mail->send();
        } catch (Exception $e) {
        	// pass possible exceptions to log file
        	$this->writeLog($e->getMessage());
        	return false;
        }
    }
    
    /**
     * random a string data
     * **/
    public function generateRandomString($length = 10) {
    	$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ!@#$%^&*(){}[]';
    	$randomString = '';
    	for ($i = 0; $i < $length; $i++) {
    		$randomString .= $characters[rand(0, strlen($characters) - 1)];
    	}
    	return $randomString;
    }
    
    /**
     * get files of a folder
     * **/
    public function getFilesInFolder($dir) {
        $files = array();
        if ($handle = opendir($dir)) {
        	while (($file = readdir($handle)) !== false) {
        		if ($file !== '.' && $file !== '..') {
        			$files[] = $file; 
        		}
        	}
        	closedir($handle);
        }
        return $files;
    }
    
    /**
     * detect files are imported
     * **/
    public function detectImportFileName($pathFileName){
        $ext = end(explode('.', $pathFileName));
        if ($ext==$this->_ext){
        	$fileName = end(explode('/', $pathFileName));
        	$fName = substr($fileName, 0, strrpos($fileName, '.'));
        	$pathFolder = FILE_UPLOAD_DESTINATION . '/' . $fName;
        	exec('unzip ' . $pathFileName . ' -d ' . $pathFolder);
        	$arrFiles = $this->getFilesInFolder($pathFolder);
        	$importFile = $pathFolder . '/' . end($arrFiles);
        	$this->deleteAFile($pathFileName);
        }else {
        	$importFile = $pathFileName;
        }
        return $importFile;
    }
    
    /**
     * delete a file
     * **/
    public function deleteAFile($pathFileName){
    	if (file_exists($pathFileName)) {
    	    unlink($pathFileName);
    	    return true;
    	}
    	return false;
    }
    
    /**
     * delete a folder
     * **/
    public function deleteDirectory($dirname) {
    	if (is_dir($dirname)){
    		$dir_handle = opendir($dirname);
    	}
    	if (!$dir_handle){
    		return false;
    	}
    	while($file = readdir($dir_handle)) {
    		if ($file != "." && $file != "..") {
    			if (!is_dir($dirname."/".$file)){
    				unlink($dirname."/".$file);
    			}else{
    				$this->deleteDirectory($dirname.'/'.$file);
    			}
    		}
    	}
    	closedir($dir_handle);
    	rmdir($dirname);
    	return true;
    }
    
    /**
     * check to delete files are imported
     * **/
    public function checkToDeleteImportFile($pathFileName, $importFile){
        if (pathinfo($pathFileName, PATHINFO_EXTENSION)=='zip') {
        	$pathInfoDirName = pathinfo($importFile, PATHINFO_DIRNAME);
        	$this->deleteDirectory($pathInfoDirName);
        }
        $this->deleteAFile($importFile);
    }
    
    /**
     * write log
     * **/
    public function writeLog($errorMsg) {
        $logger = new Zend_Log();
		$writer = new Zend_Log_Writer_Stream(logfile);
		$logger->addWriter($writer);
		# log into FirePHP console
		$logger->log($errorMsg, Zend_Log::ERR);
    }

    /**
     * check and get an url
     * **/
    public function siteURL(){
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $domainName = $_SERVER['HTTP_HOST'];
        $siteURL = $protocol.$domainName;
        return $siteURL;
    }
    
    /**
     * replace special characters
     * **/
    public function replaceSpecialChars($strData){
        $strReturn = '';
		$strReturn = str_replace($this->_arrSpecialChars, $this->_arrReplacementChars, $strData);
		return $strReturn;
    }
    
    /**
     * encode a key
     * **/
    public function encodeKey($key){
        if ($key!='') {
        	return md5($key) . Zend_Session::getId();
        }
        return '';
    }
    
    /**
     * decode a key
     * **/
    public function decodeKey($key){
        if ($key!='') {
            $intPos = strpos($key, Zend_Session::getId());
            return substr($key, 0, $intPos); 
        }
        return '';
    }
    
    /**
     * check policy of a password
     * **/
    public function checkPolicyPassword($strPass){
        $error = false;
        $bChkSpecialChar = true;
        $arrPass = str_split($strPass);
        for ($i=0; $i<count($arrPass); $i++) {
            if (in_array($arrPass[$i], $this->arrSpecialChars)) {
                $bChkSpecialChar = false;
                break;
            }
        }
        $error = $bChkSpecialChar;
        if(!preg_match('/[a-zA-Z]+/',$strPass)){
            $error = true;
        }
        if(!preg_match('/[0-9]+/',$strPass)){
        	$error = true;
        }
        return $error;
    }
    
    /**
     * cut a string data
     * **/
    public function cutAString($string, $length = 30){
        $charset = 'utf-8';
        mb_internal_encoding('utf-8');
        if(mb_strlen($string, $charset) > $length) {
        	$string = mb_strcut($string, 0, $length, $charset) . '...';
        }
        return $string;
    }
    
    /**
     * detect a risk level
     * **/
    public function detectRickLevel($risk_level){
        $strResult = "";
    	switch ($risk_level) {
    	    case 3:
    	        $strResult = "High"; 
    	        break;
    	    case 2:
    	      	$strResult = "Medium";
    	       	break;
    	    case 1:
    	     	$strResult = "Low";
    	       	break;
    	    default:
    	        $strResult = "Issue";
    	        break;
    	}
    	return $strResult;
    }
}
?>
