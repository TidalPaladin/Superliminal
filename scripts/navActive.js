// JavaScript Document
// Sets the active navigation button based on current page

$(document).ready(function() {
	
	// Load header, set active button
	$('.add-nav').load('/links/top_header.php', function() {
		var matched = false;

		// Get name of current page
		if( document.location.pathname.match(/[^\/]+$/) )
			var current_file = document.location.pathname.match(/[^\/]+$/)[0];
		else
			var current_file = 'index.php';
		console.log('Setting active nav button: '+current_file);
		
		// Set proper navlist element to active
		$('.navlist').find('a').each(function(key, value) {
			var file = $(this).attr('href').match(/[^\/]+$/)[0];
			if ( file == current_file ) {
				$(this).parent().attr('class','active');
				matched = true;
			}
		});
		
		// Log to console if no match found
		if ( !matched )
			console.log('Header - No active page found');
	});
});