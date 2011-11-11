<?php 

// authentication information file
include("access.php");

$auth = false; 

if(isset($PHP_AUTH_USER) && isset($PHP_AUTH_PW)) 
  { 
    try
	  {
        if($_authenticationList[$PHP_AUTH_USER] == $PHP_AUTH_PW)
	      {
	        $auth = true;
	      }
		throw new Exception("");
      } 
	catch(Exception $exception)
	  {;}
  }
if(!$auth) 
  { 
    header( 'WWW-Authenticate: Basic realm="Private"' ); 
    header( 'HTTP/1.0 401 Unauthorized' ); 
    echo 'Authorization Required.'; 
    exit; 
  } 

?> 
