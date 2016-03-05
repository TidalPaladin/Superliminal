// JavaScript Document
// Sets the active navigation button based on current page

$(document).ready(function() {
	
	// Load header, set active button
	$('.add-nav').load('/links/top_header.php', function() {
		console.log('Setting active nav button');
		var matched = false;
		
		// Get name of current page
		var current_file = document.location.pathname.match(/[^\/]+$/)[0];
		
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