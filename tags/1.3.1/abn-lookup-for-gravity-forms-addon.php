<?php
/* 
 *   Setup the settings page for configuring the options
 */
if ( class_exists( "GFForms" ) ) {
	GFForms::include_addon_framework();
	class ITSG_GF_AbnLookup_Settings extends GFAddOn {
		protected $_version = "1.0";
		protected $_min_gravityforms_version = "1.7.9999";
		protected $_slug = "itsg_gf_abnlookup_settings";
		protected $_full_path = __FILE__;
		protected $_title = "ABN Lookup for Gravity Forms";
		protected $_short_title = "ABN Lookup";
		
		public function init(){
			parent::init();
			add_filter( "gform_submit_button", array( $this, "form_submit_button" ), 10, 2);
        } // END init
		
		// Add the text in the plugin settings to the bottom of the form if enabled for this form
		function form_submit_button( $button, $form ){
			$settings = $this->get_form_settings( $form );
			if( isset( $settings["enabled"] ) && true == $settings["enabled"] ){
				$text = $this->get_plugin_setting( "mytextbox" );
				$button = "<div>{$text}</div>" . $button;
			}
			return $button;
		} // END form_submit_button

		// add the options
		public function plugin_settings_fields() {
			$abnlookup_options = ITSG_GF_AbnLookup::get_options();
            return array(
                array(
                    "title"  => __( 'Settings', 'abn-lookup-for-gravity-forms' ),
                    "fields" => array(
                        array(
                            "label"   => __( 'GUID', 'abn-lookup-for-gravity-forms' ),
							"name"    => "guid",
                            "tooltip" => sprintf( __( 'To receive a GUID see %sweb services registration%s on the Australian Business Register website.', 'abn-lookup-for-gravity-forms' ), '<a target="_blank" href="http://abr.business.gov.au/webservices.aspx">', '</a>' ),
                            "type"    => "guid"
                        ),
						array(
                            "label"   => __( 'Include CSS styles', 'abn-lookup-for-gravity-forms' ),
                            "type"    => "checkbox",
                            "name"    => "includecss",
                            "tooltip" => __( 'This option allows you to control whether to use the CSS styles provided in the plugin. If this is not enabled you will need to apply styles through your theme.', 'abn-lookup-for-gravity-forms' ),
                            "choices" => array(
                                array(
                                    "label" => "Yes",
                                    "name"  => "includecss",
									"default_value" => true
                                )
                            )
                        )
                    )
                ), array(
                    "title"  => __( 'Validation messages', 'abn-lookup-for-gravity-forms' ),
                    "fields" => array(
                        array(
                            "label"   => __( 'ABN not valid', 'abn-lookup-for-gravity-forms' ),
							"name"    => "validation_message_not_valid",
                            "tooltip" => __( 'This message is displayed to the user if they enter a ABN that is not valid.', 'abn-lookup-for-gravity-forms' ),
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_not_valid']
                        ),
						array(
                            "label"   => __( 'ABN not active', 'abn-lookup-for-gravity-forms' ),
							"name"    => "validation_message_activeabn",
                            "tooltip" => __( 'This message is displayed to the user if they enter a ABN is not active.', 'abn-lookup-for-gravity-forms' ),
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_activeabn']
                        ),
						array(
                            "label"   => __( 'ABN not registered for GST', 'abn-lookup-for-gravity-forms' ),
							"name"    => "validation_message_reggst",
                            "tooltip" => __( 'This message is displayed to the user if they enter a ABN that is not registered for GST and the field validation is set to only allow ABNs that are GST registered.', 'abn-lookup-for-gravity-forms' ),
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_reggst']
                        ),
						array(
                            "label"   => __( 'ABN registered for GST', 'abn-lookup-for-gravity-forms' ),
							"name"    => "validation_message_notreggst",
                            "tooltip" => __( 'This message is displayed to the user if they enter a ABN is registered for GST and the field validation is set to only allow ABNs that are not registered for GST.', 'abn-lookup-for-gravity-forms' ),
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_notreggst']
                        ),
						array(
                            "label"   => __( 'ABN not correct length', 'abn-lookup-for-gravity-forms' ),
							"name"    => "validation_message_11_char",
                            "tooltip" => __( 'This message is displayed to the user if they enter a value into the ABN field that does not contain the required 11 characters that make up an ABN.', 'abn-lookup-for-gravity-forms' ),
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_11_char']
                        ),
						array(
                            "label"   => __( 'Loading message', 'abn-lookup-for-gravity-forms' ),
							"name"    => "validation_message_loading",
                            "tooltip" => __( 'This message is displayed to the user when the ABN Lookup is running.', 'abn-lookup-for-gravity-forms' ),
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_loading']
                        ),
						array(
                            "label"   => __( 'Error comminicating message', 'abn-lookup-for-gravity-forms' ),
							"name"    => "validation_message_error_communicating",
                            "tooltip" => __( 'This message is displayed to the user when the ABN Lookup script has failed to communicate with the Australian Business Register more than three times.', 'abn-lookup-for-gravity-forms' ),
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_error_communicating']
                        )
                    )
                )
            );
        } // END plugin_settings_fields
		
		public function settings_guid(){
                $this->settings_text(
                    array(
                         "name"    => "guid",
						 "class"   => "large"
                    )
                );
				printf(
					'<div><p>%s</p></div>',
						sprintf( __( 'To receive a GUID see %sweb services registration%s on the Australian Business Register website.', 'abn-lookup-for-gravity-forms' ), '<a target="_blank" href="http://abr.business.gov.au/webservices.aspx">', '</a>' )
				);
        } // END settings_guid
		
	
    }
    new ITSG_GF_AbnLookup_Settings();
}