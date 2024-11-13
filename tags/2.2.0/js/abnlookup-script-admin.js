function itsg_gf_abnlookup_form_has_abnlookup() {
	for (var key in form.fields) {
		// skip loop if the property is from prototype
	    if (!form.fields.hasOwnProperty(key)) continue;

	    var field = form.fields[key];
		if ('true'== field.enable_abnlookup) return true;
	}
}

jQuery(document).bind('gform_load_field_settings', function(event, field, form) {
	// we only need to add in the ABN Lookup result settings if we actually have an ABN lookup in the form
		
	var field_type = field['type'];
	if (!itsg_gf_abnlookup_form_has_abnlookup()){
		if ('text' == field_type) {

			// the fields
			var abnlookup_field = jQuery(".abnlookup_field_setting");
			var abnlookup_field_validate = jQuery(".abnlookup_validate_field_setting");
			var abnlookup_field_link = jQuery(this).find(".abnlookup_link_field_setting");
			var abnlookup_field_entity_results = jQuery(".abnlookup_entity_results_setting");
			var abnlookup_field_entity_results_setting = jQuery(".abnlookup_entity_results_field_setting_text");

			// lets display the options in the page
			abnlookup_field.show();

		
			// first remove existing list of options
			abnlookup_field_link.find('select option').remove();

			// now to create the list of options and assign to link field
			for (var i = 0; i < form.fields.length; i++) {
				if ('true' == form.fields[i].enable_abnlookup) {
					var value = form.fields[i].label;
					var key = form.fields[i].id;
					abnlookup_field_link.find('select').append('<option value=' + key + '>' + value + '</option>');
				}
			}

			// now get their values
			var enable_abnlookup_value = (typeof field['enable_abnlookup'] != 'undefined' && field['enable_abnlookup'] != '') ? field['enable_abnlookup'] : false;
			var abnlookup_field_entity_value = (typeof field['abnlookup_results_enable'] != 'undefined' && field['abnlookup_results_enable'] != '') ? field['abnlookup_results_enable'] : false;

			// now set the value to the option field
			if (enable_abnlookup_value != false) {
				abnlookup_field.find("input:checkbox").prop('checked', true);
			} else {
				abnlookup_field.find("input:checkbox").prop('checked', false);
			}

			if (abnlookup_field_entity_value != false) {
				abnlookup_field_entity_results.find("input:checkbox").prop('checked', true);
			} else {
				abnlookup_field_entity_results.find("input:checkbox").prop('checked', false);
			}

			if (field["abnlookup_results"] !== undefined) {
				abnlookup_field_entity_results_setting.find("input#" + field["abnlookup_results"]).prop('checked', true);
			}

			abnlookup_field_validate.find("select").val(field["field_validate_abnlookup"] == undefined ? "validabn" : field["field_validate_abnlookup"]);
			abnlookup_field_link.find("select").val(field['field_link_abnlookup'] == undefined ? "" : field['field_link_abnlookup']);

		}
	} else {
		if ('text' == field_type) {

			// the fields
			var abnlookup_field = jQuery(".abnlookup_field_setting");
			var abnlookup_field_validate = jQuery(".abnlookup_validate_field_setting");
			var abnlookup_field_link = jQuery(this).find(".abnlookup_link_field_setting");
			var abnlookup_field_entity_results = jQuery(".abnlookup_entity_results_setting");
			var abnlookup_field_entity_results_setting = jQuery(".abnlookup_entity_results_field_setting_text");

			// lets display the options in the page
			abnlookup_field.show();
			abnlookup_field_entity_results.show();
			if ( 'true'== field.enable_abnlookup ) {
				// we do need to see the ABN Lookup field settings
				abnlookup_field_validate.show();
				// we dont need to see the ABN Lookup results settings
				abnlookup_field_link.hide();
				abnlookup_field_entity_results.hide();
				abnlookup_field_entity_results_setting.hide();
			} else {
				abnlookup_field_validate.hide();
				abnlookup_field_link.hide();
				abnlookup_field_entity_results_setting.hide();
			}
		
			if ( 'true'== field.abnlookup_results_enable ) {
				// we dont need to see the ABN Lookup field settings
				abnlookup_field_validate.hide();
			
				// we do need to see the ABN Lookup results settings
				abnlookup_field_entity_results.show();
				abnlookup_field_entity_results_setting.show();
				abnlookup_field_link.show();
			} else {
				abnlookup_field_link.hide();
				abnlookup_field_entity_results_setting.hide();
			}
		
			// first remove existing list of options
			abnlookup_field_link.find('select option').remove();

			// now to create the list of options and assign to link field
			for (var i = 0; i < form.fields.length; i++) {
				if ('true' == form.fields[i].enable_abnlookup) {
					var value = form.fields[i].label;
					var key = form.fields[i].id;
					abnlookup_field_link.find('select').append('<option value=' + key + '>' + value + '</option>');
				}
			}

			// now get their values
			var enable_abnlookup_value = (typeof field['enable_abnlookup'] != 'undefined' && field['enable_abnlookup'] != '') ? field['enable_abnlookup'] : false;
			var abnlookup_field_entity_value = (typeof field['abnlookup_results_enable'] != 'undefined' && field['abnlookup_results_enable'] != '') ? field['abnlookup_results_enable'] : false;

			// now set the value to the option field
			if (enable_abnlookup_value != false) {
				abnlookup_field.find("input:checkbox").prop('checked', true);
			} else {
				abnlookup_field.find("input:checkbox").prop('checked', false);
			}

			if (abnlookup_field_entity_value != false) {
				abnlookup_field_entity_results.find("input:checkbox").prop('checked', true);
			} else {
				abnlookup_field_entity_results.find("input:checkbox").prop('checked', false);
			}

			if (field["abnlookup_results"] !== undefined) {
				abnlookup_field_entity_results_setting.find("input#" + field["abnlookup_results"]).prop('checked', true);
			}

			abnlookup_field_validate.find("select").val(field["field_validate_abnlookup"] == undefined ? "validabn" : field["field_validate_abnlookup"]);
			abnlookup_field_link.find("select").val(field['field_link_abnlookup'] == undefined ? "" : field['field_link_abnlookup']);

		} else if ('select' == field_type) {
			var abnlookup_field_business_name = jQuery(this).find(".abnlookup_business_name_field_setting");
			var abnlookup_field_link = jQuery(this).find(".abnlookup_link_field_setting");

			// lets display the options in the page
			abnlookup_field_business_name.show();
			if ( 'true'== field.abnlookup_enable_business_name ) {
				abnlookup_field_link.show();
			} else {
				abnlookup_field_link.hide();
			}
			

			// now get their values
			var abnlookup_field_business_name_value = (typeof field['abnlookup_enable_business_name'] != 'undefined' && field['abnlookup_enable_business_name'] != '') ? field['abnlookup_enable_business_name'] : false;

			// LINK FIELD - first delete existing list of options
			abnlookup_field_link.find('select option').remove();

			// now to create the list of options and assign to link field
			for (var i = 0; i < form.fields.length; i++) {
				if ('true' == form.fields[i].enable_abnlookup) {
					var value = form.fields[i].label;
					var key = form.fields[i].id;
					abnlookup_field_link.find('select').append('<option value=' + key + '>' + value + '</option>');
				}
			}

			// now set the value to the option field
			if (abnlookup_field_business_name_value != false) {
				abnlookup_field_business_name.find("input:checkbox").prop('checked', true);
			} else {
				abnlookup_field_business_name.find("input:checkbox").prop('checked', false);
			}

			abnlookup_field_link.find("select").val(field['field_link_abnlookup'] == undefined ? "" : field['field_link_abnlookup']);
		} else if ('radio' == field_type) {
			var abnlookup_field_gst = jQuery(this).find(".abnlookup_gst_field_setting");
			var abnlookup_field_link = jQuery(this).find(".abnlookup_link_field_setting");

			// lets display the options in the page
			abnlookup_field_gst.show();
			if ( 'true'== field.abnlookup_enable_gst ) {
				abnlookup_field_link.show();
			} else {
				abnlookup_field_link.hide();
			}
			

			// now get their values
			var abnlookup_field_gst_value = (typeof field['abnlookup_enable_gst'] != 'undefined' && field['abnlookup_enable_gst'] != '') ? field['abnlookup_enable_gst'] : false;

			// LINK FIELD - first delete existing list of options
			abnlookup_field_link.find('select option').remove();

			// now to create the list of options and assign to link field
			for (var i = 0; i < form.fields.length; i++) {
				if ('true' == form.fields[i].enable_abnlookup) {
					var value = form.fields[i].label;
					var key = form.fields[i].id;
					abnlookup_field_link.find('select').append('<option value=' + key + '>' + value + '</option>');
				}
			}

			// now set the value to the option field
			if (abnlookup_field_gst_value != false) {
				abnlookup_field_gst.find("input:checkbox").prop('checked', true);
			} else {
				abnlookup_field_gst.find("input:checkbox").prop('checked', false);
			}

			abnlookup_field_link.find("select").val(field['field_link_abnlookup'] == undefined ? "" : field['field_link_abnlookup']);
		} else if ('date' == field_type) {

			// the fields
			var abnlookup_field_entity_results = jQuery(".abnlookup_entity_results_setting");
			var abnlookup_field_link = jQuery(".abnlookup_link_field_setting");
			var abnlookup_field_entity_results_setting = jQuery(".abnlookup_entity_results_field_setting_date");
			//var abnlookup_field_validate = jQuery(".abnlookup_validate_field_setting");

			// lets display the options in the page
			abnlookup_field_entity_results.show();
			if ( 'true'== field.abnlookup_results_enable ) {
				abnlookup_field_link.show();
				abnlookup_field_entity_results_setting.show();
			//	abnlookup_field_validate.show();
			} else {
				abnlookup_field_link.hide();
				abnlookup_field_entity_results_setting.hide();
			//	abnlookup_field_validate.hide();
			}
			
			// first remove existing list of options
			abnlookup_field_link.find('select option').remove();

				// now to create the list of options and assign to link field
			for (var i = 0; i < form.fields.length; i++) {
				if ('true' == form.fields[i].enable_abnlookup) {
					var value = form.fields[i].label;
					var key = form.fields[i].id;
					abnlookup_field_link.find('select').append('<option value=' + key + '>' + value + '</option>');
				}
			}

			// now get their values
			var abnlookup_field_entity_value = (typeof field['abnlookup_results_enable'] != 'undefined' && field['abnlookup_results_enable'] != '') ? field['abnlookup_results_enable'] : false;

			if (abnlookup_field_entity_value != false) {
				abnlookup_field_entity_results.find("input:checkbox").prop('checked', true);
			} else {
				abnlookup_field_entity_results.find("input:checkbox").prop('checked', false);
			}

			if (field["abnlookup_results"] !== undefined) {
				abnlookup_field_entity_results_setting.find("input#" + field["abnlookup_results"]).prop('checked', true);
			}

			//abnlookup_field_validate.find("select").val(field["field_validate_abnlookup"] == undefined ? "validabn" : field["field_validate_abnlookup"]);
			abnlookup_field_link.find("select").val(field['field_link_abnlookup'] == undefined ? "" : field['field_link_abnlookup']);
		}
	}
});

jQuery(".abnlookup_field_setting input").click(function() {
	if (jQuery(this).is(":checked")) {
		SetFieldProperty('enable_abnlookup', 'true');
		SetFieldProperty('abnlookup_results_enable', ''); // force opposite value to off
	} else {
		SetFieldProperty('enable_abnlookup', '');
	}
});

jQuery(".abnlookup_entity_results_setting input").click(function() {
	if (jQuery(this).is(":checked")) {
		SetFieldProperty('abnlookup_results_enable', 'true');
		SetFieldProperty('enable_abnlookup', ''); // force opposite value to off
	} else {
		SetFieldProperty('abnlookup_results_enable', '');
	}
});

jQuery(".abnlookup_gst_field_setting input").click(function() {
	if (jQuery(this).is(":checked")) {
		SetFieldProperty('abnlookup_enable_gst', 'true');
	} else {
		SetFieldProperty('abnlookup_enable_gst', '');
	}
});

jQuery(".abnlookup_business_name_field_setting input").click(function() {
	if (jQuery(this).is(":checked")) {
		SetFieldProperty('abnlookup_enable_business_name', 'true');
	} else {
		SetFieldProperty('abnlookup_enable_business_name', '');
	}
});

jQuery('.abnlookup_entity_results_field_setting_text input').click(function() {
	if (jQuery(this).is(":checked")) {
		SetFieldProperty('abnlookup_results', jQuery(this).attr('id'));
	} else {
		SetFieldProperty('enable_results', '');
	}
});

jQuery('.abnlookup_entity_results_field_setting_date input').click(function() {
	if (jQuery(this).is(":checked")) {
		SetFieldProperty('abnlookup_results', jQuery(this).attr('id'));
	} else {
		SetFieldProperty('enable_results', '');
	}
});


function itsg_gf_abnlookup_click_function(self) {

	if (typeof field == 'undefined') {
		return;
	}

	var abnlookup_enable_gst = (typeof field['abnlookup_enable_gst'] != 'undefined' && field['abnlookup_enable_gst'] != '') ? field['abnlookup_enable_gst'] : false;
	var abnlookup_enable_business_name = (typeof field['abnlookup_enable_business_name'] != 'undefined' && field['abnlookup_enable_business_name'] != '') ? field['abnlookup_enable_business_name'] : false;

	if (abnlookup_enable_gst != false) {
		//check the checkbox if previously checked
		jQuery(self).find(".choices_setting:visible").hide();
		jQuery(self).find(".other_choice_setting:visible").hide();

		jQuery(self).find(".ginput_container ul li:nth-child(1) label").text('Yes');
		jQuery(self).find(".ginput_container ul li:nth-child(2) label").text('No');
		jQuery(self).find(".ginput_container ul li:nth-child(n+3)").remove();
	}

	// handles displaying the 'Validate ABN Lookup field' select list
	if (jQuery('input#field_enable_abnlookup:visible').is(":checked")) {
		jQuery('.abnlookup_validate_field_setting').show(); // show validate options
		jQuery('input#abnlookup_entity_results').prop('checked', false); // untick opposite option
	} else {
		jQuery('.abnlookup_validate_field_setting').hide(); // hide validate options
	}

	// handles displaying the 'Entity results' radio list
	if (jQuery('input#abnlookup_entity_results:visible').is(":checked")) {
		jQuery('.abnlookup_entity_results_field_setting_' + field['type']).show(); // show entity result options
		jQuery('.abnlookup_link_field_setting').show(); // show the 'Link ABN Field' setting
		jQuery('input#field_enable_abnlookup').prop('checked', false); // untick opposite option
	} else {
		jQuery('.abnlookup_entity_results_field_setting_' + field['type']).hide(); // hide entity result options
		jQuery('.abnlookup_link_field_setting').hide(); // hide the 'Link ABN Field' setting
	}
	
	jQuery('input#field_enable_abnlookup_business_name:visible').each(function() {
		if (jQuery(this).is(":checked")) {
			jQuery(this).closest("ul").find('li.abnlookup_link_field_setting').show();
		}
	});
	
	// handles how the GST field is displayed if the GST option is enabled
	jQuery('input#field_enable_abnlookup_gst:visible').each(function() {
		if (jQuery(this).is(":checked")) {

			jQuery(this).closest("ul").find('li.abnlookup_link_field_setting').show(); // show the 'Link ABN Field' setting

			// hide the choices section
			jQuery(this).closest("ul").find('.choices_setting').hide();
			jQuery(this).closest("ul").find('.other_choice_setting').hide();

			// set the field options preview as 'yes' and 'no' -- the actual values are set using gform_pre_render
			var override_input_value = '<div class="ginput_container ginput_container_radio"> \
							<ul class="gfield_radio"> \
								<li> \
									<input type="radio" disabled="disabled"> \
									<label>Yes</label> \
								</li> \
								<li> \
									<input type="radio" disabled="disabled"> \
									<label>No</label> \
								</li> \
							</ul> \
							</div>';
			jQuery(this).closest("li.gfield").find('.ginput_container_radio').html(override_input_value);
		} else {
			// hide the 'Link ABN Field' setting
			jQuery(this).parent("li").next().hide();

			// display the choices section
			jQuery(this).closest("ul").find('.choices_setting').show();
			jQuery(this).closest("ul").find('.other_choice_setting').show();

			// update the field option preview to what is contained in the choices setion
			InsertFieldChoice(0);
			DeleteFieldChoice(0);
		}
	});

}

// trigger for when field is opened
jQuery(document).on('click', 'ul.gform_fields li.gfield', function() {
	setTimeout(function() {
		itsg_gf_abnlookup_click_function(jQuery(this));
	}, 500);
});