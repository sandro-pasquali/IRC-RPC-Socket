<?php
/**
* getUserKey.
*
* @param     $username 		(string)	Default:(string)""
* @example /usr/local/apache/htdocs/sandro/sockets/generatedFiles/privateMethods.xml.rpcExamples.html
*/
function getUserKey() {
    global $System;$_argv = func_get_arg(0);$_slot = array_shift($_argv);$username= (isset($_argv[0])) ? $_argv[0] : (string)"";if(is_string($username)==false) { return(false); }if(isset($_slot) &amp;&amp; isset($username) &amp;&amp; $System->Socket->clients[$_slot]['level'] == 0) { $System->Socket->clients[$_slot]['username'] = $username; /* * send challenge as RPC packet */ $challenge = array('challenge',array($System->Socket->getChallenge($_slot))); $System->Socket->sendData($_slot,XMLRPC_methodResponse($challenge)); } else { return(false); }
  }

/**
* checkPassword.
*
* @param     $challenge 		(string)	Default:(string)""
* @example /usr/local/apache/htdocs/sandro/sockets/generatedFiles/privateMethods.xml.rpcExamples.html
*/
function checkPassword() {
    global $System;$_argv = func_get_arg(0);$_slot = array_shift($_argv);$challenge= (isset($_argv[0])) ? $_argv[0] : (string)"";if(is_string($challenge)==false) { return(false); }if(isset($_slot) &amp;&amp; isset($challenge) &amp;&amp; $System->Socket->clients[$_slot]['level'] == 0) { $q = "SELECT password,site_skin from user_accounts WHERE username = '".$System->Socket->clients[$_slot]['username']."'"; $result = mysql_query($q,$System->Dbase->readID); /* * is there a password for this username? */ if(mysql_num_rows($result) > 0) { /* * password will be $creds[0] */ $creds = mysql_fetch_array($result); if(sha1($System->Socket->getChallenge($_slot).$creds[0]) == $challenge) { /* * all good; log in user (slot,userlevel,lifespan,skin) */ $System->Socket->loginUser($_slot,1,"session",$creds[1]); /* * attach methodcall name */ $aResp = array('authenticate',array('authOK')); /* * send response */ return($System->Socket->sendData($_slot,XMLRPC_methodResponse($aResp))); } mysql_free_result($result); } /* if no parameters set, or authentication fails * send bad login. */ $aResp = array('authenticate',array('authFAIL')); return($System->Socket->sendData($_slot,XMLRPC_methodResponse($aResp))); } else { return(false); }
  }

?>
