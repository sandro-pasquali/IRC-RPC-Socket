<?php
/**
* allBuddies.
*
* @param     $sentUsername 		(string)	Default:(string)""
* @example /usr/local/apache/htdocs/sandro/sockets/generatedFiles/buddyMethods.xml.rpcExamples.html
*/
function allBuddies() {
    global $System;$_argv = func_get_arg(0);$_slot = array_shift($_argv);$sentUsername= (isset($_argv[0])) ? $_argv[0] : (string)"";if(is_string($sentUsername)==false) { return(false); }/************************************************************* * channelMethods.xml :: allBuddies * * Returns a buddy list for a specified user as structs * containing various pieces of information on buddy and location * of buddy within chat * * Sandro Pasquali (sandropasquali@yahoo.com) * 15.03.2004 * * ACCEPTS: String sentUsername * a valid username * * RETURNS: * array( * struct( * * 'displayName' - String * * 'ircChannel' - String - default "" * * 'currentChannel' - String - default "" * * 'buddyType' - String - default "" * * 'status' - String * * HISTORY: * *************************************************************/ /* * get userid for this username */ $result = mysql_query("select user_id from user_misc where username = '$sentUsername'",$System->Dbase->readID); if($result &amp;&amp; (mysql_num_rows($result) > 0)) { $uID = mysql_fetch_array($result); $uID = $uID[0]; } else /* not a known user */ { return(false); } /* * now get users buddy list */ $q = "SELECT t2.username FROM dc_datebook AS t1, user_misc AS t2 WHERE (t2.user_id = t1.buddy_id) AND t1.user_id = $uID LIMIT 0,50"; $buddyList = mysql_query($q,$System->Dbase->readID); /* * get all the channels and create a user/room lookup array */ $q = "select DISTINCT(chanid),channel from chan"; $allChannels = mysql_query($q,$System->Dbase->readID); if($allChannels) { /* used to determine which room any one user is in. * will create an array that allows quick lookup * for any username, such as [sandro] => '#triviaroom1' */ $userIndex = array(); /* go through each channel, determine who is in it, and * populate $userIndex; */ while(list($id,$channelName) = mysql_fetch_array($allChannels)) { /* only store valid(public) rooms information; * skip if not a valid room */ if(!$System->Rooms->isValidRoom($channelName)) { continue; } $q = "SELECT t1.nick FROM user AS t1, ison AS t2 WHERE t2.chanid = $id AND t1.nickid=t2.nickid"; $roomUsers = mysql_query($q,$System->Dbase->readID); if($roomUsers &amp;&amp; (mysql_num_rows($roomUsers) > 0)) { while($usrNm = mysql_fetch_array($roomUsers)) { /* * remove trailing "_", if any */ $displayUsername = (substr($usrNm[0], -1) == "_") ? substr($usrNm[0], 0, -1) : $usrNm[0]; $userIndex[$displayUsername] = $channelName; } } mysql_free_result($roomUsers); } mysql_free_result($allChannels); } $bList = array(); $cnt = 0; /* build the final data structure, running through each buddy in * list and determining room buddy is in, if any */ if($buddyList) { /* * for each buddy in list, check if online */ while(list($buddyDisplayName) = mysql_fetch_array($buddyList)) { $bList[$cnt] = array(); /* displayName * * nickname in chat */ $bList[$cnt]['displayName'] = $buddyDisplayName; /* * determine if the user is in a room */ if(isset($userIndex[$buddyDisplayName])) { /* ircChannel * * raw channel name, ie. #triviaroom1 */ $bList[$cnt]['ircChannel'] = $userIndex[$buddyDisplayName]; /* currentChannel * * formatted room name, ie. "TriviaRoom 1" */ $bList[$cnt]['currentChannel'] = $System->Rooms->getRoomName($userIndex[$buddyDisplayName]); } /* buddyType * * unused */ $bList[$cnt]['buddyType'] = ""; /* status * * unused */ $bList[$cnt]['status'] = ""; ++$cnt; } mysql_free_result($buddyList); return($bList); } else { return(false); }
  }

/**
* addBuddy.
*
* @param     $user 		(string)	Default:(string)""
* @param     $buddy 		(string)	Default:(string)""
* @example /usr/local/apache/htdocs/sandro/sockets/generatedFiles/buddyMethods.xml.rpcExamples.html
*/
function addBuddy() {
    global $System;$_argv = func_get_arg(0);$_slot = array_shift($_argv);$user= (isset($_argv[0])) ? $_argv[0] : (string)"";if(is_string($user)==false) { return(false); }$buddy= (isset($_argv[1])) ? $_argv[1] : (string)"";if(is_string($buddy)==false) { return(false); }/* * get ID's for user and buddy */ $q = "SELECT username,user_id FROM user_accounts WHERE username='$user' || username='$buddy'"; $usernameID = mysql_query($q,$System->Dbase->readID); /* * if we get exactly two results */ if($usernameID &amp;&amp; (mysql_num_rows($usernameID) == 2)) { while($row = mysql_fetch_array($usernameID)) { if($row[0]==$user) { $userID = $row[1]; } else { $buddyID = $row[1]; } } mysql_free_result($usernameID); /* * now check if buddy is already on list */ $q = "SELECT user_id FROM dc_datebook WHERE buddy_id = '$buddyID' AND user_id = '$userID'"; $isBuddy = mysql_query($q,$System->Dbase->readID); /* * if there are no results (ie. not already on list) */ if($isBuddy &amp;&amp; (mysql_num_rows($isBuddy)==0)) { mysql_free_result($isBuddy); /* * get buddy gender */ $q = "SELECT user_gender FROM dc_searchprefs WHERE user_id = '$buddyID'"; $getGender = mysql_query($q,$System->Dbase->readID); if($getGender &amp;&amp; (mysql_num_rows($getGender)>0)) { $gender = mysql_fetch_array($getGender); mysql_free_result($getGender); /* * update buddy list */ $q = "INSERT INTO dc_datebook (user_id, buddy_id, buddy_gender) VALUES ('$userID','$buddyID','".$gender[0]."')"; $addBuddy = mysql_query($q,$System->Dbase->writeID); if($addBuddy) { return(array('OK')); } } } } return(false);
  }

/**
* removeBuddy.
*
* @param     $user 		(string)	Default:(string)""
* @param     $buddy 		(string)	Default:(string)""
* @example /usr/local/apache/htdocs/sandro/sockets/generatedFiles/buddyMethods.xml.rpcExamples.html
*/
function removeBuddy() {
    global $System;$_argv = func_get_arg(0);$_slot = array_shift($_argv);$user= (isset($_argv[0])) ? $_argv[0] : (string)"";if(is_string($user)==false) { return(false); }$buddy= (isset($_argv[1])) ? $_argv[1] : (string)"";if(is_string($buddy)==false) { return(false); }/* * get ID's for user and buddy */ $q = "SELECT username,user_id FROM user_accounts WHERE username='$user' || username='$buddy'"; $usernameID = mysql_query($q,$System->Dbase->readID); /* * if we get exactly two results */ if($usernameID &amp;&amp; (mysql_num_rows($usernameID) == 2)) { while($row = mysql_fetch_array($usernameID)) { if($row[0]==$user) { $userID = $row[1]; } else { $buddyID = $row[1]; } } mysql_free_result($usernameID); /* * now check if buddy exists */ $q = "SELECT user_id FROM dc_datebook WHERE buddy_id = '$buddyID' AND user_id = '$userID'"; $isBuddy = mysql_query($q,$System->Dbase->readID); /* * if there are results (ie. on list) */ if($isBuddy &amp;&amp; (mysql_num_rows($isBuddy)>0)) { mysql_free_result($isBuddy); /* * update buddy list */ $q = "DELETE FROM dc_datebook WHERE user_id='$userID' AND buddy_id = '$buddyID'"; $removeBuddy = mysql_query($q,$System->Dbase->writeID); if($removeBuddy) { return(array('OK')); } } } return(false);
  }

/**
* callService.
*
* @param     $user 		(string)	Default:(string)""
* @param     $buddy 		(string)	Default:(string)""
* @example /usr/local/apache/htdocs/sandro/sockets/generatedFiles/buddyMethods.xml.rpcExamples.html
*/
function callService() {
    global $System;$_argv = func_get_arg(0);$_slot = array_shift($_argv);$user= (isset($_argv[0])) ? $_argv[0] : (string)"";if(is_string($user)==false) { return(false); }$buddy= (isset($_argv[1])) ? $_argv[1] : (string)"";if(is_string($buddy)==false) { return(false); }$client = new soapclient('http://63.110.38.195/sandro/SOAP/test.php?wsdl', true); $err = $client->getError(); if ($err) { echo '<h2>Constructor error</h2><pre>' . $err . '</pre>'; } $result = $client->call('hello', array('name' => 'Scott')); if ($client->fault) { return(false); } else { $err = $client->getError(); if ($err) { return(false); } else { return(array($result)); } }
  }

?>
