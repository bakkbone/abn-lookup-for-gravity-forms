<?php
/*
 *   Setup the settings page for configuring the options
 */
if ( class_exists( "GFForms" ) ) {
	GFForms::include_addon_framework();
	class ITSG_GF_AbnLookup_Settings extends GFAddOn {
		protected $_version = "2.0.0";
		protected $_min_gravityforms_version = "2.5";
		protected $_slug = "itsg_gf_abnlookup_settings";
		protected $path = 'abn-lookup-for-gravity-forms/abn-lookup-for-gravity-forms.php';
		protected $_full_path = __FILE__;
		protected $_title = ABR_PLUGIN_TITLE;
		protected $_short_title = ABR_LOOKUP;

		public function init(){
			parent::init();
        }

		// add the options
		public function plugin_settings_fields() {
			$abnlookup_options = ITSG_GF_AbnLookup::get_options();
            return array(
                array(
                    "title"  => ABR_SETTINGS,
                    "fields" => array(
                        array(
                            "label"   => ABR_GUID,
							"name"    => "guid",
                            "tooltip" => sprintf( ABR_GET_GUID, '<a target="_blank" href="http://abr.business.gov.au/webservices.aspx">', '</a>' ),
                            "type"    => "guid"
                        ),
						array(
                            "label"   => ABR_INC_CSS,
                            "type"    => "checkbox",
                            "name"    => "includecss",
                            "tooltip" => ABR_INC_CSS_TOOLTIP,
                            "choices" => array(
                                array(
                                    "label" => ABR_YES,
                                    "name"  => "includecss",
									"default_value" => true
                                )
                            )
                        ),
						array(
                            "label"   => ABR_TIMEOUT,
                            "type"    => "text",
                            "name"    => "lookup_timeout",
                            "tooltip" => ABR_TIMEOUT_TOOLTIP,
                            "default_value" => $abnlookup_options['lookup_timeout']
                        ),
						array(
                            "label"   => ABR_RETRIES,
                            "type"    => "text",
                            "name"    => "lookup_retries",
                            "tooltip" => ABR_RETRIES_TOOLTIP,
                            "default_value" => $abnlookup_options['lookup_retries']
                        )
                    )
                ), array(
                    "title"  => ABR_VAL_MSG,
                    "fields" => array(
                        array(
                            "label"   => ABR_VAL_NOT_VALID,
							"name"    => "validation_message_not_valid",
                            "tooltip" => ABR_VAL_NOT_VALID_TOOLTIP,
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_not_valid']
                        ),
						array(
                            "label"   => ABR_VAL_NOT_ACTIVE,
							"name"    => "validation_message_activeabn",
                            "tooltip" => ABR_VAL_NOT_ACTIVE_TOOLTIP,
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_activeabn']
                        ),
						array(
                            "label"   => ABR_VAL_NOTREG,
							"name"    => "validation_message_reggst",
                            "tooltip" => ABR_VAL_NOTREG_TOOLTIP,
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_reggst']
                        ),
						array(
                            "label"   => ABR_VAL_REG,
							"name"    => "validation_message_notreggst",
                            "tooltip" => ABR_VAL_REG_TOOLTIP,
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_notreggst']
                        ),
						array(
                            "label"   => ABR_VAL_LENGTH,
							"name"    => "validation_message_11_char",
                            "tooltip" => ABR_VAL_LENGTH_TOOLTIP,
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_11_char']
                        ),
						array(
                            "label"   => ABR_VAL_LOADING,
							"name"    => "validation_message_loading",
                            "tooltip" => ABR_VAL_LOADING_TOOLTIP,
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_loading']
                        ),
						array(
                            "label"   => ABR_VAL_ERROR,
							"name"    => "validation_message_error_communicating",
                            "tooltip" => ABR_VAL_ERROR,
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
						sprintf( ABR_GET_GUID, '<a target="_blank" href="http://abr.business.gov.au/webservices.aspx">', '</a>' )
				);
        }

		public function styles() {
			$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';
			$version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? mt_rand() : $this->_version;

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
			$version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? mt_rand() : $this->_version;

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
			$text_yes = ABR_YES;
			$text_no = ABR_NO;
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
				'text_checking' => esc_js( ABR_CHECKING ),
				'text_check_abn' => esc_js( ABR_CHECK_ABN ),
				'gst_value_yes' => esc_js( $gst_value_yes ),
				'gst_value_no' => esc_js( $gst_value_no ),
				'lookup_timeout' => esc_js( ( int ) abs( $abnlookup_options['lookup_timeout'] * 1000 ) ),
				'lookup_retries' => esc_js( ( int ) abs( $abnlookup_options['lookup_retries'] ) ),
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