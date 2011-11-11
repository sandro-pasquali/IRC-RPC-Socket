<?php
/**
* testRPC.
*
* @param     $integerParam 		(int)	Default:(int)""
* @param     $integerParamDefault 		(string)	Default:'defaultOK'
* @example /usr/local/apache/htdocs/sandro/sockets/generatedFiles/testMethods.xml.rpcExamples.html
*/
function testRPC() {
    global $System;$_argv = func_get_arg(0);$_slot = array_shift($_argv);$integerParam= (isset($_argv[0])) ? $_argv[0] : (int)"";if(is_int($integerParam)==false) { return(false); }$integerParamDefault= (isset($_argv[1])) ? $_argv[1] : 'defaultOK';if(is_string($integerParamDefault)==false) { return(false); }/* $ret[0] = "integerParam: ".$integerParam; * $ret[1] = "integerParamDefault: ".$integerParamDefault; */ return('sandro');
  }

/**
* testArray.
*
* @param     $fore 		(datetime.iso8601)	Default:(string)""
* @param     $ar 		(array)	Default:(array)""
* @param     $aft 		(string)	Default:(string)""
* @example /usr/local/apache/htdocs/sandro/sockets/generatedFiles/testMethods.xml.rpcExamples.html
*/
function testArray() {
    global $System;$_argv = func_get_arg(0);$_slot = array_shift($_argv);$fore= (isset($_argv[0])) ? $_argv[0] : (string)"";if((preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})(\.[0-9]*)?/",$fore) || preg_match("/^([0-9]{4})-([0-9]{2})-([0-9]{2})T([0-9]{2}):([0-9]{2}):([0-9]{2})(\.[0-9]*)?/?(Z|[+\-][0-9]{2}:?[0-9]{2})?/",$fore))==false) { return(false); }$ar= (isset($_argv[1])) ? $_argv[1] : (array)"";if(is_array($ar)==false) { return(false); }$aft= (isset($_argv[2])) ? $_argv[2] : (string)"";if(is_string($aft)==false) { return(false); }return(array($fore,$ar,$aft));
  }

/**
* testStruct.
*
* @param     $ar 		(array)	Default:(array)""
* @example /usr/local/apache/htdocs/sandro/sockets/generatedFiles/testMethods.xml.rpcExamples.html
*/
function testStruct() {
    global $System;$_argv = func_get_arg(0);$_slot = array_shift($_argv);$ar= (isset($_argv[0])) ? $_argv[0] : (array)"";if(is_array($ar)==false) { return(false); }return($ar);
  }

?>
