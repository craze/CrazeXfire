<?php
	// Includes
	require_once "classes/CrazeDownload.class.php";
	include_once"../../../xfire.conf";

	// Remote resource
	$remote = new CrazeDownload( $xfirehosts , 1800);
	if (filesize($remote->cachefile()) > 0) {
		$xml = @simplexml_load_file( $remote->cachefile() ) ;
	}

	// Parse XML data
	if (false == $xml) {
		message();
	} else {
		foreach ($xml->children() as $game) {

			foreach ($game->children() as $data) {

				// Establish name of game
				if ($data->getname() == 'game') {
					if ($gamedb["$gamename"]) { $gamecount++; } else { $gamecount = 0; }
						$gamename = $data." ".$gamecount;
						$gamedb["$gamename"]['longname'] = $data;
						$gamedb["$gamename"]['shortname'] = $data['shortname'];
					} elseif ($data->getname() == 'ip') {
						// We want hostnames, but only if forward and reverse match
						$resolved = gethostbyaddr($data);
						$forwardip = gethostbyname($resolved);
						$gamedb["$gamename"][$data->getname()] = (($data == $forwardip) ? $resolved : $data);
					} else {
					$gamedb["$gamename"][$data->getname()] = $data;
				}

				// Remove port if using known default
				$portcheck = $gamedb["$gamename"]['shortname'].':'.$gamedb["$gamename"]['port'];
				foreach($defaultport as $port) {
					if ($portcheck == $port) { unset($gamedb["$gamename"]['port']); }
				}
			}
		}
		if (!is_array($gamedb)) { message(); } else {
			ksort($gamedb);
			foreach ($gamedb as $info) {

				// Downloading gif to cache instead of flooding xfire.com
				$flag = new CrazeDownload($xfireflags.'/'.$info['country'].'.gif',99999999,$info['country'].".gif");
				$icon = new CrazeDownload($xfireicons.'/'.$info['shortname'].'.gif',99999999,$info['shortname'].".gif");
	
				// Prepare the port string if needed
				$port = ($info["port"] ? ':'.$info["port"] : "");

				// HTML output
				$output1 = '<img src="'.$icon->cachefile().'" title="'.$info['longname'].'" alt="'.$info['longname'].'"> ';
				$output2 = $info['ip'] . $port;
				$output3 = ' (<img src="'.$flag->cachefile().'" title="'.$info['country'].'" alt="'.$info['country'].'">) ';
				$output4 = "<br>\n";
				
				// In case of hotlinks compatibility, add clickable addresses
				switch($info['shortname']) {
					case "tspeak3":
						$outputformat = $output1 . '<a title="Click to connect" style="text-decoration: none;" href="ts3server://'.$output2.'">';
						$outputformat .= $output2 . '</a>' . $output3 . $output4;
						break;
					default;
						$outputformat = $output1.$output2.$output3.$output4;
						break;
				}

				// Send to browser
				echo $outputformat;
			}
		}
	}
	function message($msg = '(<em>Server list is currently not available.</em>)') {
		echo $msg;
	}

?>
