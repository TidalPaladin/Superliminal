<!--
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

File: general.php
Description: Allows user to configure basic parameters of Superliminal

Details:
Most program settings are stored in /server_files/settings.ini.  Some settings
from settings.ini are inserted into other places during boot by rc.local and
reboot by /server_files/reboot.sh

----------------------------------------------------------------------------------
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
-->

<head>
<title>Superliminal - General</title>
<link rel="stylesheet" type="text/css" href="/styles/wizard.css">
<script src="/scripts/jquery-2.1.4.min.js" type="text/javascript"></script>
<script src="/scripts/functions.js" type="text/javascript"></script>
<link rel="shortcut icon" href="/resources/favicon.ico"/>
</head>
<body>
<div class="add-nav">
	<script src='/scripts/navActive.js'></script>
</div>
<?php
$root = realpath($_SERVER["DOCUMENT_ROOT"]);
require "$root/scripts/functions.php";

// Insert changed password into .htpasswd
if( isset($_POST['user'], $_POST['password']) && !empty($_POST['user']) && !empty($_POST['password']) ) {
	$user = $_POST['user'];
	$pass = crypt($_POST['password'], base64_encode($_POST['password']));
	$file = fopen('/var/www/html/server_files/.htpasswd','r+');
	ftruncate($file,0);
	fwrite($file,$user.':'.$pass);
	fclose($file);
}

// Set mode if coming from start page
if ( isset($_GET['mode']) && $_GET['mode'] == 'USB' )
	replaceSetting('mode','WIRED',"$server_files_dir/settings.ini");
	
?>
<script>

$(document).ready(function() {
	console.log(placeholders);
	
	$('#overscan').change( function() {
		$(this).off('change');
		alert('Enabling/disabling overscan requires a reboot to take effect');
	});
	
	// Set free response placeholders
	ini_input_placeholders();
	
	// Set select placeholders for settings.ini options
	ini_select_placeholders()
	
});

</script>

	
<div class='mainBackground'>
	<form action="#"  id='general' onsubmit='return validateForm()' method="POST">
	<table class='spacedTable'>
		<thead><tr>
			<td>
				<H1>General Settings</H1>
			</td>
			<td style='text-align:right'>
				<a href='/help/help-general.html' target='_blank'>Help</a>
			</td>
			</tr></thead>
		<tbody>
			<tr><td colspan='2'><H2 class='table_heading'>Login</H2></td></tr>
			<tr>
				<td>Username:</td></td>
				<td><input id='name' name="user" type="text" class="setting login"/></td>
			</tr>
			<tr>
				<td>Password:</td>
				<td><input id='password' name="password" type="text" class="setting login"/></td>
			</tr>
			<tr><td colspan='2'><H2 class='table_heading'>Hotspot</H2></td></tr>
				<td>Hotspot:</td>
				<td><select id='hotspot' class='setting ini_select' name="setting_ini[hotspot]">
					<option id='hotspot' value="enabled">Enabled</option>
					<option id='hotspot' value="disabled">Disabled</option>
				</select></td>
			</tr>
			<tr>
				<td>SSID:</td>
				<td><input id='ssid' name="setting_ini[ssid]" type="text" class="setting ini_free_response"/></td>
			</tr>
			<tr>
				<td>Passphrase:</td>
				<td><input id='wpa_passphrase' name="setting_ini[wpa_passphrase]" type="text" class="setting ini_free_response"/></td>
			</tr>
			<tr><td colspan=2><H2 class='table_heading'>System</H2></td></tr>
			<tr>
				<td>Mode:</td>
				<td><select id='mode' class='setting ini_select' name="setting_ini[mode]">
					<option id='mode' value="WIRED">USB</option>
					<option id='mode' value="WIRELESS">Network</option>
				</select></td>
			</tr>
			<tr>
				<td>Dropbox Account:</td>
				<td><?php $configs = array_slice($json,1); generateConfigFilesDropdown($configs, 'setting_ini[configFile]');?></td>
			</tr>
			<tr><td colspan='2'><h4 class='table_heading'>Wait Times</h4></td></tr>
			<tr>
				<td>Max Wait for Network (s):</td>
				<td><input id='maxWait' name="setting_ini[maxWait]" type="numeric" class="setting ini_free_response"/></td>
			</tr>
			<tr>
				<td>Max Wait for USB (s):</td>
				<td><input id='maxWaitUSB' name="setting_ini[maxWaitUSB]" type="numeric" class="setting ini_free_response"/></td>
			</tr>
			<tr>
				<td>Slideshow Interval (s):</td>
				<td><input id='speed' name="setting_ini[speed]" type="numeric" class="setting ini_free_response"/></td>
			</tr>
			<tr>
				<td>Delay for TV (s):</td>
				<td><input id='bootcode_delay' name="setting_ini[bootcode_delay]" type="numeric" class="setting ini_free_response"/></td>
			</tr>
			<tr>
				<td>Bootscreen Delay (s):</td>
				<td><input id='readDelay' name="setting_ini[readDelay]" type="numeric" class="setting ini_free_response"/></td>
			</tr>
			<tr><td colspan='2'><h4 class='table_heading'>Overscan</h4></td></tr>
			<tr>
				<td>Overscan:</td></td>
				<td><select id='overscan' class='setting ini_select' name="setting_ini[overscan]">
					<option id='overscan' value="enabled">Enabled</option>
					<option id='overscan' value="disabled">Disabled</option>
				</select></td>
			</tr>
			<tr class="overscan_values">
				<td>Horizontal Overscan:</td></td>
				<td><input id='overscan_x' name="setting_ini[overscan_x]" type="numeric" class="setting ini_free_response"/></td>
			</tr>
			<tr class="overscan_values">
				<td>Vertical Overscan:</td></td>
				<td><input id='overscan_y' name="setting_ini[overscan_y]" type="numeric" class="setting ini_free_response"/></td>
			</tr>
			<tr>
				<td><input type="submit" id="submit" class="submit" value="Apply"></td>
				<td><input type="button" value="Reboot" class="submit" onclick="reboot()"></td>
			</tr>
		</tbody>
	</table>
</form>
</div>
</body>
