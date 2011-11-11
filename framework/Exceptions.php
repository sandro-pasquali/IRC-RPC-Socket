<?php
/**
 * Exceptions.php
 * 
 * @author		Sandro Pasquali 	sandropasquali@yahoo.com
 * @version 	0.09 alpha			
 */
class _Exception extends Exception
  {
	function log()
	  {
	    global $System;
	    $System->log("EX|".$this->exception
		                  ."|".$this->getTraceAsString()
						  ."|".$this->getFile()."(".$this->line.")");
	  }
  }  

class DbaseException extends _Exception
  {
    function __construct($exception = "error!")
	  {
	    $this->exception = "DB|".$exception;
	  }
  }  
  
class RPCException extends _Exception
  {
    function __construct($exception = "error!")
	  {
	    $this->exception = "PC|: ".$exception;
	  }
  } 

?>
