<?php
/*
 * CrazeDownload copies remote data to local cache
 *
 * @package Craze
 * @author Geir Andre Halle
 * @version v11.8.3
 * @copyright 2009,2011 Geir Andre Halle
 * @license http://www.gnu.org/licenses/gpl.txt
 */
class CrazeDownload {
    // Initializing variables
    private $cachepath = 'cache/' ;    // Local folder for temporary storage
    private $cachefile = '' ;          // Filename variable for use later

    /*
     * @param string $url uniform resource locator/identifier
     * @param int $ttl Time-to-live, in seconds between refresh
     * @param string $filename For occasions you don't want an encoded string
     */
    function __construct($url, $ttl = 300, $filename = "") {

	// Generated filename is encoded from url to avoid slashes and colons in the filesystem
	if ("" != $filename) { 
		$this->cachefile = $this->cachepath . $filename;
	} else { 
		$this->cachefile = $this->cachepath . str_replace("/","-",base64_encode($url)) ;
	}

	// Folders are not created automatically (by design choice). Tell user where to make one.
        if (!is_dir($this->cachepath)) {
            die ("Cache folder not found! Try to create manually: $this->cachepath");
        } elseif(!is_writable($this->cachepath)) {
            die ("Cache folder $this->cachepath is not writable!");
	}

	// Remove empty files (might be failed download)
	if (file_exists($this->cachefile)) {
		if (0 == filesize($this->cachefile)) {
			unlink($this->cachefile);
		}
	}

	// If local file does not exist or is too old, retrieve remote data.
        if ( (!file_exists($this->cachefile)) || ((time() - filemtime($this->cachefile)) > $ttl) ) {

            // Get remote resource
            $remoteio = curl_init() ;
            curl_setopt($remoteio, CURLOPT_URL, $url) ;
            curl_setopt($remoteio, CURLOPT_HEADER, 0) ;
            curl_setopt($remoteio, CURLOPT_RETURNTRANSFER, true) ;
	    //curl_setopt($remoteio, CURLOPT_BINARYTRANSFER,1);
            $remotecontent = curl_exec($remoteio) ;
            curl_close($remoteio) ;

            // Save resource to local file
            $cacheio = fopen($this->cachefile, 'w+');
            fwrite($cacheio, $remotecontent);

            // Clean up
            fclose($cacheio);
        }
    }

    /*
     * @return string Local filename
     */
    public function cachefile() {
        return "$this->cachefile" ;
    }
}
?>
