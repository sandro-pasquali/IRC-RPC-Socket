<?php

require_once("../framework/System.php");

require_once("Socket.php");
require_once("Rooms.php");

$System->startLog("logs/socketServer.log");

$System->Dbase->readConnect("phpread","123","db","127.0.0.1:3306");
$System->Dbase->writeConnect("phpwrite","123","db","127.0.0.1:3306");

$System->attach("Socket");
$System->attach("Rooms");

$System->Socket->loadRPCMethods("privateMethods.xml");
$System->Socket->loadRPCMethods("testMethods.xml");
$System->Socket->loadRPCMethods("channelMethods.xml");
$System->Socket->loadRPCMethods("buddyMethods.xml");

$System->Socket->create("127.0.0.1",9000);
$System->Socket->start();

?>
