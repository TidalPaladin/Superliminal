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
	try {
		list($accessToken, $dropboxUserId) = $webAuth->finish($authCode);
	}
	catch(Exception $e) {
		nmcli_network_up('wlan0');
		list($accessToken, $dropboxUserId) = $webAuth->finish($authCode);
	}
	$json[$_POST['configFile']] = $accessToken;
	file_put_contents( "$server_files_dir/accounts.json", json_encode($json) );
}

// Upload accounts.json file
function process_accounts_upload() {
	$dest = '/var/www/html/server_files/accounts.json';
	$content = json_decode(file_get_contents($_FILES['uploadFile']['tmp_name']), true);
	
	// If accounts.json missing app key/seret, add default in
	if( !isset($content['dropbox_app_key_secret']) ) {
		$key_secret = array('dropbox_app_key_secret' => array("key" => "oxi7abm1z604pk6", "secret" => "n5y4z2e9dz84btr"));
		$content = array_merge($key_secret,$content);
	}
	file_put_contents($dest, json_encode($content, JSON_PRETTY_PRINT));

	// Make sure permissions are ok
	chmod($dest,0775);
	return $content;
}

if( isset($_FILES['uploadFile']['tmp_name']) && !empty($_FILES['uploadFile']['tmp_name']) )
	$json = process_accounts_upload();

// Set up Dropbox
require_once "/var/www/html/Dropbox/autoload.php"; 
use \Dropbox as dbx;	
try {
	$appInfo = dbx\AppInfo::loadFromJson($json['dropbox_app_key_secret']);
	$webAuth = new dbx\WebAuthNoRedirect($appInfo, "PHP-Example/1.0", "en");
	$authorizeUrl = $webAuth->start();
} catch(Exception $e) {
	alert_error('Problem with accounts.json file, please try reuploading');
}


?>
<script>
$(document).ready( function() {
	
	//Remove class tag from 'Delete Config' so selected tag doesn't change
	$('#remove').removeClass('ini_select');
	
	// Set current config placeholder
	ini_select_placeholders();
});
</script>

<div class='mainBackground'>
<table class='spacedTable' style='max-width:600px'>
	<thead>
		<tr>
			<td><H1 style='text-decoration:underline'>Dropbox Settings</H1></td>
			<td align='right'>
				<a href='/help/help-dropbox.html' target='_blank'>Help</a>
			</td>
		</tr>
	</thead>
	<form id='finish' method="POST" class='inputForm' onsubmit='return validateForm()' enctype="multipart/form-data">
		<tbody colspan='2' style='border:thin solid black'>
			<tr>
				<td colspan='2'><H2>Add Dropbox Account</H2></td>
			<tr>
			<tr>
				<td colspan='2'><H4>Step 1 - Authorize Superliminal on Dropbox.com</H4></td>
			</tr>
			<tr>
				<td colspan='2'><P> Press the button below to link a Dropbox account.
					You will be taken to dropbox.com to authorize Superliminal.
					Return to this page once Dropbox is authorized. </P>
				</td>
			</tr>
			<tr>
				<td colspan='2'>
					<a href='<?php echo $authorizeUrl ?>' target='_blank' style="max-width: 200px;" name="start" value="Link Dropbox" class="submit">Link Dropbox</a>
				</td>
			</tr>
			<tr><td><br></td></tr>
			<tr>
				<td colspan='2'><H4>Step 2 - Paste Authorization Token</H4></td>
			</tr>
			<tr>
				<td colspan='2'><P> Enter a name that Superliminal will remember this account by and paste the code given by 
				dropbox.com when you authorized Superliminal.</P></td>
			</tr>
			<tr>
				<td>Account Name:</td>
				<td><input placeholder='Name this config file' id='dropbox_name' name="configFile" type="text" class="setting"/></td>
			</tr
			><tr>
				<td>Dropbox Token:</td>
				<td><input placeholder='Paste token here' id='dropbox_token' name="authCode" type="text" class="setting"/></td>
			</tr>
		</tbody>
		<tr><td><br></tr></tr>
		<tbody>
			<tr>
				<td colspan='2'><H2>Manage Accounts</H2></td>
			</tr>
			<tr>
				<td colspan='2'><H4>Download/Upload</H4></td>
			</tr>
			<tr>
				<td colspan='2'><P>Here you can download or upload a master list of all linked dropbox accounts.
				This file will be saved as accounts.json and any upload should be a .json file.</P></td>
			</tr>
			<tr>
				<td>Download Account File</td>
				<td><a href='/server_files/accounts.json' target='_blank' class='setting button' download>
				Download
				</a></td>
			<tr>
				<td>Upload Account File</td>
				<td><input type="file" accept='.json' id='uploadFile' name="uploadFile"></td>
			</tr>
			<tr>
				<td colspan='2'><H4>Activate/Delete</H4></td>
			</tr>
			<tr>
				<td colspan='2'><P>Choose the linked Dropbox account to use or delete an account from the
				master list.</P></td>
			</tr>
			<tr>
				<td>Use Config:</td>
				<td><?php $configs = array_slice($json,1);
								generateConfigFilesDropdown($configs, 'setting_ini[configFile]');?>
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
			<tr>
				<td colspan='2'><br><input type="submit" style="max-width: 300px" name="finish" value="Apply" class="submit"></td>
			</tr>
		</tbody>
	</form>
</table>

</div>

</body>
