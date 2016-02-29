<?php /*
   _____                                 _   _               _                   _ 
  / ____|                               | | (_)             (_)                 | |
 | (___    _   _   _ __     ___   _ __  | |  _   _ __ ___    _   _ __     __ _  | |
  \___ \  | | | | | '_ \   / _ \ | '__| | | | | | '_ ` _ \  | | | '_ \   / _` | | |
  ____) | | |_| | | |_) | |  __/ | |    | | | | | | | | | | | | | | | | | (_| | | |
 |_____/   \__,_| | .__/   \___| |_|    |_| |_| |_| |_| |_| |_| |_| |_|  \__,_| |_|
                  | |                                                              
                  |_| 
				  
Copyright (c) 2016 by Scott Chase Waggener <tidal@utexas.edu>
				                                                               
Application: Superliminal
Description: A digital signage solution designed for Raspberry Pi

File: server_actions.php
Description: List of functions intended to be accessed via AJAX

---------------------------------------------------------------------------
This file is part of Superliminal.

Superliminal is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

Superliminal is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with Superliminal.  If not, see <http://www.gnu.org/licenses/>.
*/?>


<?php
header('HTTP/1.1 200');
header('Content-Type: application/json; charset=UTF-8');
error_reporting();

require_once "/var/www/html/Dropbox/autoload.php";
use \Dropbox as dbx;

$_POST['action'] = 'dropbox';

if ( !isset($_POST['action']) ) {
	header('HTTP/1.1 500 No Action Specified');
	die();
}

switch ($_POST['action']) {
	case 'dropbox':
		dropbox_download();
		break;
	case 'usb':
		usb($_POST['count']); 
		break;
	case 'connectivity':
		getConnectivity($_POST['count']);
		break;
	case 'hotspot':
		hotspot();
		break;
	case 'scan':
		get_network();
		break;
	default:
		header('HTTP/1.1 500 Invalid Call');
		die('Invalid function call');
		break;
}

// Clean up output from 'iw' scan
function parse_iw($needle,$haystack){
	return ltrim(stristr(ltrim(implode(preg_grep($needle,$haystack))),': '),': ');
}

// Returns a JSON of scanned networks and their signal strength
function get_network() {
	$string = shell_exec('sudo /var/www/html/server_files/scan.sh');
	$networks = explode('(on wlan0)', $string);
	foreach( $networks as $key => $value )
		$networks[$key] = explode(PHP_EOL,$value);
	$parsed_networks = array();
	foreach( $networks as $key => $value ) {
		$array['ssid'] = parse_iw("/\bSSID\b/i",$value);
		
		$db = parse_iw("/\bsignal\b/i",$value);
		if($db <= -100)
        	$quality = 0;
   		else if($db >= -50)
       		$quality = 100;
   		else
        	$quality = 2 * ($db + 100);
		$array['signal'] = $quality;
		$array['db'] = $db;
		
		array_push($parsed_networks,$array);
	}
	$parsed_networks = array_slice($parsed_networks,1);
		
	$ssid_pattern = "/\bSSID\b/i";
	//print_r($networks);
	echo json_encode($parsed_networks,JSON_PRETTY_PRINT);
}



// Copy from USB
function usb($count) {
	$dir = '/mnt/'; 
	$settings = parse_ini_file("/var/www/html/server_files/settings.ini");
	 
	
	// Mount USB disk to /mnt
	try {
		exec('sudo mount -o umask=027,gid=33,uid=1000 /dev/sda1 /mnt');
		$files = array_slice(scandir('/mnt'), 2);
		$local = array_slice(scandir('/var/www/html/flyers'), 2);
	}catch (Exception $e) {
	}
	
	if( empty($files) ) {
		header('HTTP/1.1 500 Internal Server');
		die();
	}
	
	// If USB mounted, empty local files
	foreach( $local as $key => $file )
		unlink('/var/www/html/flyers/'.$file);
	
	foreach( $files as $key => $file ) {
		if ( !is_dir($dir.$file) )
			copy($dir.$file,'/var/www/html/flyers/'.$file);
	}
	$return = array('flyers' => array_slice(scandir('/var/www/html/flyers'), 2));
	array_push($return, array('count' => $count));
	echo json_encode($return,JSON_PRETTY_PRINT);
}

 
// Download from Dropbox
function dropbox_download() {
	// Set path to settings file and import to array $settings
	$server_files_dir = '/var/www/html/server_files';
	$settings = parse_ini_file("$server_files_dir/settings.ini");
	$json = json_decode(file_get_contents("$server_files_dir/accounts.json"), true);
	
	// Look for local flyers folder, if no exists make it
	$flyers_path = '/var/www/html/flyers/';
	if ( !file_exists($flyers_path) )
		mkdir($flyers_path, 0770);
		
	// Pull local image filenames into $local_files
	$local = array_slice(scandir($flyers_path), 2);
	$local_files = array();
	foreach ($local as $key => $value ) 
		$local_files[$value] = filesize('/var/www/html/flyers/'.$value);
		
	
	// Try to load token from accounts.json
	if ( array_key_exists($settings['configFile'],$json) ) {
		$accessToken = $json[$settings['configFile']];
	}
	// If target account is missing, return an error
	else {
		header('HTTP/1.1 500 Account Missing');
		die('Missing Dropbox account');
	}
	
	// Try to set up dropbox
	$count = 0; $max_tries = 5;
	$done = false;
	while ( !$done && $count <= $max_tries ) {
		try {
			// Set up client, get folder meta and account info
			$dbxClient = new dbx\Client($accessToken, "PHP-Example/1.0");
			$done = true;
			$folderMetadata = $dbxClient->getMetadataWithChildren("/");
			$accountInfo = $dbxClient->getAccountInfo();
			
			// Parse dropbox files to $files as [ $file => $size ]
			$files = array();
			foreach ($folderMetadata['contents'] as $key => $meta) {
				$files[ltrim($meta['path'],'/')] = $meta['bytes'];
			}
			
			// Delete local files no longer in Dropbox
			$deleted_files = array();
			foreach ( $local_files as $local_file => $size ) {
				if ( !array_key_exists($local_file, $files) ) {
					unlink($flyers_path.$local_file);
					$deleted_files[] = $local_file;
				}
			}
			
			// Download Dropbox files if not saved locally or if size has changed
			$downloaded_files = array();
			foreach ( $files as $file => $size ) {
				$filePath = $flyers_path.$file;
				
				// If $file isnt in $local_files and 
				if ( !array_key_exists($file, $local_files) || $local_files[$file] != $size ) {
					$f = fopen($flyers_path.'/'.$file, "w+b");
					$fileMetadata = $dbxClient->getFile('/'.$file, $f);
					fclose($f);
					//chmod($flyers_path.'/'.$file, 0777);
					$downloaded_files[] = $file;
				} 
			}
	
			// Assemble array to return to calling function
			header('HTTP/1.1 200');
			$return = array( 
				'deleted' => $deleted_files,
				'downloaded' => $downloaded_files,
				'local' => array_slice( scandir('/var/www/html/flyers'), 2 ),
				'account' => $accountInfo
			);
			echo json_encode($return, JSON_PRETTY_PRINT);
		}
			catch (InvalidArgumentException $e) {
				header('HTTP/1.1 500 Network Error?');
				$string = $e->getMessage(); 
				$s = explode(': ',$string);
				die(str_replace( array('}',"\""), '', $s[1] ));
			}
			catch ( dbx\Exception $e ) {
				header('HTTP/1.1 500 General Dropbox Error');
				$string = $e->getMessage(); 
				$s = explode(': ',$string);
				die( str_replace( array('}',"\""), '', $s[1] ) );
			}
		
	}
}


// Check connectivity to LAN and WAN 
function getConnectivity($count) {
	$local_ip = rtrim(shell_exec("ifconfig | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'"));
								
	$connected = @fsockopen("www.google.com", 80);
    if ($connected){
		$is_conn = 'true';
		fclose($connected);
	}
	else { 
		$is_conn = 'false';
	}
	header('HTTP/1.1 200');
	echo json_encode(array('ip' => $local_ip, 'internet' => $is_conn, 'count' => $count));
}

// Start AP hotspot using nmcli (con-name AP)
function hotspot() {
	exec('sudo nmcli con up AP');
}

function reboot() {
	exec('sudo /var/www/html/server_files/reboot.sh');
}


?>