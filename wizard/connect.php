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
	<script src="/scripts/functions.js" type="text/javascript"></script>
	<script src="/scripts/jquery-validate.js" type="text/javascript"></script>
</head>

<body>
<div class="add-nav">
	<script src='/scripts/navActive.js'></script>
</div>

<?php
$root = realpath($_SERVER["DOCUMENT_ROOT"]);
require "$root/scripts/functions.php";


//Set mode if coming from setup wizard
if ( isset($_GET['mode']) && strtoupper($_GET['mode']) == 'WIRELESS' ) {
	replaceSetting('mode','WIRELESS',"$server_files_dir/settings.ini");
}

// Update $_POST nmcli
update_nmcli();
?>

<script>
$(document).ready(function() {
	
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
			<form action="#" onsubmit="return validateForm( $(this) )" method="POST" id="connect">
				<tr>
					<td>SSID (Network name):</td>
					<td><input class='setting ini_free_response' id='ssid_connect' name="nmcli[ssid]" type="text"/></td>
				</tr>
				<tr>
					<td>Passphrase:</td>
					<td><input class='setting ini_free_response passphrase' id='key_connect' name="nmcli[key]" type="text"/></td>
				</tr>
				<tr>
					<td>Encryption:</td>
					<td><select class='setting ini_select' id="enc_connect" name="nmcli[enc]">
							<option value="wpa-psk">WPA 1/2 Personal</option>
							<option value="wpa-none">WEP</option>
							<option value="wpa-eap">WPA Enterprise</option>
							<option value="none">Open</option>
						</select></td>
				</tr>
				<tr>
					<td>Use DHCP:</td>
					<td><select class='setting ini_select' id='dhcp' name="setting_ini[dhcp]">
							<option id='dhcp' value="enabled">Enabled</option>
							<option id='dhcp' value="disabled">Disabled</option>
						</select></td>
				</tr>
				<tr class="hidden_setting">
					<td>Static IP:</td>
					<td><input class='setting ini_free_response' id='ip' name="nmcli[ip]" type="text"/></td>
				</tr>
				<tr class="hidden_setting">
					<td>Gateway:</td>
					<td><input class='setting ini_free_response' id='gateway' name="nmcli[gateway]" type="text"/></td>
				</tr>
				<tr>
					<td>Forget Network:</td>
					<td><?php generateNetworksDropdown( nmcli_get_networks(), 'networks_forget' );?></td>
				</tr>
				<tr>
					<td>Join Network:</td>
					<td><?php generateNetworksDropdown( nmcli_get_networks(), 'networks_join' );?></td>
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
