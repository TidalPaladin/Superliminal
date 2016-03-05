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

File: index.php
Description: Start screen used as a partial wizard

------------------------------------------------------------------------------------
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
<title>Superliminal - Start</title>
<link rel="stylesheet" type="text/css" href="styles/wizard.css">
<script src="/scripts/jquery-2.1.4.min.js" type="text/javascript"></script>
</head>
<body>
<div class="add-nav">
	<script src='/scripts/navActive.js'></script>
</div>
<div class='bigBox'>
	<div class="infoBox">
		<H2>Welcome to the configuration wizard</H2>
		<P style="text-align: center; display: inline-block;">Please select if you plan to use USB or WiFi mode</P>
		<br>
		<form style="width:400px" method="GET">
			<div class="buttonBox">
				<input type="submit" name="mode" value="Wireless" class="submit" id="buttonBox" formaction="/wizard/connect.php">
				<input type="submit" name="mode" value="USB" class="submit" id="buttonBox" formaction="/wizard/general.php">
			</div>
		</form>
	</div>
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

File: index.php
Description: Start screen used as a partial wizard

------------------------------------------------------------------------------------
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
<title>Superliminal - Start</title>
<link rel="stylesheet" type="text/css" href="styles/wizard.css">
<script src="/scripts/jquery-2.1.4.min.js" type="text/javascript"></script>
</head>
<body>
<div class="add-nav">
	<script src='/links/navActive.js'></script>
</div>
<div class='bigBox'>
	<div class="infoBox">
		<H2>Welcome to the configuration wizard</H2>
		<P style="text-align: center; display: inline-block;">Please select if you plan to use USB or WiFi mode</P>
		<br>
		<form style="width:400px" method="GET">
			<div class="buttonBox">
				<input type="submit" name="mode" value="Wireless" class="submit" id="buttonBox" formaction="/wizard/connect.php">
				<input type="submit" name="mode" value="USB" class="submit" id="buttonBox" formaction="/wizard/general.php">
			</div>
		</form>
	</div>
</div>
</body>
>>>>>>> origin/master
