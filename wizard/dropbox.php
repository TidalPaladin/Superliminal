<html>
<head>
<title></title>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.0/jquery.min.js"></script>
<link rel="stylesheet" type="text/css" href="/styles/wizard.css">
</head>
<body>
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

// Process 'Use Config' and 'Delete Config'
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

function importFile($target_dir,$inputName) {
	$target_file = $target_dir . basename($_FILES[$inputName]["name"]);
	foreach ($_FILES as $key => $value) {
		move_uploaded_file($_FILES[$inputName]["tmp_name"], $target_file);
	} 
}

?>
<div id="head">
	<H1>Superliminal v<?php echo $settings['version']?></H1>
</div>
<ul>
	<li><a href="/index.php">Start</a></li>
	<li><a href="/wizard/general.php">General</a></li>
	<li><a href="/wizard/connect.php">Network</a></li>
	<li class="active"><a href="/wizard/dropbox.php">Dropbox</a></li>
</ul>

<div class="bigBox">
	<div class="infoBox" style='width:50%; max-width: 400px;'>
	<H1>Add Dropbox Account</H1>
	<div id='link'>
		<P> Press the button below to link a Dropbox account.  You
			will be taken to dropbox.com to authorize the app<br>
			<br>
			Return to this page once Dropbox is authorized </P>
		<input type="button" style="max-width: 200px; min-width: 150px" name="start" value="Link Dropbox" class="submit" onclick="changeForm()">
	</div>
		<form id="finish" onsubmit="alert('Token saved')" action="#" method="POST" style="margin: 0 auto; width: 95%; display:none">
		<P> Enter a name for this config file and paste the code given by dropbox.com </P>
		<br>
		<div class='inputForm'>
			<div class="row">
				<label>Give config file a name:</label>
				<input name="configFile" type="text" class="setting"/>
			</div>
			<br>
			<div class="row">
				<label>Paste authenication here:</label>
				<input name="authCode" type="text" class="setting"/>
			</div>
		</div>
			<br>
			<input type="submit" style="max-width: 300px" name="finish" value="Apply" class="submit">
		</form>
	</div>
	<div class='infoBox' style='width:50%; max-width: 400px; border-left:0;'>
		<H1>Manage Accounts</H1>
		<form id="configs" action="#" method="POST" style="">
			<div  class='inputForm'>
				<div class="row">
					<label>Use Config:</label>
					<?php $configs = array_slice($json,1);
						generateConfigFilesDropdown($configs, 'configFile');?>
				</div>
				<br>
				<div class="row">
					<label>Delete Config:</label>
					<?php $configs = array_slice($json,1);
						$configs['--'] = '--';
						ksort($configs);
						generateConfigFilesDropdown($configs, 'remove');?>
				</div>
			</div>
			<br>
			<input type="submit" style="max-width: 300px" name="submit" value="Apply" class="submit">
		</form>
	</div>
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

/*$(function() {
  $("input[type='file'].filepicker").filepicker();
});*/
</script>

</body>
