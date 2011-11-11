<?php 

include("../access/authentication.php"); 

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN"
    "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>
<title>formal</title>
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
    color: black;
	font-family: Verdana, Arial;
	font-size: 11px;
	background-color: gainsboro;
	padding: 0px;
	margin: 0px;
	border: 0px;
  }
  

.check
  {
    position: static;
    font: 11px verdana, arial, sans-serif;
    font-family: Tahoma, Verdana, Arial, sans-serif;
    font-size: 11px;
	font-weight: bold;
	color: crimson;
	padding: 2px;
  }	
  
.additionalErrorInfoQ
  {
    font-family: Tahoma, Verdana, Arial, sans-serif;
    font-size: 10px;
	font-weight: bold;
	color: red;
	padding-left: 4px;
	cursor: hand;
	cursor: pointer;
  }  
  
.additionalErrorInfo
  {
    position: absolute;
	top:0;
	left:0;
    font-family: Tahoma, Verdana, Arial, sans-serif;
    font-size: 11px;
	font-weight: bold;
	color: #ff0000;
	padding: 2px;
	border: 1px black dashed;
	visibility: hidden;
  }  
  
</style>

<LINK id="menuStyle" href="menu.css" type="text/css" rel="stylesheet">

<script language="Javascript" type="text/javascript" src="Validator.js"></script>
<script language="Javascript" type="text/javascript" src="Error.js"></script>
<script language="JavaScript" type="text/javascript" src="Menu.js"></script>

<script language="Javascript">

var MenuRef = new Menu();

function nsMD(e)
  {
    if(e)
	  {
	    if(e.target.tagName.toLowerCase() != 'input') 
		  { 
		    return(false);
		  }
	  }
  }

function nsCK(e)
  {
    if(e)
	  {
	    if(e.target.tagName.toLowerCase() != 'input') 
		  {
		    return(true);
		  }
	  }
  }
  
function ieSS()
  {
	if(window.event.srcElement.tagName.toLowerCase() != 'input') 
	  {
		return(false);
	  }
  }

function init()
  {
	document.onselectstart = ieSS; 
	document.onmousedown = nsMD;
	document.onclick = nsCK;
  }
	
</script>

</head>
<body onload="init()">


<div id="menuContainer" style="width:400px">


<?php
require_once("../../framework/libs/XPath.class.php");

class loadRPCMethods
  {
    function loadRPCMethods($library="privateMethods.xml")
	  {
	  	// set up xpath object
	  	$xmlOptions = array(XML_OPTION_CASE_FOLDING => TRUE, XML_OPTION_SKIP_WHITE => TRUE);
        $this->xPath =& new XPath(FALSE, $xmlOptions);
		
	    $this->methodsDir = "/usr/local/apache/htdocs/sandro/sockets/rpcMethods/";
		$this->generatedFilepath = "/usr/local/apache/htdocs/sandro/sockets/generatedFiles/";
	  }

    function generateMenu($library="privateMethods.xml")
	  {    
	    $xml = $this->methodsDir.$library;

		print'<div class="methodCollection"><div onclick="MenuRef.activate(this)" state="closed"><img hspace="2" src="images/closed.gif" />';		

		print $library.'</div>';
		
		$this->xPath->reset();
 	    if($this->xPath->importFromFile($xml)) 
	      { 
	        // get all functions
		    $func = $this->xPath->match("/child::functions/child::*");
			
		    // read all methods in this file
		    foreach($func as $node)
		      {
		        // function name
		        $fName = $this->xPath->getAttributes($node,"NAME");

                print '<div class="methodCall"><div onclick="MenuRef.activate(this)" state="closed"><img hspace=2 src="images/closed.gif" />'.$fName.'</div>';
				
			    // get parameters for this methodCall
			    $params = $this->xPath->match("child::parameters/child::*",$node);
				
			    // tracks which parameter is currently being processed
                $pCount = 0;
				
				// store parameter data, used to generate the test form
				$formParams = array();
				
			    foreach($params as $par)
			      {
			        // get the parameter key
				    $pKey = $this->xPath->getAttributes($par,"KEY");
					
				    // bad parameter definition; die
				    if($pKey == null)
				      {
				        print"Bad parameter definition for :: ".$xml."->".$fName." :: Missing parameter KEY -> ".$par;
				        exit;
				      }
					  
				    // get parameter type (note: TYPE is optional, may be empty)
                    $pType = $this->xPath->getAttributes($par,"TYPE");
					
				    // set default type to string if no type set
				    if($pType == null) { $pType = "string"; }
					
				    // keep $pType lowercase as switch{} below is case sensitive
				    $pType = strtolower($pType);
					
				    // get the parameter default, if any
				    $pDefault = $this->xPath->getAttributes($par,"DEFAULT");
					
					$pDefault = ($pDefault) ? $pDefault : '[none]';
					
					$formParams[] = array(
					  'name' 		=> $pKey,
					  'type' 		=> $pType,
					  'default'		=> $pDefault
					);

                    ++$pCount;
				  }
				/*
				 * create a form that allows the client to test
				 * this method. 
				 */
				
				print '<div class="methodCallFormatElement"><div onclick="MenuRef.activate(this)" state="closed"><img hspace=2 src="images/closed.gif" />test method</div>';
				// load and display the method call example
				print '<div class="element">';

                print '<form name="form_'.$fName.'" method="post" action="executeRPC.php" target="executeRPC">';
				print '<input type="hidden" name="methodCall" value="'.$fName.'">';
				print '<div style="width:96%; padding:4px;">';
				
				for($f=0; $f<count($formParams); $f++)
				  {
				    print '<div style="width:100%; background-color:LemonChiffon;">';
				    print '<input type="text" name="'.$formParams[$f]['name'].'" value="'.(($formParams[$f]['default']!='[none]') ? $formParams[$f]['default'] : "").'" style="width:98%; margin: 2px;">';
				    print '</div>';
				
				    print '<div style="width:100%; background-color:LightSkyBlue; padding:2px;">';
					print '<span style="float:left; text-align:right; width:24%; color:Navy;">';
				    print 'Parameter:<br />Type:<br />Default:<br />';
					print '</span>';
					print '<div>';
				    print '&nbsp;'.$formParams[$f]['name'].'<br />';
				    print '&nbsp;'.$formParams[$f]['type'].'<br />';
				    print '&nbsp;'.$formParams[$f]['default'].'<br />';
					print '</div>';
				    print '</div><br />';
				  }
				
				print '<div id="'.$formParams[$f]['name'].'_submit"><input	type="button" onclick="return Validator.attemptSubmit(this);" value="TEST" /></div>';
				
				print '</div>';
                print '</form>';
				print '</div></div>';
				
				/*
				 * create a sample of proper method call structure
				 */
				print '<div class="methodCallFormatElement"><div onclick="MenuRef.activate(this)" state="closed"><img hspace=2 src="images/closed.gif" />methodCall</div>';
				
				// load and display the method call example
				print '<div class="element">';
                $fPath = $this->generatedFilepath.basename($xml).".".$fName.".example.html";
				$mE = file_get_contents($fPath);
				print $mE.'</div></div>';
				
				print'</div>';
			  }
		  }
	    else
	      {
	        print "error";
			exit;
	      }
		
		print '</div>';  
		
	  } 
  }

$reader = new loadRPCMethods();

$reader->generateMenu('buddyMethods.xml');
$reader->generateMenu('channelMethods.xml');

//$reader->generateMenu('testMethods.xml');

?>


</div>

</body>
</html>
