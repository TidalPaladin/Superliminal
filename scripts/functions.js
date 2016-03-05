// JavaScript Document

// bool reboot()
// Makes AJAX call to server_actions.php for reboot
function reboot() {
	$.ajax({
			url:"/scripts/ajax_actions.php",
			type: "POST",
			data: { 'action':'reboot'},
			dataType: 'json',
			success: function() {
				return true;
			},
			error: function() {
				return false;
			}
		});
	alert('Rebooting...');
	location.reload();
}

// Called on form submit to make sure user didn't screw up
function validateForm($form) {
	console.log('Validating form'); 
	var error = false;
	var error_msg;
	var ssid;
	var encryption;
	
	// Clean up errors for multiple tries
	$('.error').each( function() {
		$(this).removeClass('error');
		$(this).children('p').remove();
		$(this).children('br').remove();
	});
		
	
	// Make sure all numeric inputs are ok
	$(document).find('[type="numeric"]').each(function() {
		if( !$.isNumeric($(this).val()) && $(this).val() )
			error = validation_error($(this), 'Must be a number');
	});
	
	$(document).find('#ssid_connect').each( function() {
		if( !$(this).val() )
			ssid = false;
		else
			ssid = true;
	});
	
	$(document).find('#enc_connect').each( function() {
		if( $(this).val() != 'none'  )
			encryption = true;
		else
			encryption = false;
	});
	
	// Process SSID/Passphrase pairs
	$(document).find('.passphrase').each( function() {
		if( !$(this).val() && encryption && ssid )
			error = validation_error($(this), 'Required when encryption is "Open"');
		else if( $(this).val() && !encryption )
			error = validation_error($(this), 'Must be empty when encryption is disabled');
		else if( $(this).val() && !ssid )
			error = validation_error($('#ssid_connect'), 'Required when passphrase is entered');
		else if( ($(this).val().length < 8 || $(this).val().length > 63) && ssid )
			error = validation_error($(this), 'Must be between 8 and 63 characters');	
	});
	
	// Process Dropbox name/token pairs
	if( $('#dropbox_name').val() && !$('#dropbox_token').val() )
		error = validation_error($('#dropbox_token'), 'Token required when account name entered');
	if( !$('#dropbox_name').val() && $('#dropbox_token').val() )
		error = validation_error($('#dropbox_name'), 'Name required when token entered');
		
	// Process accounts.json upload
	if( $('#uploadFile').val() ) {
		var ext = $('#my_file_field').val().split('.').pop().toLowerCase();
		if( ext != 'json' )
			error = validation_error($('#uploadFile'),'Must be a .json file');
	}
		
	// Return form validated or not
	if( !error )
		return true;
	else
		return false;
}

function validation_error(elem, error_msg) {
	elem.parent('td').addClass('error');
	elem.parent('td').append('<br><p>'+error_msg+'</p>');
	console.log('Validation error - '+elem.attr('id')+': '+elem.val()+' ('+error_msg+')');
	return true;
}
	

// Set default value for all <select>
function ini_select_placeholders() {
	console.log('Setting select placeholders');
	$('.ini_select').each(function() {
		var matched = false; 
		$(this).children('option').each(function() {
			var setting = $(this).attr('id');
			if ( $(this).val() == placeholders[setting] ) {
				$(this).attr('selected',true);
				matched = true;
				
				// Break from each
				return false;
			}
		});
		
		// If no match found, set the value to file missing
		if ( !matched )
			$(this).append("\r\n<option value='' selected>File missing</option>");
	});
}

// Setting placeholders for setting inputs
function ini_input_placeholders() {
	console.log('Setting input placeholders');
	$('.ini_free_response').each(function() {
		var setting = $(this).attr('id');
		$(this).attr('placeholder', placeholders[setting]);
	});
}

// Scans for networks on wlan0
function scan() {
	$.ajax({
			url:"/scripts/ajax_actions.php",
			type: "POST",
			data: { 'action':'scan'},
			dataType: 'json',
			success: function(data) {
				console.log('AJAX Scan success');
				var list = [];
				$.each(data, function(index, value) {
					list.push('<tr><td><P>' + value['ssid'] + '</P></td><td><P>' + value['signal']+'</P><td></tr>');
				});
				$('#networks').append(list);
				
				$('#loading').hide();
				$('#networks').show();
			},
			error: function(jqXHR, textStatus, errorThrown){
				console.log(errorThrown);
			}
		});
}

// General validation functions called by validateForm()
