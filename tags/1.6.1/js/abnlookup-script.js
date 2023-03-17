function itsg_gf_abnlookup_function( field_id, field_validate_abnlookup, field_dateFormat ){
	var ajax_url = gf_abnlookup_settings.ajax_url;
	var validation_message_loading = gf_abnlookup_settings.validation_message_loading;
	var validation_message_not_valid = gf_abnlookup_settings.validation_message_not_valid;
	var validation_message_error_communicating = gf_abnlookup_settings.validation_message_error_communicating;
	var validation_message_11_char = gf_abnlookup_settings.validation_message_11_char;
	var text_checking = gf_abnlookup_settings.text_checking;
	var text_check_abn = gf_abnlookup_settings.text_check_abn;
	var gst_value_yes = gf_abnlookup_settings.gst_value_yes;
	var gst_value_no = gf_abnlookup_settings.gst_value_no;
	var lookup_timeout = gf_abnlookup_settings.lookup_timeout;
	var lookup_retries = gf_abnlookup_settings.lookup_retries;

	(function( $ ) {
		"use strict";
		var checkABR = function( data ){
			var request = $.ajax({
				type: 'POST',
				url: ajax_url,
				data: data,
				tryCount : 0,
				retryLimit : lookup_retries,
				beforeSend: function(){
					gform_validation_message.hide();
					itsg_abnlookup_response.html( validation_message_loading );
					itsg_abnlookup_checkabn_button.val( text_checking + ' ... ' );
					itsg_abnlookup_response.addClass( 'loading' );
					itsg_abnlookup_response.removeClass( 'error Active Cancelled validation_message' );
					},
					success: function(response){
						if(typeof response !== 'undefined' ){
							try {
								console.log (response);
								itsg_abnlookup_checkabn_button.val( text_check_abn );
								var result = JSON.parse(response);
								if ( result["exception"] != undefined ) {
									gform_validation_message.hide();
									if ( 'Search text is not a valid ABN or ACN' == result['exception']['exceptionDescription'] ) {
										itsg_abnlookup_response.html( validation_message_not_valid );
									} else {
										itsg_abnlookup_response.text( result['exception']['exceptionDescription'] );
									}
									itsg_abnlookup_response.removeClass( 'Active Cancelled loading' );
									itsg_abnlookup_response.addClass( 'error validation_message' );
									gform_abnlookup_entity_gst_field.hide();
									gform_abnlookup_entity_type_field.hide();
									gform_abnlookup_entity_status_field.hide();
									gform_abnlookup_entity_name_field.hide();
									gform_abnlookup_entity_postcode_field.hide();
									gform_abnlookup_entity_state_field.hide();
									gform_abnlookup_entity_effective_from.hide();
									gform_abnlookup_gst_effective_from.hide();
									gform_abnlookup_entity_type_field_input.val('').change();
									gform_abnlookup_entity_status_field_input.val('').change();
									gform_abnlookup_entity_name_field_input.val('').change();
									gform_abnlookup_entity_postcode_field_input.val('').change();
									gform_abnlookup_entity_state_field_input.val('').change();
									gform_abnlookup_entity_effective_from_input.val('').change();
									gform_abnlookup_gst_effective_from_input.val('').change();
									gform_abnlookup_entity_gst_field_yes.prop( 'disabled', false );
									gform_abnlookup_entity_gst_field_no.prop( 'disabled', false );
									gform_abnlookup_entity_gst_field_yes.prop( 'checked', false );
									gform_abnlookup_entity_gst_field_no.prop( 'checked', false );
									gform_abnlookup_entity_type_field_input.prop( 'readonly', false );
									gform_abnlookup_entity_status_field_input.prop( 'readonly', false );
									gform_abnlookup_entity_name_field_input.prop( 'readonly', false );
									gform_abnlookup_entity_postcode_field_input.prop( 'readonly', false );
									gform_abnlookup_entity_state_field_input.prop( 'readonly', false );
									gform_abnlookup_entity_effective_from_input.prop( 'readonly', false );
									gform_abnlookup_gst_effective_from_input.prop( 'readonly', false );
								} else if ( result['businessEntity'] != undefined ) {
									console.log(result['businessEntity']);
									var entityTypeCode = result['businessEntity']['entityType']['entityTypeCode'];
									var entityType = result['businessEntity']['entityType']['entityDescription'];
									var entityStatus = result['businessEntity']['entityStatus']['entityStatusCode'];
									var entityPostcode = result['businessEntity']['mainBusinessPhysicalAddress']['postcode'];
									var entityState = result['businessEntity']['mainBusinessPhysicalAddress']['stateCode'];
									var entityEffectiveFrom = result['businessEntity']['entityStatus']['effectiveFrom'];
									// format the date to match the field format
									var date = new Date( entityEffectiveFrom );
									var day = ( '0' + date.getDate() ).slice(-2);
									var month = ( '0' + ( date.getMonth() + 1 ) ).slice(-2);
									var year = date.getFullYear();
									// get date format
									if ( gform_abnlookup_entity_effective_from_input.hasClass('dmy') ) {
										var entityEffectiveFromFormatted = day + '/' + month + '/' + year;
									} else if ( gform_abnlookup_entity_effective_from_input.hasClass('dmy_dash') ) {
										var entityEffectiveFromFormatted = day + '-' + month + '-' + year;
									} else if ( gform_abnlookup_entity_effective_from_input.hasClass('dmy_dot') ) {
										var entityEffectiveFromFormatted = day + '.' + month + '.' + year;
									} else if ( gform_abnlookup_entity_effective_from_input.hasClass('ymd_slash') ) {
										var entityEffectiveFromFormatted = year + '/' + month + '/' + date;
									} else if ( gform_abnlookup_entity_effective_from_input.hasClass('ymd_dash') ) {
										var entityEffectiveFromFormatted = year + '-' + month + '-' + date;
									} else if ( gform_abnlookup_entity_effective_from_input.hasClass('ymd_dot') ) {
										var entityEffectiveFromFormatted = year + '.' + month + '.' + date;
									} else {
										var entityEffectiveFromFormatted = month + '/' + day + '/' + year;
									}

									if ( result['businessEntity']['goodsAndServicesTax'] != undefined ) {
										var GSTEffectiveFrom = result['businessEntity']['goodsAndServicesTax']['effectiveFrom'];
										// format the date to match the field format
										var date = new Date( GSTEffectiveFrom );
										var day = ( '0' + date.getDate() ).slice(-2);
										var month = ( '0' + ( date.getMonth() + 1 ) ).slice(-2);
										var year = date.getFullYear();
										// get date format
										if ( gform_abnlookup_entity_effective_from_input.hasClass('dmy') ) {
											var GSTEffectiveFromFormatted = day + '/' + month + '/' + year;
										} else if ( gform_abnlookup_gst_effective_from_input.hasClass('dmy_dash') ) {
											var GSTEffectiveFromFormatted = day + '-' + month + '-' + year;
										} else if ( gform_abnlookup_gst_effective_from_input.hasClass('dmy_dot') ) {
											var GSTEffectiveFromFormatted = day + '.' + month + '.' + year;
										} else if ( gform_abnlookup_gst_effective_from_input.hasClass('ymd_slash') ) {
											var GSTEffectiveFromFormatted = year + '/' + month + '/' + date;
										} else if ( gform_abnlookup_gst_effective_from_input.hasClass('ymd_dash') ) {
											var GSTEffectiveFromFormatted = year + '-' + month + '-' + date;
										} else if ( gform_abnlookup_gst_effective_from_input.hasClass('ymd_dot') ) {
											var GSTEffectiveFromFormatted = year + '.' + month + '.' + date;
										} else {
											var GSTEffectiveFromFormatted = month + '/' + day + '/' + year;
										}

										gform_abnlookup_gst_effective_from_input.val( GSTEffectiveFromFormatted ).change();
										gform_abnlookup_gst_effective_from_input.prop( 'readonly', true );
										gform_abnlookup_gst_effective_from.show();
									} else {
										gform_abnlookup_gst_effective_from.hide();
										gform_abnlookup_gst_effective_from_input.val('').change();
									}

									if ( entityTypeCode == 'IND' ) {
										var familyName = 'string' == typeof result['businessEntity']['legalName']['familyName'] ? result['businessEntity']['legalName']['familyName'] : '';
										var givenName = 'string' == typeof result['businessEntity']['legalName']['givenName'] ? result['businessEntity']['legalName']['givenName'] : '';
										var otherGivenName = 'string' == typeof result['businessEntity']['legalName']["otherGivenName"] ? result['businessEntity']['legalName']["otherGivenName"] : '';
										var entityName = familyName + ", " + givenName + " " + otherGivenName;
									} else {
										var entityName = result['businessEntity']['mainName']['organisationName'];
									}
									gform_validation_message.hide();
									itsg_abnlookup_response.text( entityStatus + ' - ' + entityName );
									gform_abnlookup_entity_type_field_input.val( entityType ).change();
									gform_abnlookup_entity_type_field_input.prop( 'readonly', true );
									gform_abnlookup_entity_status_field_input.val( entityStatus ).change();
									gform_abnlookup_entity_status_field_input.prop( 'readonly', true );
									gform_abnlookup_entity_name_field_input.val( entityName ).change();
									gform_abnlookup_entity_name_field_input.prop( 'readonly', true );
									gform_abnlookup_entity_postcode_field_input.val( entityPostcode ).change();
									gform_abnlookup_entity_postcode_field_input.prop( 'readonly', true );
									gform_abnlookup_entity_state_field_input.val( entityState ).change();
									gform_abnlookup_entity_state_field_input.prop( 'readonly', true );
									gform_abnlookup_entity_effective_from_input.val( entityEffectiveFromFormatted ).change();
									gform_abnlookup_entity_effective_from_input.prop( 'readonly', true );
									itsg_abnlookup_response.removeClass( 'error loading validation_message' );
									itsg_abnlookup_response.addClass( entityStatus );
									if (result['businessEntity']['goodsAndServicesTax'] != undefined && result['businessEntity']['goodsAndServicesTax']['effectiveTo'] == '0001-01-01' ) {
										gform_abnlookup_entity_gst_field_yes.prop( 'checked', true ).click();
										gform_abnlookup_entity_gst_field_yes.prop( 'disabled', false );
										gform_abnlookup_entity_gst_field_no.prop( 'disabled', true );
									} else {
										gform_abnlookup_entity_gst_field_no.prop( 'checked', true ).click();
										gform_abnlookup_entity_gst_field_no.prop( 'disabled', false );
										gform_abnlookup_entity_gst_field_yes.prop( 'disabled', true );
									}
									gform_abnlookup_entity_gst_field.show();
									gform_abnlookup_entity_type_field.show();
									gform_abnlookup_entity_name_field.show();
									gform_abnlookup_entity_status_field.show();
									gform_abnlookup_entity_postcode_field.show();
									gform_abnlookup_entity_state_field.show();
									gform_abnlookup_entity_effective_from.show();
								}
							} catch( error ){
								console.log( response );
								itsg_abnlookup_response.text( error );
								itsg_abnlookup_response.removeClass( 'loading Active Cancelled' );
								itsg_abnlookup_response.addClass( 'error validation_message' );
							}
						}
					},
				error: function ( request, status, error ) {
					if ( 'timeout' == status ) {
						this.tryCount++;
						if ( this.tryCount <= this.retryLimit ) {
							//try again
							$.ajax( this );
							return;
						}
						itsg_abnlookup_response.text( validation_message_error_communicating );
					} else {
						itsg_abnlookup_response.text( request.responseText );
					}
					itsg_abnlookup_checkabn_button.val( text_check_abn );
					itsg_abnlookup_response.removeClass( 'loading Active Cancelled' );
					itsg_abnlookup_response.addClass( 'error validation_message' );
				},
				timeout: lookup_timeout // set timeout to 5 seconds
			});
		return request;
		};

		var request = false;

		var gform_abnlookup_field = $( '.gform_abnlookup_field_' + field_id + ' input[type="text"]' );
		var gform_validation_message = $( '.gform_abnlookup_field_' + field_id + ' .gfield_description.validation_message' );
		var itsg_abnlookup_response = $( '.itsg_abnlookup_response_' + field_id + '' );
		var itsg_abnlookup_checkabn_button = $( '.itsg_abnlookup_checkabn_' + field_id + '' );
		var gform_abnlookup_entity_gst_field = $( '.gform_abnlookup_entity_gst_field_' + field_id + '' );
		var gform_abnlookup_entity_gst_field_yes = $( '.gform_abnlookup_entity_gst_field_' + field_id + ' input[value="' + gst_value_yes + '"]' );
		var gform_abnlookup_entity_gst_field_no = $( '.gform_abnlookup_entity_gst_field_' + field_id + ' input[value="' + gst_value_no + '"]' );
		var gform_abnlookup_entity_type_field = $( '.gform_abnlookup_entity_type_field_' + field_id + '' );
		var gform_abnlookup_entity_type_field_input = $( '.gform_abnlookup_entity_type_field_' + field_id + ' input' );
		var gform_abnlookup_entity_status_field = $( '.gform_abnlookup_entity_status_field_' + field_id + '' );
		var gform_abnlookup_entity_status_field_input = $( '.gform_abnlookup_entity_status_field_' + field_id + ' input' );
		var gform_abnlookup_entity_name_field = $( '.gform_abnlookup_entity_name_field_' + field_id + '' );
		var gform_abnlookup_entity_name_field_input = $( '.gform_abnlookup_entity_name_field_' + field_id + ' input' );
		var gform_abnlookup_entity_postcode_field = $( '.gform_abnlookup_entity_postcode_field_' + field_id + '' );
		var gform_abnlookup_entity_postcode_field_input = $( '.gform_abnlookup_entity_postcode_field_' + field_id + ' input' );
		var gform_abnlookup_entity_state_field = $( '.gform_abnlookup_entity_state_field_' + field_id + '' );
		var gform_abnlookup_entity_state_field_input = $( '.gform_abnlookup_entity_state_field_' + field_id + ' input' );
		var gform_abnlookup_entity_effective_from = $( '.gform_abnlookup_entity_effective_from_field_' + field_id + '' );
		var gform_abnlookup_entity_effective_from_input = $( '.gform_abnlookup_entity_effective_from_field_' + field_id + ' input' );
		var gform_abnlookup_gst_effective_from = $( '.gform_abnlookup_gst_effective_from_field_' + field_id + '' );
		var gform_abnlookup_gst_effective_from_input = $( '.gform_abnlookup_gst_effective_from_field_' + field_id + ' input' );

		if ( '' !== itsg_abnlookup_response.html() ) {

			// if pre-filled fields are empty - trigger ABN Lookup
			if ( ( gform_abnlookup_entity_gst_field_yes.is( ':visible' )
				&& true != gform_abnlookup_entity_gst_field_yes.prop( 'checked' )
				&& true != gform_abnlookup_entity_gst_field_no.prop( 'checked' ) )
				|| ( gform_abnlookup_entity_type_field_input.is( ':visible' )
				&& '' == gform_abnlookup_entity_type_field_input.val() )
				|| ( gform_abnlookup_entity_status_field_input.is( ':visible' )
				&& '' == gform_abnlookup_entity_status_field_input.val() )
				|| ( gform_abnlookup_entity_name_field_input.is( ':visible' )
				&& '' == gform_abnlookup_entity_name_field_input.val() )
				|| ( gform_abnlookup_entity_postcode_field_input.is( ':visible' )
				&& '' == gform_abnlookup_entity_postcode_field_input.val() )
				|| ( gform_abnlookup_entity_state_field_input.is( ':visible' )
				&& '' == gform_abnlookup_entity_state_field_input.val() )
				|| ( gform_abnlookup_entity_effective_from_input.is( ':visible' )
				&& '' == gform_abnlookup_entity_effective_from_input.val() )
				|| ( gform_abnlookup_gst_effective_from_input.is( ':visible' )
				&& '' == gform_abnlookup_gst_effective_from_input.val() )				) {
					gform_abnlookup_field.trigger( 'change' );
				}

			// disable GST field that isnt currently used
			if ( gform_abnlookup_entity_gst_field_yes.is( ':checked' ) ) {
				gform_abnlookup_entity_gst_field_no.prop( 'disabled', true );
			} else if ( gform_abnlookup_entity_gst_field_no.is( ':checked' ) ) {
				gform_abnlookup_entity_gst_field_yes.prop( 'disabled', true );
			}

			// set fields to read only
			gform_abnlookup_entity_type_field_input.prop( 'readonly', true );
			gform_abnlookup_entity_status_field_input.prop( 'readonly', true );
			gform_abnlookup_entity_name_field_input.prop( 'readonly', true );
			gform_abnlookup_entity_postcode_field_input.prop( 'readonly', true );
			gform_abnlookup_entity_state_field_input.prop( 'readonly', true );
			gform_abnlookup_entity_effective_from_input.prop( 'readonly', true );
			gform_abnlookup_gst_effective_from_input.prop( 'readonly', true );
		} else {

			// hide linked fields
			gform_abnlookup_entity_gst_field.closest( 'li.gfield' ).hide()
			gform_abnlookup_entity_type_field.closest( 'li.gfield' ).hide();
			gform_abnlookup_entity_status_field.closest( 'li.gfield' ).hide();
			gform_abnlookup_entity_name_field.closest( 'li.gfield' ).hide();
			gform_abnlookup_entity_postcode_field.closest( 'li.gfield' ).hide();
			gform_abnlookup_entity_state_field.closest( 'li.gfield' ).hide();
			gform_abnlookup_entity_effective_from.closest( 'li.gfield' ).hide();
			gform_abnlookup_gst_effective_from.closest( 'li.gfield' ).hide();
		}

		gform_abnlookup_field.unbind( 'change' ).change( function() {
			var numbersOnly = $( this ).val().replace(/\D/g, '' );
			if ( 11 == numbersOnly.length ) {
				console.log( numbersOnly );
				var abn = numbersOnly;
				var data = {
					'action': 'itsg_gf_abnlookup_check_ajax',
					'abn': abn
				};
				if( request && 4 !== request.readyState ){
					console.log( 'Abort! -- another request has been submitted.' )
					request.abort();
				}

				request = checkABR( data );
			} else {
				gform_abnlookup_entity_gst_field.hide();
				gform_abnlookup_entity_type_field.hide();
				gform_abnlookup_entity_status_field.hide();
				gform_abnlookup_entity_name_field.hide();
				gform_abnlookup_entity_postcode_field.hide();
				gform_abnlookup_entity_state_field.hide();
				gform_abnlookup_entity_effective_from.hide();
				gform_abnlookup_gst_effective_from.hide();
				gform_abnlookup_entity_type_field_input.val('').change();
				gform_abnlookup_entity_status_field_input.val('').change();
				gform_abnlookup_entity_name_field_input.val('').change();
				gform_abnlookup_entity_postcode_field_input.val('').change();
				gform_abnlookup_entity_state_field_input.val('').change();
				gform_abnlookup_entity_effective_from_input.val('').change();
				gform_abnlookup_gst_effective_from_input.val('').change();
				gform_abnlookup_entity_gst_field_yes.prop( 'disabled', false );
				gform_abnlookup_entity_gst_field_no.prop( 'disabled', false );
				gform_abnlookup_entity_gst_field_yes.prop( 'checked', false );
				gform_abnlookup_entity_gst_field_no.prop( 'checked', false );
				gform_validation_message.hide();
				itsg_abnlookup_response.html( validation_message_11_char );
				itsg_abnlookup_response.addClass( 'error validation_message' );
				itsg_abnlookup_response.removeClass( 'loading Active Cancelled' );
			}
		});
	}( jQuery ));
}

// runs the main function when the page loads
jQuery( document ).bind( 'gform_post_render gform_post_conditional_logic', function( $ ) {
	var abnlookup_fields = gf_abnlookup_settings.abnlookup_fields;
	var form_id = gf_abnlookup_settings.form_id;
	for ( var key in abnlookup_fields ) {
		// skip loop if the property is from prototype
		if ( !abnlookup_fields.hasOwnProperty( key ) ) continue;

		var field_id = key;
		var field_validate_abnlookup = abnlookup_fields[ key ]['validate'];
		var field_dateFormat = abnlookup_fields[ key ]['dateFormat'];

		if( typeof field_validate_abnlookup !== 'undefined' ) {
			console.log( 'abn-lookup-for-gravity-forms :: field_id: ' + field_id + ' field_validate_abnlookup: ' + field_validate_abnlookup + ' field_dateFormat: ' + field_dateFormat );

			itsg_gf_abnlookup_function( field_id, field_validate_abnlookup, field_dateFormat );

			jQuery( '#input_' + form_id + '_' + field_id ).unbind( 'keydown' ).keydown( function( event ) {
				if ( 13 == event.which || 13 == event.keyCode ) {
					event.preventDefault();
					jQuery( this ).trigger( 'change' );
				}
			});
		}

	}
});