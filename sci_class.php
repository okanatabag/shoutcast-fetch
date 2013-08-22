<?php error_reporting(E_ALL ^ E_NOTICE ^ E_DEPRECATED); 
header("Content-Type: text/html; charset=iso-8859-9");
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
class shoutCastInfo {
//General parameters;
// Radio Station Name
var $station_name = '';
// IP address (e.g. localhost)
var $caster_ip = ""; // insert ip or domain        
// Port for the caster (e.g. 8000) Leave blank if u don't know what your doing.
var $caster_port = ""; // insert broadcast port 
// Advanced config parameters
// If both web server and icecast are behind a router and the web server needs
// to know the internal ip of caster so it can pull the info for the player.
// This could be the case if all three got different internal ip-s.
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

public function shoutCastInfo($caster_ip,$caster_port){
	if(empty($this->caster_internal_ip)){
		$this->shout_caster_ip = $caster_ip;
		$this->shout_caster_port = $caster_port;
	} 
	else{
		$this->shout_caster_ip = $this->caster_internal_ip;
		$this->shout_caster_port = $this->caster_internal_port;
	}
}
public function init(){ 
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
public function get_bitrate(){
	$in=$this->init();
	if($in===true){
		return $this->bitrate;
	}
	else{
		return $in['error-message'];
	}
}
public function get_currentsong(){
	$in=$this->init();
	if($in===true){
		return $this->currentsong;
	}
	else{
		return $in['error-message'];
	}
}
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
