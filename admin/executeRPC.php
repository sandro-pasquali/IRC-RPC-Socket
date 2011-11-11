<?php 

include("../access/authentication.php"); 

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title></title>
<meta http-equiv="Pragma" content="no-cache" />
<meta http-equiv="Expires" content="-1" />
<meta http-equiv="Content-type" content="text/html; charset=iso-8859-1" />
<meta http-equiv="Content-Language" content="en-us" />
<meta name="ROBOTS" content="ALL" />
<meta name="Copyright" content="Copyright (c) Jeebus" />
<meta http-equiv="imagetoolbar" content="no" />
<meta name="MSSmartTagsPreventParsing" content="true" />

<style type="text/css">

BODY
  {
    font-family: Verdana, Arial;
	font-size: 11px;
	color: black;
	background-color: #ccffff;
  }

</style>

</head>
<body>

<?php

include("../includes/authentication.php");

if(!isset($methodCall)) { exit; }

require_once("../../framework/libs/xmlrpc.php");
require_once("../../framework/libs/XPath.class.php");

// set up xpath object
$xmlOptions = array(XML_OPTION_CASE_FOLDING => TRUE, XML_OPTION_SKIP_WHITE => TRUE);
$xPath =& new XPath(FALSE, $xmlOptions);

$methodCall = "admin";

/*
 * package username and password 
 */
$params[0] = XMLRPC_prepare($PHP_AUTH_USER);
$params[1] = XMLRPC_prepare($PHP_AUTH_PW);

$postedData = $_POST;

while(list($f,$v) = each($postedData))
  {
    $params[] = XMLRPC_prepare($v);
  }  
  
$data["methodCall"]["methodName"] = $methodCall;
$param_count = count($params);
if(!$param_count)
  {
	$data["methodCall"]["params"] = NULL;
  }
else
  {
	for($n = 0; $n<$param_count; $n++)
	  {
		$data["methodCall"]["params"]["param"][$n]["value"] = $params[$n];
	  }
  }

$data = XML_serialize($data);

$resp = "";

$fp = stream_socket_client("tcp://63.110.38.195:9000", $errno, $errstr);

if(!$fp) 
  {
    $resp = "$errstr ($errno)<br />\n";
  } 
else 
  {
    $resp = '<?xml version="1.0"?>';
    fputs($fp, $data);
	
    $resp .= stream_get_line($fp, 27000, '</methodResponse>');
	
	//print "<p>term:".substr($resp, -17)."<p>";
	
	$term = strtolower(substr($resp, -17));
	if(strpos($term,'methodresponse')==false)
	  {
        $resp .= '</methodResponse>';
      }
	  
    fclose($fp);
  }
  

/*
 * BEGIN OUTPUT
 *
 * 1. sent methodcall
 * 2. response
 *
 */

print '<div align="right">Sent</div>'; 
 
reset($postedData);

while(list($f,$v) = each($postedData))
  {
    $mCall[] = ($f=='methodCall') ? $v : XMLRPC_prepare($v);
  }  
  
$mData["methodCall"]["methodName"] = array_shift($mCall);
$param_count = count($mCall);

if(!$param_count)
  {
	$mData["methodCall"]["params"] = NULL;
  }
else
  {
	for($n = 0; $n<$param_count; $n++)
	  {
	    $mData["methodCall"]["params"]["param"][$n]["value"] = $mCall[$n];
	  }
  }  
  
$mData = XML_serialize($mData); 
 
$xPath->reset();
if($xPath->importFromString($mData))
  {
	print $xPath->exportAsHtml('');
  }

print '<div style="width:100%; height:1px; background-color:black;"></div>';
print '<div align="right">Received</div>'; 

$xPath->reset();
if($xPath->importFromString($resp))
  {
	print $xPath->exportAsHtml('');
  }
else
  {
    print "<p>A parsing error has occurred. Best attempt given below.<p>";
    print '<form><textarea style="width:500px; height:600px;">';
    
	$resp = str_ireplace("<value>","\n<value>",$resp);
	$resp = str_ireplace("</value>","</value>\n",$resp);
	$resp = str_ireplace("<member>","\n<member>",$resp);
	$resp = str_ireplace("</member>","</member>\n",$resp);
	print $resp;
	
	print "</textarea></form>";
  }
?>

</body>
</html>