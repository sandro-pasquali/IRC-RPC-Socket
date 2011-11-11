<?php

/**
 * DateTime.php
 * 
 * @author		Sandro Pasquali 	sandropasquali@yahoo.com
 * @version 	0.09 alpha			
 */
class DateTime extends Assembly
  {
    function __construct()
	  {
	  }
	  
	public function getStamp($typ="")
	  {
	    switch($typ)
		  {
		    case "base":
	          return(date("YmdHis"));
			break;
			
			case "log":
			  // exists in nusoap.php
			  return(timestamp_to_iso8601(time()));
			break;
			
			default:
			  return(time());
			break;
	      }
	  } 
  }  
  
?>