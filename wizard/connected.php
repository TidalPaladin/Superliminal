<html>
<head>
    <title></title>
</head>
<body>
<br><br>
</body>

<?php
if(isset($_POST['ssid']) && isset($_POST['key'])) {
	//Format SSID and KEY as args
	$argSSID=escapeshellarg($_POST['ssid']);
	$argKEY=escapeshellarg($_POST['key']);

	//Add connection to nmcli
	shell_exec('sudo nmcli con delete '.$argSSID);
	shell_exec('sudo nmcli con add con-name '.$argSSID.' ifname wlan0 type wifi ssid '.$argSSID);
	shell_exec('sudo nmcli con modify '.$argSSID.' wifi-sec.key-mgmt wpa-psk');
	shell_exec('sudo nmcli con modify '.$argSSID.' wifi-sec.psk '.$argKEY);
	
	//If user specified static IP/gateway
	if(isset($_POST['ip']) && isset($_POST['gateway'])) {	
		
		//Format args ip/24 gw
		$argIP='\"'.escapeshellarg($_POST['ip']).'/24 '.escapeshellarg($_POST['gateway']).'\"';
		
		//Specify IP/gateway
		shell_exec('sudo nmcli con mod '.$argSSID.' ipv4.addresses '.$argIP);
	}
	//echo "Connected to ".shell_exec('iwgetid -r')."<br>IP: ".exec('hostname -I');
}
else {
	die('<br><br>Invalid network configuration');
}
?>
