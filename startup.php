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
<script src="/scripts/functions.js" type="text/javascript"></script>
<script src="/scripts/startup.js" type="text/javascript"></script>
</head>
<body class="bootScreen" onkeypress="window.location.href='/index.php';">

<?php
$root = realpath($_SERVER["DOCUMENT_ROOT"]);
require "$root/links/functions.php";

$configFiles = array_slice($json,1);
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
		<table>
			<thead>
				<H2>Account Info</H2>
			</thead>
			<tbody>
				<tr>
					<td>Account:</td>
					<td id='account'></td>
				</tr>
				<tr>
					<td>Email:</td>
					<td id='email'></td>
				</tr>
				<tr>
					<td>Local IP:</td>
					<td id='ip'><?php echo rtrim(shell_exec("ifconfig | grep -Eo 'inet (addr:)?([0-9]*\.){3}[0-9]*' | grep -Eo '([0-9]*\.){3}[0-9]*' | grep -v '127.0.0.1'"))?></td>
				</tr>
			</tbody>
		</table>
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
var settings = <?php echo json_encode($settings);?>;
var debug = true;

$(document).ready( function() {
	
	$('#error').hide();
	
	// Display wireless information on boot
	if ( settings.mode == 'WIRELESS' ) {
		console.log('Starting in wireless mode, waiting for network');
		network_wait(settings.maxWait, settings.hotspot, settings.readDelay);
		
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