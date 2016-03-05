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
		if ( $value != 'AP' )
			echo "<option id='network' value='$value'> $value </option>\n";
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

// array nmcli_get_networks()
// Returns an array of nmcli networks, plus '--' as a placeholder at position 0
function nmcli_get_networks() {
	$networks = array_filter(explode("\n", shell_exec('sudo nmcli -t -f NAME con show')) );
	sort($networks);
	array_unshift( $networks, '--' );
	return $networks;
}

// bool nmcli_network_up([string $dev], [string $network])
// Puts up nmcli network $network or device $dev
function nmcli_network_up($dev = false, $network = false) {
	if( !$dev ) {
		$network = escapeshellarg($network);
		exec('sudo nmcli con up '.$network, $output, $return_var);
		return $return_var;
	}
	else if( !$network ) {
		$dev = escapeshellarg($dev);
		exec('sudo nmcli dev connect '.$dev, $output, $return_var);
		return $return_var;
	}
	else {
		$dev = escapeshellarg($dev);
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