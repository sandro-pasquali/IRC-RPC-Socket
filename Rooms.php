<?php

/**
 * Rooms.php
 * 
 * @author		Sandro Pasquali 	sandropasquali@yahoo.com
 * @version 	0.09 alpha			
 * @package 	SocketServer
 */
class Rooms extends Assembly
  {
    function __construct()
	  {
	    $this->roomLookup = array
          (
            '#triviaroom1' 	=> 'Trivia Room 1',
            '#triviaroom2' 	=> 'Trivia Room 2',
            '#actionchatt' 	=> 'Action Chat',
	        '#adultlobby' 	=> 'Adult Lobby',
	        '#hotttub' 		=> 'The Hot Tub',
	        '#lubetube' 	=> 'The Lube Tube',
	        '#truthordare' 	=> 'Truth or Dare',
	        '#publicroom1' 	=> 'Public Room 1',
	        '#publicroom2' 	=> 'Public Room 2',
	        '#publicroom3' 	=> 'Public Room 3',
	        '#publicroom4' 	=> 'Public Room 4',
	        '#glounge' 		=> 'G-Lounge',
	        '#cloudnine' 	=> 'Cloud Nine'
		  );
	  }
	  
    function isValidRoom($rm)
      {
	    return(isset($this->roomLookup[$rm]));
      }
  
    function getRoomName($rm)
      {
        if($this->isValidRoom($rm))
	      {
	        return($this->roomLookup[$rm]);
	      }
	    else
	      {
	        return("");
	      }
      }
  }
  
?>
