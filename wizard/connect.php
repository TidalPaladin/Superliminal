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

File: connect.php
Description: Network settings configuration page

Details:
Networks are managed using 'network-manager' and 'nmcli', the latter must have
sudo permissions for 'www-data'.  Network scanning is done using iw with the
option 'ap-force'.  This is required for proper scanning while SUperliminal is
in AP mode.  An access point connection named 'AP' must be added to
network-manager (it is hidden on this web page so the user cannot delete it).

-----------------------------------------------------------------------------------
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

<html>
<head>
<title>Superliminal - Connect</title>
<link rel="stylesheet" type="text/css" href="/styles/wizard.css">
<script src="/scripts/jquery-2.1.4.min.js" type="text/javascript"></script>
</head>

<body>
<div class="add-nav">
	<script src='/links/navActive.js'></script>
</div>

<?php

// Set up constants
$server_files_dir = '/var/www/html/server_files';
$settings = parse_ini_file("$server_files_dir/settings.ini");

// Replace the value of $setting with $newOption in $file
function replaceSetting($setting, $newOption, $file) {
	exec('sed -i s/.*'.$setting.'=.*/'.$setting.'='.$newOption.'/g '.$file);
}

function generateNetworksDropdown($array) {
	echo "<select class='setting' name='networks'>";
	foreach ($array as $key => $value) {
		if ( $value != 'AP' )
			echo "<option id='network' value='$value'> $value </option>\n";
	}
	echo "</select>";
}

//Set mode if coming from setup wizard
if ( isset($_GET['mode']) && strtoupper($_GET['mode']) == 'WIRELESS' ) {
	replaceSetting('mode','WIRELESS',"$server_files_dir/settings.ini");
}

// Set DHCP
if ( isset($_POST['dhcp']) && $_POST['dhcp'] == 'enabled' && $settings['dhcp'] != 'enabled') {
	replaceSetting('dhcp','enabled',"$server_files_dir/settings.ini");
}
elseif ( isset($_POST['dhcp']) && $_POST['dhcp'] == 'disabled' && $settings['dhcp'] != 'disabled') {
	replaceSetting('dhcp','disabled',"$server_files_dir/settings.ini");
}

//Set mode if changed in form
if ( isset($_POST['mode']) ) {
	if ($_POST['mode'] == 'WIRELESS' && $settings['mode'] != 'WIRELESS' ) {
		replaceSetting('mode','WIRELESS',"$server_files_dir/settings.ini");
	}
	elseif ( $_POST['mode'] == 'WIRED' && $settings['mode'] != 'WIRED' ) {
		replaceSetting('mode','WIRED',"$server_files_dir/settings.ini");
	}
}
	
//If SSID and KEY entered set them up
if( isset($_POST['ssid']) && ! empty($_POST['ssid']) && isset($_POST['key']) && !empty($_POST['key']) ) {
	$argSSID = escapeshellarg($_POST['ssid']);
	$argKEY = escapeshellarg($_POST['key']);
	$argENC = escapeshellarg($_POST['enc']);
	exec('sudo nmcli con delete '.$argSSID);
	exec('sudo nmcli con add con-name '.$argSSID.' ifname wlan0 type wifi ssid '.$argSSID);
	exec('sudo nmcli con modify '.$argSSID.' wifi-sec.key-mgmt '.$argENC);
	exec('sudo nmcli con modify '.$argSSID.' wifi-sec.psk '.$argKEY);
}
//If IP and Gateway specified, add to setup
if( isset($_POST['dhcp']) && $_POST['dhcp']=='enabled' && isset($_POST['ip'], $_POST['gateway']) && !empty($_POST['ip']) && !empty($_POST['gateway']) ) {
	$argIP = escapeshellarg($_POST['ip']."/24 ".$_POST['gateway']);
	exec('sudo nmcli con mod '.$argSSID.' ipv4.addresses '.$argIP);
}

// Forget network if instructed
if ( isset($_POST['networks']) && $_POST['networks'] != '--' ) {
	exec("sudo nmcli con delete \"".$_POST['networks']."\"");
}

$settings = parse_ini_file("$server_files_dir/settings.ini");
?>
<script>
$(document).ready(function() {
	// Set mode selector
	var mode = "<?php echo $settings['mode'] ?>";
	if ( mode == 'WIRELESS' ) {
		$('#wireless').prop('selected',true);
		$('#wifiSettings').show();
	}
	else if ( mode == 'WIRED' ) {
		$('#wired').prop('selected',true);
		$('#wifiSettings').hide();
	}
	
	// Hide/show DHCP settings initially
	if( $("[name=dhcp]").val() == 'disabled' )
		$('.hidden_setting').show();
	else
		$('.hidden_setting').hide();
	
	// Hide/show DHCP settings on toggle
	$(document).on('change', "[name=dhcp]", function() {
		if( $(this).val() == 'disabled' )
			$('.hidden_setting').show();
		else
			$('.hidden_setting').hide();
	});
	
	scan();
});

function scan() {
	$.ajax({
			url:"/server_actions.php",
			type: "POST",
			data: { 'action':'scan'},
			dataType: 'json',
			success: function(data) {
				console.log('AJAX Scan success');
				var list = [];
				$.each(data, function(index, value) {
					list.push('<tr><td><P>' + value['ssid'] + '</P></td><td><P>' + value['signal']+'</P><td></tr>');
				});
				$('#networks').append(list);
				
				$('#loading').hide();
				$('#networks').show();
			},
			error: function(jqXHR, textStatus, errorThrown){
				console.log(errorThrown);
			}
		});
}

function validateForm() {
	var pass = $('[name="key"]').val();
	var ssid = $('[name="ssid"]').val();
	if ( pass.length > 63 || pass.length < 8 && ssid != '' ) {
		if ( pass.length == 0 )
			alert('Passphrase cannot be empty');
		else 
			alert('Passphrase must be between 8 and 63 characters');
		return false;
	}
	else if ( ssid == "" && pass != '' ) {
		alert('Passphrase set but SSID is empty');
		return false;
	}
	else
		return true;
}
	

function modeChange(elem){
	if(elem.val() == "WIRED") {
    	$('#wifiSettings').hide();
		$('#submit').val("Apply");
	}
	else {
		$('#wifiSettings').show();
		$('#submit').val("Connect");
	}
}

$("#network").submit(function(event) {
	alert('Saving settings...');
	<?php $settings = parse_ini_file("$server_files_dir/settings.ini");?>
});
</script>
<div class='mainBackground'>
<table>
	<tr>
		<td>
			<table class='spacedTable'>
			<thead>
				<tr>
					<td><H2>Wireless</H2></td>
					<td align='right'>
						<a href='/help/help-connect.html' target='_blank'>Help</a>
					</td>
				</tr>
			</thead>
			<tbody>
			<form action="#" onsubmit='return validateForm()' method="POST" id="network">
				<tr>
					<td>SSID:</td>
					<td><input class='setting'name="ssid" type="text"/></td>
				</tr>
				<tr>
					<td>Passphrase:</td>
					<td><input class='setting' name="key" type="text"/></td>
				</tr>
				<tr>
					<td>Encryption:</td>
					<td><select class='setting' id="enc" name="enc" onchange="modeChange( $(this) );">
							<option value="wpa-psk">WPA 1/2 Personal</option>
							<option value="wpa-none">WEP</option>
							<option value="wpa-eap">WPA Enterprise</option>
							<option value="none">Open</option>
						</select></td>
				</tr>
				<tr>
					<td>Use DHCP:</td>
					<td><select class='setting' name="dhcp">
							<option id='dhcp' value="enabled">Enabled</option>
							<option id='dhcp' value="disabled">Disabled</option>
						</select></td>
				</tr>
				<tr class="hidden_setting">
					<td>Static IP:</td>
					<td><input class='setting' name="ip" type="text"/></td>
				</tr>
				<tr class="hidden_setting">
					<td>Gateway:</td>
					<td><input class='setting' name="gateway" type="text"/></td>
				</tr>
				<tr>
					<td>Forget Network:</td>
					<td><?php 
				$networks = array_filter(explode("\n", shell_exec('sudo nmcli -t -f NAME con show')) );
				sort($networks);
				array_unshift( $networks, '--' );
				generateNetworksDropdown( $networks );
				?></td>
				</tr>
				<tr>
					<td colspan='2'><input type="submit" name='submit' id="submit" class="submit" value="Apply"></td>
				</tr>
			</form>
				</tbody>
			
		</table>
		</td>
		<td style='border: thin solid black'>
			<table>
				<thead>
					<tr>
						<td colspan='2'><H2 style='text-align:center'>Available Networks</H2></td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td colspan='2' align='center'>
							<div class='loadBar' id='loading'> <img id='loadImage' style='width:80px; text-align:center' src='/resources/ajax-loader.gif'> <br>
							<H3 id='loadingText'>Scanning</H3></div>
						</td>
					</tr>
				</tbody>
				<tbody id="networks" style='display:none'>
					<tr>
						<td><P style="text-decoration:underline;">SSID</P></td>
						<td><P style="text-decoration: underline">Strength</P></td>
					</tr>
				</tbody>
			</table>
		</td>
	</tr>	
</table>
</div>
</body>
