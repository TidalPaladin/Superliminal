<head>
<title></title>
<link rel="stylesheet" type="text/css" href="/styles/wizard.css">
<script src="/scripts/jquery-2.1.4.min.js" type="text/javascript"></script>
</head>
<body>
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
	$('.general').each(function() {
		var setting = $(this).attr('name');
		console.log('setting '+setting+' to '+placeholders[setting]);
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
<div id="head">
	<H1>Superliminal v<?php echo $settings['version']?></H1>
</div>
<ul>
	<li><a href="../index.php">Start</a></li>
	<li class="active"><a href="general.php">General</a></li>
	<li><a href="connect.php">Network</a></li>
	<li><a href="dropbox.php">Dropbox</a></li>
</ul>
<div class='bigBox'>
	<div class='infoBox' style="width: 430px;">
	<H1>General Settings</H1>
		<form action="#"  id='general' onsubmit='return validateForm()' method="POST" style="width: 400px;">
			<div class="inputForm">
				<div class="row">
					<label>Username:</label>
					<input name="user" type="text" class="setting login"/>
				</div>
				<div class="row">
					<label>Password:</label>
					<input name="password" type="text" class="setting login"/>
				</div>
				<div class="row">
					<label>Mode:</label>
					<select class='setting' name="mode">
						<option id='mode' value="WIRED">Wired</option>
						<option id='mode' value="WIRELESS">Wireless</option>
					</select>
				</div>
				<div class="row">
					<label>Automatic Updates:</label>
					<select class='setting' name="auto_update">
						<option id='auto_update' value="update">Enabled</option>
						<option id='auto_update' value="noUpdate">Disabled</option>
					</select>
				</div>
				<div class="row">
					<label>Hotspot:</label>
					<select class='setting' name="hotspot">
						<option id='hotspot' value="enabled">Enabled</option>
						<option id='hotspot' value="disabled">Disabled</option>
					</select>
				</div>
				<div class="row">
					<label>Hotspot SSID:</label>
					<input name="ssid" type="text" class="setting general"/>
				</div>
				<div class="row">
					<label>Hotspot Passphrase:</label>
					<input name="wpa_passphrase" type="text" class="setting general"/>
				</div>
				<div class="row">
					<label>Slideshow Interval (s):</label>
					<input name="speed" type="numeric" class="setting general"/>
				</div>
				<div class="row">
					<label>Max Wait for Network (s):</label>
					<input name="maxWait" type="numeric" class="setting general"/>
				</div>
				<div class="row">
					<label>Max Wait for USB (s):</label>
					<input name="maxWaitUSB" type="numeric" class="setting general"/>
				</div>
				<div class="row">
					<label>Delay for TV (s):</label>
					<input name="bootcode_delay" type="numeric" class="setting general"/>
				</div>
				<div class="row">
					<label>Bootscreen Delay (s):</label>
					<input name="readDelay" type="numeric" class="setting general"/>
				</div>
				<div class="row">
					<label>Dropbox Config:</label>
					<?php $configs = array_slice($json,1); generateConfigFilesDropdown($configs, 'configFile');?>
				</div>
				<div class="row">
					<label>Overscan:</label>
					<select class='setting' name="overscan">
						<option id='overscan' value="enabled">Enabled</option>
						<option id='overscan' value="disabled">Disabled</option>
					</select>
				</div>
				<div class="row hidden_setting">
					<label>Horizontal Overscan:</label>
					<input name="overscan_x" type="numeric" class="setting general"/>
				</div>
				<div class="row hidden_setting">
					<label>Vertical Overscan:</label>
					<input name="overscan_y" type="numeric" class="setting general"/>
				</div>
			</div>
			<br>
			<input type="submit" id="submit" class="submit" value="Apply">
		</form>
		<input type="button" value="Reboot" class="submit" onclick="reboot()">
	</div>
</div>
</body>
