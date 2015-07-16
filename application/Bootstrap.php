<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    
    protected function _intSession()
    {
        $myVars = $this->getOption('myvars');
        $expireTime = $myVars['auth_timeout'];
        ini_set('session.use_cookies', 1);
        ini_set('session.use_only_cookies', 1);
        ini_set('session.gc_maxlifetime', $expireTime);
        ini_set('session.cookie_lifetime', $expireTime);
    }
    
    protected function _initDoctype()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');
        $view->doctype('XHTML1_STRICT');
    }
    
	protected function _initViewHelpers()
	{
		$view = new Zend_View();
		$viewRenderer = new Zend_Controller_Action_Helper_ViewRenderer();
	 
		$view->addHelperPath('ZendX/JQuery/View/Helper/', 'ZendX_JQuery_View_Helper');
		$viewRenderer->setView($view);
		Zend_Controller_Action_HelperBroker::addHelper($viewRenderer);
	}
	
	protected function _initVars()
	{
		$options = $this->getOption('myvars');
		if (is_array($options)) {
			foreach($options as $key => $value) {
				if(!defined($key)) {
					define($key, $value);
				}
			}
		}
	}
    
    protected function _initConstants()
    {
    	$options = $this->getOption('constants');
    	if (is_array($options)) {
    		foreach($options as $key => $value) {
    			if(!defined($key)) {
    				define($key, $value);
    			}
    		}
    	}
    }
    
    protected function _initConstantsMsg()
    {
    	$sqlTablesFile = APPLICATION_PATH . '/configs/constants.ini';
    	$iniParser = new Zend_Config_Ini($sqlTablesFile);
    	$constants = $iniParser->toArray();
    	if (is_array($constants)) {
    		foreach($constants as $key => $value) {
    			if(!defined($key)) {
    				define($key, $value);
    			}
    		}
    	}
    }
    
    protected function _initMail()
    {
    	$options = $this->getOption('mail');
    	if (is_array($options)) {
    		foreach($options as $key => $value) {
    			if(!defined($key)) {
    				define($key, $value);
    			}
    		}
    	}
    }
    
    protected function _initMakeFileUploadConstant() 
    {
    	$myVars = $this->getOption('myvars');
    	$uploadDir = realpath($myVars['fileuploaddir']);
    	defined('FILE_UPLOAD_DESTINATION') || define('FILE_UPLOAD_DESTINATION', $uploadDir);
    }
    
    protected function _initAutoload()
    {
    	// instantiate the loader
		$loader = Zend_Loader_Autoloader::getInstance();
		
		// specify class namespaces you want to be auto-loaded.
		// 'Zend_' and 'ZendX_' are included by default
		$loader->registerNamespace('Excel_');
		
		// optional argument if you want the auto-loader to load ALL namespaces
		$loader->setFallbackAutoloader(true);
		$loader->suppressNotFoundWarnings(false);
		        
    
    }
}

