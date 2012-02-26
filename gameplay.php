<?php
	// Required files
	require_once "classes/CrazeDownload.class.php";
	require_once "../../../xfire.conf"; // Contains Xfire userdata


	// Script
	$remote = new CrazeDownload( $xfiregames . "?u=" . $xfirelogin . "&p=" . $xfirepass, 1800);
	if (filesize($remote->cachefile()) > 0) {
		$xml = @simplexml_load_file( $remote->cachefile() ) ;
	}
	if (false == $xml) {
		message();
	} else {
		foreach ($xml->children() as $game) {
			// Sometimes an error message is displayed in feed
			if (($game == 'User does not have any Gameplay Hours') ) { message('(<em>No recent gameplay available from Xfire.</em>)'); return; 
			} elseif (($game->getname() == 'error') ) { message('Xfire error: «<em>'.$game.'</em>»'); return; }


			$dataid = $game['id'];
			foreach ($game->children() as $data) {
				// Additional variables
				$dataname = $data->getname();

				// Build into array for later processing
				$gamedb["$dataid"]["$dataname"] = $data;
			}
			// Skip games with no hours last 7 days
			if (0 == $gamedb["$dataid"]["weektime"]) { unset($gamedb["$dataid"]); }

			// Copy recent games to separate array for easy sorting
			if ($xfiretime < $gamedb["$dataid"]["weektime"]) { 
				$longname = $gamedb["$dataid"]["longname"];
				$recent["$longname"] = $gamedb["$dataid"];
			}
		}
		if (!is_array($recent)) { message(); } else {
			ksort($recent);
			foreach ($recent as $info) {
				// Downloading gif to cache instead of flooding xfire.com
				$icon = new CrazeDownload($xfireicons.'/'.$info['shortname'].'.gif',99999999,$info['shortname'].".gif");
	
				// HTML output
				echo '<img src="'.$icon->cachefile().'" alt="'.$info['longname'].'"> ';
				echo $info['longname']."<br>\n";
			}
		}
	}
	function message($msg = '(<em>List of games is currently not available.</em>)') {
		echo $msg;
	}

?>
