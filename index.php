<html>
<head>
<title>Configuration Wizard</title>
<?php $server_files_dir = '/var/www/html/server_files';
$settings = parse_ini_file("$server_files_dir/settings.ini");?>
<link rel="stylesheet" type="text/css" href="styles/wizard.css">
<div id="head">
	<H1>Superliminal v<?php echo $settings['version']?></H1>
</div>
</head>
<body>
<ul>
	<li class="active"><a href="index.php">Start</a></li>
	<li><a href="/wizard/general.php">General</a></li>
	<li><a href="/wizard/connect.php">Network</a></li>
	<li><a href="/wizard/dropbox.php">Dropbox</a></li>
</ul>
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
