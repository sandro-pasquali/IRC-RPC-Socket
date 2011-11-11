<?php

// valid (standard) channel array

$_vRs = array
  (
    '#triviaroom1' => 'Trivia Room 1',
    '#triviaroom2' => 'Trivia Room 2',
    '#actionchatt' => 'Action Chat',
	'#adultlobby' => 'Adult Lobby',
	'#hotttub' => 'The Hot Tub',
	'#lubetube' => 'The Lube Tube',
	'#truthordare' => 'Truth or Dare',
	'#publicroom1' => 'Public Room 1',
	'#publicroom2' => 'Public Room 2',
	'#publicroom3' => 'Public Room 3',
	'#publicroom4' => 'Public Room 4'
  );

function _isValidRoom($rm)
  {
    global $_vRs;
	
	return(isset($_vRs[$rm]));
  }
  
function _getRoomName($rm)
  {
    global $_vRs;
	
    if(_isValidRoom($rm))
	  {
	    return($_vRs[$rm]);
	  }
	else
	  {
	    return("");
	  }
  }
  
?>
