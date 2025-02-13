<?php

if ( class_exists( "GFForms" ) ) {
	GFForms::include_addon_framework();
	class ITSG_GF_AbnLookup_Settings extends GFAddOn {
		protected $_version = "2.3.0";
		protected $_min_gravityforms_version = "2.5";
		protected $_slug = "itsg_gf_abnlookup_settings";
		protected $path = 'abn-lookup-for-gravity-forms/abn-lookup-for-gravity-forms.php';
		protected $_full_path = __FILE__;
		protected $_title = 'ABN Lookup for Gravity Forms';
		protected $_short_title = 'ABN Lookup';

		public function init(){
			parent::init();
        }

		// add the options
		public function plugin_settings_fields() {
			$abnlookup_options = ITSG_GF_AbnLookup::get_options();
            return array(
                array(
                    "title"  => __('Settings', 'abn-lookup-for-gravity-forms'),
                    "fields" => array(
                        array(
                            "label"   => __('GUID', 'abn-lookup-for-gravity-forms'),
							"name"    => "guid",
					        /* translators: %1$s: opening link tag, %2$s: closing link tag */
                            "tooltip" => sprintf( __('To receive a GUID see %1$sweb services registration%2$s on the Australian Business Register website.', 'abn-lookup-for-gravity-forms'), '<a target="_blank" href="http://abr.business.gov.au/webservices.aspx">', '</a>' ),
                            "type"    => "guid"
                        ),
						array(
                            "label"   => __('Include CSS Styles', 'abn-lookup-for-gravity-forms'),
                            "type"    => "checkbox",
                            "name"    => "includecss",
                            "tooltip" => __('This option allows you to control whether to use the CSS styles provided in the plugin. If this is not enabled you will need to apply styles through your theme.', 'abn-lookup-for-gravity-forms'),
                            "choices" => array(
                                array(
                                    "label" => __('Yes', 'abn-lookup-for-gravity-forms'),
                                    "name"  => "includecss",
									"default_value" => true
                                )
                            )
                        ),
						array(
                            "label"   => __('Lookup timeout (seconds)', 'abn-lookup-for-gravity-forms'),
                            "type"    => "text",
                            "name"    => "lookup_timeout",
                            "tooltip" => __('This option controls the amount of time, in seconds, before a request to the ABR lookup system will timeout.', 'abn-lookup-for-gravity-forms'),
                            "default_value" => $abnlookup_options['lookup_timeout']
                        ),
						array(
                            "label"   => __('Lookup Retries', 'abn-lookup-for-gravity-forms'),
                            "type"    => "text",
                            "name"    => "lookup_retries",
                            "tooltip" => __('This option controls the number of retries when a request to the ABR lookup system has failed. When all retries have been used the field will return the "Error communicating message" error message.', 'abn-lookup-for-gravity-forms'),
                            "default_value" => $abnlookup_options['lookup_retries']
                        )
                    )
                ), array(
                    "title"  => __('Validation Messages', 'abn-lookup-for-gravity-forms'),
                    "fields" => array(
                        array(
                            "label"   => __('ABN Not Valid', 'abn-lookup-for-gravity-forms'),
							"name"    => "validation_message_not_valid",
                            "tooltip" => __('This message is displayed to the user if they enter an ABN that is not valid.', 'abn-lookup-for-gravity-forms'),
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_not_valid']
                        ),
						array(
                            "label"   => __('ABN Not Active', 'abn-lookup-for-gravity-forms'),
							"name"    => "validation_message_activeabn",
                            "tooltip" => __('This message is displayed to the user if they enter an ABN that is not active.', 'abn-lookup-for-gravity-forms'),
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_activeabn']
                        ),
						array(
                            "label"   => __('ABN Not Registered for GST', 'abn-lookup-for-gravity-forms'),
							"name"    => "validation_message_reggst",
                            "tooltip" => __('This message is displayed to the user if they enter a ABN that is not registered for GST and the field validation is set to only allow ABNs that are GST registered.', 'abn-lookup-for-gravity-forms'),
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_reggst']
                        ),
						array(
                            "label"   => __('ABN Registered for GST', 'abn-lookup-for-gravity-forms'),
							"name"    => "validation_message_notreggst",
                            "tooltip" => __('This message is displayed to the user if they enter a ABN is registered for GST and the field validation is set to only allow ABNs that are not registered for GST.', 'abn-lookup-for-gravity-forms'),
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_notreggst']
                        ),
						array(
                            "label"   => __('ABN Not Correct Length', 'abn-lookup-for-gravity-forms'),
							"name"    => "validation_message_11_char",
                            "tooltip" => __('This message is displayed to the user if they enter a value into the ABN field that does not contain the required 11 characters that make up an ABN.', 'abn-lookup-for-gravity-forms'),
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_11_char']
                        ),
						array(
                            "label"   => __('Loading Message', 'abn-lookup-for-gravity-forms'),
							"name"    => "validation_message_loading",
                            "tooltip" => __('This message is displayed to the user when the ABN Lookup is running.', 'abn-lookup-for-gravity-forms'),
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_loading']
                        ),
						array(
                            "label"   => __('Error Communicating Message', 'abn-lookup-for-gravity-forms'),
							"name"    => "validation_message_error_communicating",
                            "tooltip" => __('This message is displayed to the user when the ABN Lookup script has failed to communicate with the Australian Business Register more than three times.', 'abn-lookup-for-gravity-forms'),
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_error_communicating']
                        )
                    )
                )
            );
        }

		public function settings_guid(){
                $this->settings_text(
                    array(
                         "name"    => "guid",
						 "class"   => "large"
                    )
                );
				printf(
					'<div><p>%s</p></div>',
					    /* translators: %1$s: opening link tag, %2$s: closing link tag */
						sprintf( esc_html__('To receive a GUID see %1$sweb services registration%2$s on the Australian Business Register website.', 'abn-lookup-for-gravity-forms'), '<a target="_blank" href="http://abr.business.gov.au/webservices.aspx">', '</a>' )
				);
        }

		public function styles() {
			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';
			$version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? wp_rand() : $this->_version;

			$styles = array(
				array(
					'handle'  => 'abnlookup-style',
					'src'     => $this->get_base_url() . "/css/abnlookup-style{$min}.css",
					'version'   => $version,
					'media'   => 'screen',
					'enqueue' => array( array( $this, 'requires_styles' ) ),
				),
			);

			return array_merge( parent::styles(), $styles );
		}

		public function requires_styles( $form, $is_ajax ) {
			$abnlookup_options = ITSG_GF_AbnLookup::get_options();

			if ( ! $this->is_form_editor() && is_array( $form ) ) {
				foreach ( $form['fields'] as $field ) {
					if ( ITSG_GF_AbnLookup_Fields::is_abnlookup_field( $field ) ) {
						if ( true == $abnlookup_options['includecss'] ) {
							return true;
						}
					}
				}
			}

			return false;
		}

		public function scripts() {
			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';
			$version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? wp_rand() : $this->_version;

			$scripts = array(
				array(
					'handle'    => 'abnlookup-script',
					'src'       => $this->get_base_url() . "/js/abnlookup-script{$min}.js",
					'version'   => $version,
					'deps'      => array( 'jquery' ),
					'enqueue'   => array( array( $this, 'requires_scripts' ) ),
					'in_footer' => true,
					'callback'  => array( $this, 'localize_scripts' ),
				),
				array(
					'handle'    => 'abnlookup-script-admin',
					'src'       => $this->get_base_url() . "/js/abnlookup-script-admin{$min}.js",
					'version'   => $version,
					'deps'      => array( 'jquery' ),
					'enqueue'   => array( array( $this, 'requires_admin_js' ) ),
					'in_footer' => true,
					'callback'  => array( $this, 'localize_scripts_admin' ),
				)
			);

			return array_merge( parent::scripts(), $scripts );
		}

		function requires_admin_js() {
			return GFCommon::is_form_editor();
		}

		public function localize_scripts( $form, $is_ajax ) {
			// Localize the script with new data
			$text_yes = __('Yes', 'abn-lookup-for-gravity-forms');
			$text_no = __('No', 'abn-lookup-for-gravity-forms');
			$gst_value_yes = apply_filters( 'itsg_gf_abnlookup_gst_value_yes', $text_yes, $form['id'] );
			$gst_value_no = apply_filters( 'itsg_gf_abnlookup_gst_value_no', $text_no, $form['id'] );

			$abnlookup_options = ITSG_GF_AbnLookup::get_options();

			$abnlookup_fields = array();
			if ( is_array( $form['fields'] ) ) {
				foreach ( $form['fields'] as $field ) {
					$is_abnlookup_field = ITSG_GF_AbnLookup_Fields::is_abnlookup_field( $field );
					if ( 'abn' == $is_abnlookup_field ) {
						$field_id = $field['id'];
						$field_validate_abnlookup = $field->field_validate_abnlookup;
						$abnlookup_fields[ $field_id ]['validate'] = $field_validate_abnlookup;
					}
				}
			}

			$settings_array = array(
				'form_id' => $form['id'],
				'abnlookup_fields' => $abnlookup_fields,
				'ajax_url' => admin_url( 'admin-ajax.php' ),
				'validation_message_loading' => strip_tags( $abnlookup_options['validation_message_loading'], '<strong><a><u><i>' ),
				'validation_message_not_valid' => strip_tags( $abnlookup_options['validation_message_not_valid'], '<strong><a><u><i>' ),
				'validation_message_error_communicating' => strip_tags( $abnlookup_options['validation_message_error_communicating'], '<strong><a><u><i>' ),
				'validation_message_11_char' => strip_tags($abnlookup_options['validation_message_11_char'], '<strong><a><u><i>' ),
				'text_checking' => esc_js( __('Checking', 'abn-lookup-for-gravity-forms') ),
				'text_check_abn' => esc_js( __('Check ABN', 'abn-lookup-for-gravity-forms') ),
				'gst_value_yes' => esc_js( $gst_value_yes ),
				'gst_value_no' => esc_js( $gst_value_no ),
				'lookup_timeout' => esc_js( ( int ) abs( $abnlookup_options['lookup_timeout'] * 1000 ) ),
				'lookup_retries' => esc_js( ( int ) abs( $abnlookup_options['lookup_retries'] ) ),
				'is_ajax' => $is_ajax,
				'debug' => defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? true : false,
				'nonce' => wp_create_nonce('abnlookup')
			);

			wp_localize_script( 'abnlookup-script', 'gf_abnlookup_settings', $settings_array );

		}

		public function requires_scripts( $form, $is_ajax ) {
			if ( ! $this->is_form_editor() && is_array( $form ) ) {
				foreach ( $form['fields'] as $field ) {
					$field_type = $field->type;
					if ( 'text' == $field_type && true == $field['enable_abnlookup'] ) {
						return true;
					} elseif ( 'text' == $field_type && '' !== $field['abnlookup_results_enable'] && '' !== $field['abnlookup_results'] ) {
						return true;
					} elseif ( 'radio' == $field_type && '' !== $field['abnlookup_enable_gst'] ) {
						return true;
					}
				}
			}
			return false;
		}
		
    }
    new ITSG_GF_AbnLookup_Settings();
}