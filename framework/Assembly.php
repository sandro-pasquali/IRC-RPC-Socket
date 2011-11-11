<?php

/**
 * Assembly.php
 * 
 * @author		Sandro Pasquali 	sandropasquali@yahoo.com
 * @version 	0.09 alpha			
 */
class Assembly
  {
    protected function __construct()
	  {
		$this->errorLog = null;
        $this->xPath = $this->getXPath();
	  }
	  
    protected function System()
	  {
	  	global $System;
		return($System);
	  }
	  
	public function startLog($errLog = "logs/log.log")
	  {
	    $this->errorLog = $errLog;
	  }
	  
	protected function getXPath()
	  {
	  	$xmlOptions = array(XML_OPTION_CASE_FOLDING => TRUE, XML_OPTION_SKIP_WHITE => TRUE);
        return(new XPath(FALSE, $xmlOptions));
	  }
	  
	protected function setXPathOnFile($xml)
	  {
		$this->xPath->reset();
        if(!$this->xPath->importFromFile($xml)) 
		  { 
		    $this->log($this->xPath->getLastError());
			return(false);
		  }
		return(true);
	  }	
	
	protected function setXPathOnString($xml)
	  {
		$this->xPath->reset();
        if(!$this->xPath->importFromString($xml)) 
		  { 
		    $this->log($this->xPath->getLastError());
			return(false);
		  }
		return(true);
	  }  
	  
	public function log($msg="<null>")
	  {
	    if($this->errorLog)
		  {
	        error_log("[".$this->System()->DateTime->getStamp("log")."] ".$msg."\n", 3, $this->errorLog);
		  }
	  }
  }
  
/**
 * System.php
 * 
 * @author		Sandro Pasquali 	sandropasquali@yahoo.com
 * @version 	0.09 alpha			
 */
class System extends Assembly
  {
    function __construct()
	  {
	    parent::__construct();
	  }
	  
	public function attach($lib)
	  {
		eval('$this->'.$lib.' = new '.$lib.'();');
	  }
  } 

?>
