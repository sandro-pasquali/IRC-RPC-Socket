<?php

require_once("libs/XPath.class.php");
require_once("libs/xmlrpc.php");
require_once("libs/nusoap.php");

require_once("Assembly.php");
require_once("Exceptions.php");
require_once("DBase.php");
require_once("DateTime.php");

$System = new System();

$System->attach("DateTime");
$System->attach("Dbase");
  
?>

