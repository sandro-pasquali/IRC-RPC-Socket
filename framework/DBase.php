<?php

/**
 * DBase.php
 * 
 * @author		Sandro Pasquali 	sandropasquali@yahoo.com
 * @version 	0.09 alpha			
 */
class DBase extends Assembly
  {
    function __construct()
	  {
	    $this->readID = null;
		$this->writeID = null;
		$this->parallelID = null;
	  }
	  
	function query()
	  {
	  
	  }
	  
	function unbufferedQuery()
	  {
	  
	  }
	
	function isParallelID()
	  {
	    return($this->readID != null);
	  }
	  
	function isReadID()
	  {
	    return($this->readID != null);
	  }
	  
	function isWriteID()
	  {
	    return($this->writeID != null);
	  }
	
	function readConnect($username,$password,$dbaseName,$dbaseAddress)
	  {
	    try
		  {
		    if($this->isReadID())
			  {
			    @mysql_close($this->readID);
			  }
			  
            if(!$this->readID = @mysql_connect($dbaseAddress,$username,$password))
			  {
			    throw new DbaseException("Unable to connect on READ");
			  }
			  
			if(!$this->parallelID = @mysql_connect($dbaseAddress,$username,$password,true))
			  {
			    throw new DbaseException("Failed to create parallelID");
			  }
			  
            if(!@mysql_select_db($dbaseName,$this->readID))
			  {
				throw new DbaseException("Unable to select on READ [".$dbaseName."]");
			  }
          }
        catch(DbaseException $exception)
          {
            $exception->log();
			return(false);
          }
		return(true);
	  }

	function writeConnect($username,$password,$dbaseName,$dbaseAddress)
	  {
	    try
		  {
		    if($this->isWriteID())
			  {
			    @mysql_close($this->writeID);
			  }
			  
            if(!$this->writeID = @mysql_connect($dbaseAddress,$username,$password))
			  {
			    throw new DbaseException("Unable to connect on WRITE");
			  }
			  
            if(!@mysql_select_db($dbaseName,$this->writeID))
			  {
				throw new DbaseException("Unable to select on WRITE [".$dbaseName."]");
			  }
          }
        catch(DbaseException $exception)
          {
            $exception->log();
			return(false);
          }
		return(true);
	  }
  }  
  
?>