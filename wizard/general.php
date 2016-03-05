<<<<<<< HEAD
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
			<tr><td colspan='2'><H2>Login</H2></td></tr>
			<tr>
				<td>Username:</td></td>
				<td><input id='name' name="user" type="text" class="setting login"/></td>
			</tr>
			<tr>
				<td>Password:</td>
				<td><input id='password' name="password" type="text" class="setting login"/></td>
			</tr>
			<td><br></td>
			<tr><td colspan='2'><H2>Hotspot</H2></td></tr>
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
			<td><br></td>
			<tr><td colspan=2><H2>System</H2></td></tr>
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
			<tr><td colspan='2'><div class="sublabel"><h4>Wait Times</h4></div></td></tr>
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
			<tr><td colspan='2'><div class="sublabel"><h4>Overscan</h4></div></td></tr>
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
				<td><input type="submit" id="submit" class="submit" value="Apply" style='background-color:green'></td>
				<td><input type="button" value="Reboot" class="submit" onclick="reboot()"></td>
			</tr>
		</tbody>
	</table>
</form>
</div>
</body>
=======
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
</head>
<body>
<div class="add-nav">
	<script src='/links/navActive.js'></script>
</div>
<?php

// Set up generic constants
$server_files_dir='/var/www/html/server_files';
$json=json_decode(file_get_contents("$server_files_dir/accounts.json"), true);
$appInfo = $json['dropbox_app_key_secret'];
$settings = parse_ini_file("$server_files_dir/settings.ini");

// Function to replace $setting = $newOption in a given $file
function replaceSetting($setting, $newOption, $file) {
	if ( true ) {
		exec('sed -i s/.*'.$setting.'=.*/'.$setting.'='.$newOption.'/g '.$file);
	}
}

// Generate <select> of id=$id using elements of $array
function generateConfigFilesDropdown($array, $id) {
	echo "<select class='setting' id=$id name=$id>\r\n";
	foreach ($array as $key => $value) {
		if ( $key != 'dropbox_app_key_secret' )
			echo "\t\t\t\t<option id='configFile' value='$key'>$key</option>\r\n";
	}
	echo "\t\t\t</select>\r\n";
}

// Insert changed into settings.ini
foreach ($_POST as $key => $value) {
	$value = preg_replace("/[^A-Za-z0-9 ]/", '', trim($value,' '));
	if ( ( !empty($value) || is_numeric($value) ) && $key != 'user' && $key != 'password') {
		replaceSetting($key, $value, "$server_files_dir/settings.ini");
	}
}

// Insert changed password into .htpasswd
if( isset($_POST['user'], $_POST['password']) && !empty($_POST['user']) && !empty($_POST['password']) ) {
	$user = $_POST['user'];
	$pass = crypt($_POST['password'], base64_encode($_POST['password']));
	$file = fopen('/var/www/html/server_files/.htpasswd','r+');
	ftruncate($file,0);
	fwrite($file,$user.':'.$pass);
	fclose($file);
}
	


// Update overscan on the fly if needed
if ( isset($_POST['overscan']) && $_POST['overscan'] == 'enabled' ) {
	$args = array(trim($_POST['overscan_x'],' '), trim($_POST['overscan_y'],' '));
	if ( ( !empty($args[0]) || is_numeric($args[0]) ) && ( !empty($args[1]) || is_numeric($args[1]) ) ){
			exec('sudo /var/www/html/server_files/overscan '.$args[1].' '.$args[1].' '.$args[0].' '.$args[0], $output, $return);
	}
}


// Set mode if coming from start page
if ( isset($_GET['mode']) && $_GET['mode'] == 'USB' )
	replaceSetting('mode','WIRED',"$server_files_dir/settings.ini");
	
$settings = parse_ini_file("$server_files_dir/settings.ini");
?>
<script>

$(document).ready(function() {
	
	// Get placeholder settings from settings.ini
	var placeholders = <?php echo json_encode($settings)?>;
	
	// Setting placeholders for setting inputs
	console.log('Setting placeholders');
	$('.general').each(function() {
		var setting = $(this).attr('name');
		//console.log('setting '+setting+' to '+placeholders[setting]);
		$(this).attr('placeholder', placeholders[setting]);
	});
	
	// Set default value for all <select>
	$('select').each( function() {
		var matched = false; 
		$(this).children('option').each(function() {
			var setting = $(this).attr('id');
			if ( $(this).val() == placeholders[setting] ) {
				$(this).attr('selected',true);
				matched = true;
				return false;
			}
		});
		
		// If no match found, set the value to file missing
		if ( !matched )
			$(this).append("\r\n<option value='' selected>File missing</option>");
	});
	
	// Hide/show overscan settings initially
	if( $("[name=overscan]").val() == 'enabled' )
		$('.hidden_setting').show();
	else
		$('.hidden_setting').hide();
	
	// Hide/show overscan settings on toggle
	$(document).on('change', "[name=overscan]", function() {
		if( $(this).val() == 'enabled' )
			$('.hidden_setting').show();
		else
			$('.hidden_setting').hide();
	});
	
});

// Called on form submit to make sure user didn't screw up
function validateForm() {
	
	// Make sure all numeric inputs are ok
	if ( !function(){ 
			$('[type="numeric"]').each(function() {
				var input = $(this).val();
				if ( !jQuery.isNumeric(input) && input.length > 0) {
					alert("Invalid non-numeric input \""+input+"\"");
					return false;
				}
			});
	} )
	return false;

	// Check that ssid/paspshrase are ok
	if ( !function(){
		var pass = $('[name="wpa_passphrase"]').val();
		var ssid = $('[name="ssid"]').val();
		if ( pass.length > 63 || pass.length < 8 && pass != "" ) {
			alert('Passphrase must be between 8 and 63 characters');
			return false;
		}
	} )
	return false;
	
	else 
	return true;
}

function reboot() {
	$.ajax({
			url:"/reboot.php"
		});
	alert('Rebooting...');
	location.reload();
}

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
			<tr><td colspan='2'><H2>Login</H2></td></tr>
			<tr>
				<td>Username:</td></td>
				<td><input name="user" type="text" class="setting login"/></td>
			</tr>
			<tr>
				<td>Password:</td>
				<td><input name="password" type="text" class="setting login"/></td>
			</tr>
			<td><br></td>
			<tr><td colspan='2'><H2>Hotspot</H2></td></tr>
				<td>Hotspot:</td>
				<td><select class='setting' name="hotspot">
					<option id='hotspot' value="enabled">Enabled</option>
					<option id='hotspot' value="disabled">Disabled</option>
				</select></td>
			</tr>
			<tr>
				<td>SSID:</td>
				<td><input name="ssid" type="text" class="setting general"/></td>
			</tr>
			<tr>
				<td>Passphrase:</td>
				<td><input name="wpa_passphrase" type="text" class="setting general"/></td>
			</tr>
			<td><br></td>
			<tr><td colspan=2><H2>System</H2></td></tr>
			<tr>
				<td>Mode:</td>
				<td><select class='setting' name="mode">
					<option id='mode' value="WIRED">USB</option>
					<option id='mode' value="WIRELESS">Network</option>
				</select></td>
			</tr>
			<tr>
				<td>Dropbox Account:</td>
				<td><?php $configs = array_slice($json,1); generateConfigFilesDropdown($configs, 'configFile');?></td>
			</tr>
			<tr><td colspan='2'><div class="sublabel"><h4>Wait Times</h4></div></td></tr>
			<tr>
				<td>Max Wait for Network (s):</td>
				<td><input name="maxWait" type="numeric" class="setting general"/></td>
			</tr>
			<tr>
				<td>Max Wait for USB (s):</td>
				<td><input name="maxWaitUSB" type="numeric" class="setting general"/></td>
			</tr>
			<tr>
				<td>Slideshow Interval (s):</td>
				<td><input name="speed" type="numeric" class="setting general"/></td>
			</tr>
			<tr>
				<td>Delay for TV (s):</td>
				<td><input name="bootcode_delay" type="numeric" class="setting general"/></td>
			</tr>
			<tr>
				<td>Bootscreen Delay (s):</td>
				<td><input name="readDelay" type="numeric" class="setting general"/></td>
			</tr>
			<tr><td colspan='2'><div class="sublabel"><h4>Overscan</h4></div></td></tr>
			<tr>
				<td>Overscan:</td></td>
				<td><select class='setting' name="overscan">
					<option id='overscan' value="enabled">Enabled</option>
					<option id='overscan' value="disabled">Disabled</option>
				</select></td>
			</tr>
			<tr class="overscan_values">
				<td>Horizontal Overscan:</td></td>
				<td><input name="overscan_x" type="numeric" class="setting general"/></td>
			</tr>
			<tr class="overscan_values">
				<td>Vertical Overscan:</td></td>
				<td><input name="overscan_y" type="numeric" class="setting general"/></td>
			</tr>
			<tr>
				<td><input type="submit" id="submit" class="submit" value="Apply" style='background-color:green'></td>
				<td><input type="button" value="Reboot" class="submit" onclick="reboot()"></td>
			</tr>
		</tbody>
	</table>
</form>
</div>
</body>
>>>>>>> origin/master
