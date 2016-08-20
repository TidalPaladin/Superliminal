// JavaScript Document
function finalTimer() {
	console.log('Starting final countdown');
	
	var readDelay = settings.readDelay;
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
		$('#timer').html('<h2>Launching slideshow in ' + (settings.readDelay-count-1) + 's... Press any key to enter setup.</h2>');
		count++;
		// When counter hits zero, launch slideshow
		if ( count >= readDelay) {
			clearInterval(finalCountDown);
			$('#timer').html('<h2>Launching slideshow</h2>');
			count++;
			console.log("Starting slideshow");
			
			// If debug mode enabled in startup.php, dont launch slideshow
			if( debug )
				return false;
			
			// Start slideshow
			window.location.replace('/slideshow.php'); 
		}
	}, 1000);
}

function dropbox() {
	console.log('Initializing Dropbox connection');
	$.ajax({
		url:"/scripts/ajax_actions.php",
		type: "POST",
		data: {'action':'dropbox'},
		dataType: 'json',
		success: function(data) {
			console.log('Ajax success - using Dropbox: ' + data['account']['display_name']);
			console.log('Fetched ' + data['downloaded'].length + ' files from Dropbox, deleted ' + data['deleted'].length + ' local files');

			// If success, display Dropbox info
			$('.bootScreen').css('background-image', "url('/resources/startup_display.jpg')");
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
			finalTimer();
		},
		error: function(jqXHR, textStatus, errorThrown){
			$('#error').show();
			$('#error_message').html(errorThrown);
			finalTimer();
		},
		complete: function(xhr, textStatus) {
			console.log(xhr.status);
		} 
	});
}
 
function network_wait(maxWait) {
	var count = 0;
	var hotspot = settings.hotspot;
	var interval = setInterval( function() {
		count++;
		$('#loadingText').html('Waiting ' + (maxWait-count) + 's for network');
		console.log('Making AJAX call');
		$.ajax({
			url:"/scripts/ajax_actions.php",
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
			url:"/scripts/ajax_actions.php",
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