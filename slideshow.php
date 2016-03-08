<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Flyer Slideshow</title>

<script src="scripts/jquery-2.1.4.min.js" type="text/javascript"></script>
<script src="scripts/jquery.cycle2.min.js" type="text/javascript"></script>
<script type="text/javascript">

$(document).ready(function(){
	
	if( !$('#myslides').html() )
		$('#error').show();
	
	$('#myslides').cycle({
		fit: 1, pause: 2,
		fx: 'none',
		timeout: 10000
	});
});
</script>
<link rel="stylesheet" href="styles/dynamicslides.css" type="text/css" media="screen" />
</head>
<body>

<?php
$directory = 'flyers/';
try {
	// Styling for images  
	echo "<div id=\"myslides\">";
	foreach ( new DirectoryIterator($directory) as $item ) {
		if ($item->isFile()) {
			$path = $directory.$item;
			$file_parts = pathinfo($item)['extension'];
			$imgExt = Array("jpg","JPG","png","PNG","jpeg","JPEG");
			if (in_array($file_parts, $imgExt)){
				echo "<img src=\"" . $path . "\" />";
			} else{
				echo "File not acceptable";
				}
		}
	}
	echo "</div>";
}
catch(Exception $e) {
}
?>

<div id='error' style='display:none'>
<h1>No images found</h1>
</div>
</body>
</html>
