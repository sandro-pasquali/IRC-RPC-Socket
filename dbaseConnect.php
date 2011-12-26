<?php

// DATABASE CONFIG

$DB_USER = "phpread";
$DB_PASS = "gn0fp51";
$DB_NAME = "NAME";
$DB_ADDRESS = "1.1.1.1:3306"; 

if(mysql_pconnect($DB_ADDRESS,$DB_USER,$DB_PASS))
  {
    mysql_select_db($DB_NAME);
  }
else
  {
    print "Could not connect to database.";
	exit;
  }

?>