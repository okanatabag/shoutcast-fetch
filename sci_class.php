<?php
/**
 * shoutCastInfo
 *
 * An open source application development framework for PHP 5.1.6 or newer
 *
 * @package		shoutCastInfo
 * @author		Okan Atabag & Turkay DALAN
 * @copyright		Okiturk
 * @license		This library is free software;
 * @link		https://github.com/Okiturk/shoutCastInfo
 * @since		Version 1.1
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * shoutCastInfo Class
 *
 * This class can get shoutcast server info.
 *
 * @package		shoutCastInfo
 */
class shoutCastInfo {
// Radio Station Name
var $station_name = '';
// IP address (e.g. localhost)
var $caster_ip = ""; // insert ip or domain        
// Port for the caster (e.g. 8000) Leave blank if u don't know what your doing.
var $caster_port = ""; // insert broadcast port 
var $caster_internal_ip = "";
var $caster_internal_port = "";
var $errno = "0";
var $errstr = array('socket-time-out'=>'Server is down');
var $connect_timeout = "5";
var $state = "";
var $currentsong = "";
var $currentlisteners = "";
var $maxlisteners = "";
var $mimetype = "";
var $stream_genre = "";
var $bitrate = "";
var $peaklisteners = "";

/**
	 * Constructor
	 *
	 * @param caster_ip	string
	 * @param caster_port	int
	 * @return	void
*/

public function __construct($caster_ip,$caster_port){
	if(empty($this->caster_internal_ip)){
		$this->shout_caster_ip = $caster_ip;
		$this->shout_caster_port = $caster_port;
	} 
	else{
		$this->shout_caster_ip = $this->caster_internal_ip;
		$this->shout_caster_port = $this->caster_internal_port;
	}
}

/**
	 * Initialize shoutCastInfo
	 *
	 *
	 * @access	private
	 * @return	bool
*/
private function init(){ 
	try {
		$fp = fsockopen ($this->shout_caster_ip, $this->shout_caster_port, $this->errno, $this->errstr, $this->connect_timeout);
		if (!$fp){
			$this->state='down';
			throw new Exception($this->errstr['socket-time-out']);
		}
		else{
			fputs($fp,"GET /index.html HTTP/1.0\r\nUser-Agent: XML Getter (Mozilla Compatible)\r\n\r\n");
			$page = "";
			while(!feof($fp)){
				$page .= fgets($fp, 1000);
			}
			$page = ereg_replace(".*Server Status:", "", $page); //extract data
			$page = ereg_replace("</b></td></tr></table><br>.*", "", $page); //extract data
			$page_array = preg_split('~(</?[^>]+>)~' , $page, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);
			if (count($page_array) > 10){
				$this->state = "up";
				for($i = 0; $i < count ( $page_array ); $i ++){
						switch ($page_array [$i]){
						case "Stream Status: ":
							$this->bitrate = ereg_replace ( " kbps.*", "", ereg_replace ( ".*at ", "", $page_array [$i + 6] ) );
							$this->currentlisteners = ereg_replace ( "of.*", "", $page_array [$i + 8] );
							$this->maxlisteners = ereg_replace ( " listeners.*", "", ereg_replace ( ".*of", "", $page_array [$i + 8] ) );
						break;
						case "Listener Peak: ":
							$this->peaklisteners = strip_tags ( $page_array [$i + 6] );
						break;
						case "Content Type: ":
							$this->mimetype = strip_tags ( $page_array [$i + 6] );
						break;
						case "Stream Genre: ":
							$this->stream_genre = strip_tags ( $page_array [$i + 6] );
						break;
						case "Stream Title: ":
							$this->stream_title = strip_tags ( $page_array [$i + 6] );
						break;
						case "Current Song: ":
							$this->currentsong = strip_tags ( $page_array [$i + 6] );
						break;
					}
				}
				if($this->state=='up'){
					return true;
				}
				else {
					$this->state='down';
					throw new Exception($this->errstr['socket-time-out']);
				}
			}
			else{
				$this->state='down';
				throw new Exception($this->errstr['socket-time-out']);
			}
		}
		fclose($fp);
	} catch (Exception $e) {
		return array('error-message'=>$e->getMessage());
	}
}

/**
	 * get Bitrate method
	 *
	 * @access	public
	 * @return	mixed
*/
public function get_bitrate(){
	$in=$this->init();
	if($in===true){
		return $this->bitrate;
	}
	else{
		return $in['error-message'];
	}
}

/**
	 * get currentsong method
	 *
	 * @access	public
	 * @return	mixed
*/
public function get_currentsong(){
	$in=$this->init();
	if($in===true){
		return $this->currentsong;
	}
	else{
		return $in['error-message'];
	}
}
/**
	 * get currentlisteners method
	 *
	 * @access	public
	 * @return	mixed
*/
public function get_currentlisteners(){
	$in=$this->init();
	if($in===true){
		return $this->currentlisteners;
	}
	else{
		return $in['error-message'];
	}
}
}
?>
