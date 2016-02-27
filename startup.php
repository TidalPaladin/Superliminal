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

File: startup.php
Description: Shows relevant information on startup before launching slideshow

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
-->

<!doctype html>
<html>
<head>
<meta charset="UTF-8">
<meta http-equiv="Link" content="&lt;/slideshow.php&gt;; rel=prefetch">
<title>Startup</title>
<link rel="stylesheet" type="text/css" href="/styles/wizard.css">
<script src="/scripts/jquery-2.1.4.min.js" type="text/javascript"></script>
</head>
<body class="bootScreen" onkeypress="window.location.href='/index.php';">

<?php
error_reporting();

// Set up generic constants
$server_files_dir = '/var/www/html/server_files';
$json = json_decode(file_get_contents("$server_files_dir/accounts.json"), true);
$configFiles = array_slice($json,1);
$appInfo = $json['dropbox_app_key_secret'];
$settings = parse_ini_file("$server_files_dir/settings.ini");
?> 

<div id='dropbox' class='info' style='display: none;'>
	<div id='left_panel' class='panel_grid'>
	<div class='console' style='width: 75%; height: 80%;'>
		<H2>Flyer List</H2>
		<br>
		<div id='list' class='arrayContainer'></div>
	</div>
	</div>
	<div id='right_panel' class='panel_grid'>
	<div class='console' style=''>
		<H2>Account Info</H2>
		<br>
		<div style='display:table; width:100%'>
		<div class='row'>
			<label>Account:</label>
			<div id='account' class='setting'></div>
		</div>
		<br>
		<div class='row'>
			<label>Email:</label>
			<div id='email' class='setting'></div>
		</div>
		<br>
		<div class='row'>
			<label>Local IP:</label>
			<div id='ip' class='setting'><?php echo rtrim(shell_exec("ifconfig | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'"))?></div>
		</div>
		</div>
	</div>
	</div>
</div>

<div id='error' style="display:none">
	<H1 id='error_message'>Dropbox error...  Check configuration settings.</H1>
	<H2>Loading last used flyers</H2>
	<div id='detail_message'></div>
</div>
<br><br>
<div class='loadBar' id='loading'>
		<img id='loadImage' src='/resources/ajax-loader.gif'>
		<br>
		<H2 id='loadingText'>Loading</H2>
</div>

<div id='timer' style='display: none;'>
<h2>Launching slideshow in <?php echo $settings['readDelay']?>s... Press any key to enter setup.</H2>
</div>

<div id='wired' class='info'>
	<div class='console'>
		<H2>Using flyers:</H2>
		<div id='wiredlist' class='arrayContainer'></div>
	</div>
</div>
	
<br><br>
</div>	

<script>
function finalTimer() {
	var count = 0; 
	$('#timer').show();
	$('#loading').hide();
	
	// Bind keypress to stop timer
	$(document).bind('keypress', function() {
		clearInterval(finalCountDown);
		console.log("Entering setup");
		$('#timer').html('<h2>Entering setup...</h2>');
		window.location.replace('/index.php');
	});
	
	var finalCountDown = setInterval(function() {
		var settings = <?php echo json_encode($settings);?>;
		$('#timer').html('<h2>Launching slideshow in ' + (settings.readDelay-count-1) + 's... Press any key to enter setup.</h2>');
		count++;
		// When counter hits zero, launch slideshow
		if ( count >= settings.readDelay) {
			clearInterval(finalCountDown);
			$('#timer').html('<h2>Launching slideshow</h2>');
			count++;
			console.log("Starting slideshow");
			window.location.replace('/slideshow.php'); 
		}
	}, 1000);
}

function dropbox() {
	console.log('Initializing Dropbox connection');
	$.ajax({
		url:"/server_actions.php",
		type: "POST",
		data: {'action':'dropbox'},
		dataType: 'json',
		success: function(data) {
			console.log('Ajax success - using Dropbox: ' + data['account']['display_name']);
			console.log('Fetched ' + data['downloaded'].length + ' files from Dropbox, deleted ' + data['deleted'].length + ' local files');

			// If success, display Dropbox info
			$('#dropbox').show();
			$('#account').html(data['account']['display_name']);
			$('#email').html(data['account']['email']);
			
			// List local files
			var list = [];
			$.each(data['local'], function(index, value) {
				list.push('<P>' + value + '</P>');
			});
			$('#list').append(list);
			
			// List downloaded files
			var list = [];
			$.each(data['downloaded'], function(index, value) {
				list.push('<P>' + value + '</P>');
			});
			$('#downloaded').append(list);
			
			// Count down time remaining
			//finalTimer();
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#error').show();
			
			$('#error_message').html(errorThrown);
			console.log(errorThrown);
			finalTimer();
		},
		complete: function(xhr, textStatus) {
			console.log(xhr.status);
		} 
	});
}
 
function network_wait(maxWait) {
	var hotspot = '<?php echo $settings['hotspot']?>';
	var count = 0;
	var interval = setInterval( function() {
		count++;
		$('#loadingText').html('Waiting ' + (maxWait-count) + 's for network');
		console.log('Making AJAX call');
		$.ajax({
			url:"/server_actions.php",
			type: "POST",
			data: {'action':'connectivity', 'count':count},
			dataType: 'json',
			success: function(data) {
				if( data['ip'] != '' && data['internet'] ) {
					clearInterval(interval);
					console.log('Network connected, IP: '+data['ip']);
					$('#loadingText').html('Communicating with Dropbox');
					dropbox();
				}
				else if ( count >= maxWait ) {
					clearInterval(interval);
					$('#error_message').html('Network did not come up');
					$('#error').show();
					console.log('Network did not come up');
					console.log(data['count']+' > '+maxWait);
					// Put up hotspot
					if( hotspot == 'enabled' )
						$.ajax({
						url:"/server_actions.php",
						type: "POST",
						data: {'action':'hotspot'},
						dataType: 'json'
					});
					finalTimer(); 
				}
				else
					console.log('Network down, retrying ('+count+')');
			},
			error: function(jqXHR, textStatus, errorThrown){
			console.log(errorThrown); 
		}
		});
	}, 1000);		
}
 
function usb(maxWaitUSB) {
	var count = 0;
	var interval = setInterval( function() {
		count++;
		$('#loadingText').html('Waiting ' + (maxWaitUSB-count) + 's for USB');
		
		// Make ajax request to download from USB, get returned information
		$.ajax({
			url:"/server_actions.php",
			type: "POST",
			data: { 'action':'usb','count':count },
			dataType: 'json',
			success: function(data) {
				console.log('AJAX USB success');
				clearInterval(interval);
				// Show wired flyer information
				var list = [];
				$.each(data['flyers'], function(index, value) {
					list.push('<P>' + value + '</P>');
				});
				$('#wiredlist').append(list);
				$('#wired').show();
				finalTimer();
			},
			error: function(jqXHR, textStatus, errorThrown){
				if( count >= maxWaitUSB) {
					console.log('USB not detected');
					clearInterval(interval);
					$('#error').show();
					$('#error_message').html("USB not detected");
					finalTimer();
				}
			}
		});
	},1000);
}


$(document).ready( function() {
	var settings = <?php echo json_encode($settings);?>;
	$('#error').hide();
	
	// Display wireless information on boot
	if ( settings.mode == 'WIRELESS' ) {
		console.log('Starting in wireless mode, waiting for network');
		network_wait(settings.maxWait);
		
	}
	
	// Display USB information on boot
	else if ( settings.mode == 'WIRED' ) {
		console.log('Using USB mode');
		usb(settings.maxWaitUSB);
	}
});


</script>
</div>
</body>
</html>