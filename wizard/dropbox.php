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

File: dropbox.php
Description: Menu for linking a Dropbox account to Superliminal

Details:
This web page gives a user options for linking or removing a Dropbox account from
Superliminal.  Linked accounts, along with Superliminal's app key are stored in
/server_files/accounts.json.  This page also allows a user to choose which saved
Dropbox account to use for displaying images.

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
<title>Superliminal - Dropbox</title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="/styles/wizard.css">
</head>
<body>
<div class="add-nav">
	<script src='/links/navActive.js'></script>
</div>

<?php
	
// Set up generic constants
$server_files_dir = '/var/www/html/server_files';
$json=json_decode(file_get_contents("$server_files_dir/accounts.json"), true);
$settings = parse_ini_file("$server_files_dir/settings.ini");

// Set up Dropbox
require_once "/var/www/html/Dropbox/autoload.php"; 
use \Dropbox as dbx;
$appInfo = dbx\AppInfo::loadFromJson($json['dropbox_app_key_secret']);
$webAuth = new dbx\WebAuthNoRedirect($appInfo, "PHP-Example/1.0", "en");
$authorizeUrl = $webAuth->start();

// Process 'Use Config' and 'Delete Config' options
if ( is_writable("$server_files_dir/settings.ini") ) {
	foreach ($_POST as $key => $value) {
		if ( !empty($value) && $value != '--' ){
			if ( $key == 'configFile' )
				replaceSetting($key, $value, "$server_files_dir/settings.ini");
			else if ( $key == 'remove' && $value != 'dropbox_app_key_secret' )
				unset($json[$value]);	
		} 
	}
}
else
	echo 'Settings file not writable';
	

// Add config element to JSON array
if ( isset($_POST['authCode']) && !empty($_POST['authCode']) ) {
	$authCode = $_POST['authCode'];
	list($accessToken, $dropboxUserId) = $webAuth->finish($authCode);
	$json[$_POST['configFile']] = $accessToken;
}

// Push JSON array to file
file_put_contents( "$server_files_dir/accounts.json", json_encode($json) );

// Generate a <select> with all linked Dropbox accounts
function generateConfigFilesDropdown($array, $id) {
	echo "<select id=$id name=$id>";
	foreach ($array as $key => $value) {
		if ( $key != 'dropbox_app_key_secret' )
			echo "<option id='configFile' value='$key'> $key </option>\n";
	}
	echo "</select>";
}

// Replace a setting in $file
function replaceSetting($setting, $newOption, $file) {
	if ( !empty($newOption) ) {
		exec('sed -i s/.*'.$setting.'=.*/'.$setting.'='.$newOption.'/g '.$file);
	}
}

if( isset($_FILES['uploadFile']['tmp_name']) )
  echo 'File uploaded';
foreach($_FILES as $key => $value)
	echo $key.' '.$value;

?>

<div class='mainBackground'>
<table class='spacedTable'>
	<thead>
		<tr>
			<td><H1>Dropbox Settings</H1></td>
			<td align='right'>
				<a href='/help/help-dropbox.html' target='_blank'>Help</a>
			</td>
		</tr>
	</thead>
	<form id='finish' class='inputForm'>
		<tbody>
			<tr>
				<td colspan='2'><H2>Add Dropbox Account</H2></td>
			<tr>
			<tr>
				<td colspan='2'><P> Press the button below to link a Dropbox account.<br>
					You will be taken to dropbox.com to authorize the app<br>
					<br>
					Return to this page once Dropbox is authorized </P>
				</td>
			</tr>
			<tr>
				<td colspan='2'>
					<input type="button" style="max-width: 200px; min-width: 150px" name="start" value="Link Dropbox" class="submit" onclick="changeForm()">
				</td>
			</tr>
			<tr><td colspan='2'><P> Enter a name for this config file and paste the code given by dropbox.com </P></td></tr>
			<tr>
				<td>Account Name:</td>
				<td><input placeholder='Name of config file' name="configFile" type="text" class="setting"/></td>
			</tr
			><tr>
				<td>Dropbox Token:</td>
				<td><input placeholder='Paste Token Here' name="authCode" type="text" class="setting"/></td>
			</tr>
		</tbody>
		<tbody>
			<tr>
				<td colspan='2'><H2>Manage Accounts</H2></td>
			</tr>
			<tr>
				<td>Download Account File</td>
				<td><a class='settings' href='/server_files/accounts.json' download>
				<button>Download</button>
				</a></td>
			<tr>
				<td>Upload Account File</td>
				<td><input type="file" name="uploadFile"></td>
			</tr>
			<tr>
				<td>Use Config:</td>
				<td><?php $configs = array_slice($json,1);
								generateConfigFilesDropdown($configs, 'configFile');?>
				</td>
			</tr>
			<tr>
				<td>Delete Config:</td>
				<td><?php $configs = array_slice($json,1);
								$configs['--'] = '--';
								ksort($configs);
								generateConfigFilesDropdown($configs, 'remove');?>
				</td>
			</tr>
		</tbody>
	</form>
</table>
<input type="submit" style="max-width: 300px" name="finish" value="Apply" class="submit">
</div>
<script>
$(document).ready( function() {
	
	// Set current config placeholder
	<?php $settings = parse_ini_file("$server_files_dir/settings.ini"); ?>
	var placeholders = <?php echo json_encode($settings)?>;
	var matched = false;
	$('#configFile option').each(function() {
		var setting = $(this).attr('id');
		if ( $(this).val() == placeholders[setting] ) {
			$(this).attr('selected',true);
			matched = true;
		}		
	});
	if ( ! matched )
		$('#configFile').append('<option value="" selected>File missing</option>');
});

// Open new tab to Dropbox authenticate and change form
function changeForm() {
	$('#finish').show();
	$('#link').hide();
	window.open("<?php echo $authorizeUrl ?>");
}
</script>
</body>
