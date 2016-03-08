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

File: functions.php
Description: Repository of function used throughout Superliminal

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

---------------------------------------------------------------------------

Functions Master List

bool replaceSetting(str $setting, str $newOption, str $file)
	Acts on $file to make a replacement of the form
	$setting = OLD OPTION 	=> 	$setting = $newOption

bool generateConfigFilesDropdown(mixed $array, string $id)
	Generates a <select> with <option id=$id> from an array of account
	files.  Array is normally passed from a parsed JSON
*/?>

<?php

// Set up constants
$server_files_dir='/var/www/html/server_files';
$json = json_decode(file_get_contents("$server_files_dir/accounts.json"), true);
$appInfo = $json['dropbox_app_key_secret'];

// Insert changed into settings.ini
$settings = update_settings_ini();

?><script>var placeholders = <?php echo json_encode($settings)?>;</script><?php

if( isset($_POST) && !empty($_POST) )
	echo '<script>alert("Settings saved.");</script>';


// array update_settings_ini([$settings])
// Replaces $setting with $newOption in ini $file and returns new ini as array
function update_settings_ini() {
	$path = '/var/www/html/server_files/settings.ini';
	$settings_old = parse_ini_file($path);
	
	if( !isset($_POST['setting_ini']) )
		return $settings_old;
	else
		$settings = $_POST['setting_ini'];
	
	debug_to_console('Updating settings.ini');
	
	// Scan $_POST['settings_ini'] and make replacements
	foreach ($settings as $key => $value) {
		$value = preg_replace("/[^A-Za-z0-9 ]/", '', trim($value,' '));
		if ( (!empty($value) || is_numeric($value)) && $value != $settings_old[$key] ) {
			// Replace the setting
			replaceSetting($key,$value,$path);
			
			// Update overscan on the fly
			if( $key == 'overscan_x' ) {
				$overscan = array(
					escapeshellarg($value),
					escapeshellarg($settings_old['overscan_y'])
				);
				update_overscan($overscan);
			}
			else if ( $key == 'overscan_y' ) {
				$overscan = array(
					escapeshellarg($settings_old['overscan_y']),
					escapeshellarg($value)
				);
				update_overscan($overscan);
			}
		}
		
	}
	return parse_ini_file($path);
}

function replaceSetting($setting, $newOption, $file) {
	exec('sed -i s/.*'.$setting.'=.*/'.$setting.'='.$newOption.'/g '.$file);
}

// Generate <select> of id=$id using elements of $array
function generateConfigFilesDropdown($array, $id) {
	echo "<select class='setting ini_select' id=$id name=$id>\r\n";
	foreach ($array as $key => $value) {
		if ( $key != 'dropbox_app_key_secret' )
			echo "\t\t\t\t<option id='configFile' value='$key'>$key</option>\r\n";
	}
	echo "\t\t\t</select>\r\n";
	return true;
}

function update_overscan($args) {;
	exec('sudo /var/www/html/server_files/overscan '.$args[1].' '.$args[1].' '.$args[0].' '.$args[0], $output, $return);
	return $return;
}

function generateNetworksDropdown($array,$id) {
	if( !isset($array,$id) )
		return false;
	echo "<select class='setting' name='nmcli[".$id."]'>";
	foreach ($array as $key => $value) {
		$name = $value['name'];
		if ( $value['name'] )
			echo "<option id='network' value=$name> $name </option>";
	}
	echo "</select>";
	return true;
}

// bool update_nmcli(array $nmcli)
// Updates nmcli settings from $_POST['nmcli']
function update_nmcli() {
	debug_to_console('Updating nmcli');
	
	//Set mode if changed in form
	if ( !isset($_POST['nmcli']) )
		return false;
	else
		$nmcli = $_POST['nmcli'];
	
	// Escape all shell args for nmcli
	foreach( $nmcli as $key => $value ) {
		$nmcli[$key] = escapeshellarg($value);	
	}
	
	// Set DHCP
	if ( isset($nmcli['dhcp']) && $nmcli['dhcp'] == 'enabled' && $settings['dhcp'] != 'enabled') {
		replaceSetting('dhcp','enabled',"$server_files_dir/settings.ini");
	}
	elseif ( isset($_POST['dhcp']) && $_POST['dhcp'] == 'disabled' && $settings['dhcp'] != 'disabled') {
		replaceSetting('dhcp','disabled',"$server_files_dir/settings.ini");
	}
	
	//If SSID and KEY entered set them up
	if( isset($nmcli['ssid']) && ! empty($nmcli['ssid']) && isset($nmcli['key']) && !empty($nmcli['key']) ) {
		exec('sudo nmcli con delete '.$nmcli['ssid']);
		exec('sudo nmcli con add con-name '.$nmcli['ssid'].' ifname wlan0 type wifi ssid '.$nmcli['ssid']);
		exec('sudo nmcli con modify '.$nmcli['ssid'].' wifi-sec.key-mgmt '.$nmcli['enc']);
		exec('sudo nmcli con modify '.$nmcli['ssid'].' wifi-sec.psk '.$nmcli['key']);
	}
	//If IP and Gateway specified, add to setup
	if( isset($nmcli['dhcp']) && $nmcli['dhcp']=='enabled' && isset($nmcli['ip'], $nmcli['gateway']) && !empty($nmcli['ip']) && !empty($nmcli['gateway']) ) {
		$argIP = escapeshellarg($nmcli['ip']."/24 ".$nmcli['gateway']);
		exec('sudo nmcli con mod '.$nmcli['ssid'].' ipv4.addresses '.$argIP);
	}
	
	// Forget network if instructed
	if ( isset($nmcli['networks_forget']) && $nmcli['networks_forget'] != '--' ) {
		exec("sudo nmcli con delete ".$nmcli['networks_forget']);
	}
	
	// Put network up if instructed
	if ( isset($nmcli['networks_join']) && $nmcli['networks_join'] != '--' ) {
		nmcli_network_up(false,$nmcli['networks_join']);
	}
	
	return true;
}

// array nmcli_get_networks([string $append])
// Returns an array of nmcli networks, plus $append as a placeholder at position 0
// This only returns wireless networks
function nmcli_get_networks($append = false) {
	$network_names = array_filter(explode("\n", shell_exec('sudo nmcli -t -f NAME con show')) );
	$network_types = array_filter(explode("\n", shell_exec('sudo nmcli -t -f TYPE con show')) );
	
	// Build array, trip eth and 'AP' connections
	foreach( $network_names as $key => $value )
		if( $value != 'AP' && $network_types[$key] != '802-3-ethernet' )
			$networks[$key] = array('name' => $network_names[$key], 'type' => $network_names[$key]);
	
	sort($networks);
	if( $append )
		array_unshift($networks, array('name' => $append, 'type' => 'placeholder') );
	return $networks;
}

// array nmcli_get_status([string $output])
// Returns an array based on $output
// Default - returns an array of devices and statuses
// 'connected' - returns an array of connected devices
function nmcli_get_status($output = false, $get_signal = false) {
	
	// Run commands to fill columns
	$dev = explode(PHP_EOL, shell_exec('nmcli -t -f device dev status'));
	$type = explode(PHP_EOL, shell_exec('nmcli -t -f type dev status'));
	$state = explode(PHP_EOL, shell_exec('nmcli -t -f state dev status'));
	$connection = explode(PHP_EOL, shell_exec('sudo nmcli -t -f connection dev status')); 
	$master = array(); 
	
	// Parse columns
	foreach( $dev as $key => $value ) {
		if( $value != 'lo' && !empty($value) ) {
			$master[$key] = array('dev' => $dev[$key], 'type' => $type[$key], 'state' => $state[$key], 'con' => $connection[$key],'ip' => get_ip($dev[$key]) );
		
			// If connected and wireless, get signal strength info
			if( $get_signal && $master[$key]['state'] == 'connected' && $master[$key]['type'] == 'wifi' && $master[$key]['con'] != 'AP' ) {
				$db = explode(' ',shell_exec('iw dev wlan0 link | grep signal'))[1];
				if($db <= -100)
					$quality = 0;
				else if($db >= -50)
					$quality = 100;
				else
        			$quality = 2 * ($db + 100);
				$master[$key]['signal'] = $quality;
			}
		}
	}
	
	if( !$output )
		return $master;
	else if ( $output == 'connected' ) {
		$return = array();
		foreach( $master as $key => $value )
			if( $value['state'] == 'connected' ) 
				array_push($return, $master[$key]);
		return $return;
	}
	else if ( $output == 'wifi' ) {
		$return = array();
		foreach( $master as $key => $value ) {
			if( $value['type'] == 'wifi' ) {
				array_push($return, $master[$key]);
			}
		}
		return $return;
	}
	else
		return false;
	$local_ip = rtrim(shell_exec("ifconfig | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'"));
}

//string get_ip([string $dev])
// Returns a list of local ip information
// Optional $dev specifies a single device (ex 'wlan0')
function get_ip($dev = false) {
	if( !$dev )
		$local_ip = rtrim(shell_exec("ifconfig | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'"));
	else
		$local_ip = rtrim(shell_exec("ifconfig ".$dev." | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'"));
	return $local_ip;
	
}
		
		

// bool nmcli_network_up([string $dev], [string $network])
// Puts up nmcli network $network or device $dev
function nmcli_network_up($dev = false, $network = false) {
	if( !$dev ) {
		exec("sudo nmcli con up ".$network, $output, $return_var);
		return $return_var;
	}
	else if( !$network ) {
		exec('sudo nmcli dev connect '.$dev, $output, $return_var);
		return $return_var;
	}
	else {
		exec('sudo nmcli dev connect '.$dev, $output, $return_var);
		return $return_var;
	}
	
}

function debug_to_console( $message ) {
	echo '<script>console.log( "PHP: '.$message.'");</script>';
}

function alert_error( $message ) {
	echo '<script>alert( "PHP: '.$message.'");</script>';
}



?>