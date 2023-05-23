<?php

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/*
 *  Contains all the functions responsible for ABN Lookup fields
 */

 if ( !class_exists( 'ITSG_GF_AbnLookup_Fields' ) ) {
    class ITSG_GF_AbnLookup_Fields {

		protected static $form = '';
		protected static $form_id = '';

		public function __construct() {

			add_action( 'gform_field_standard_settings', array( $this, 'abnlookup_field_settings' ), 10, 2 );
			add_action( 'gform_field_css_class', array( $this, 'abnlookup_css_class' ), 10, 3 );
			add_filter( 'gform_tooltips', array( $this, 'field_tooltips' ) );
			add_filter( 'gform_field_content', array( $this, 'change_abnlookup_fields' ), 10, 5 );
			add_filter( 'gform_pre_render', array( $this, 'customise_abnlookup_fields' ) );
			add_filter( 'gform_admin_pre_render', array( $this, 'customise_abnlookup_fields' ) );
			add_filter( 'gform_pre_validation', array( $this, 'check_field_values' ) );
			add_filter( 'gform_validation', array( $this, 'validate_abnlookup_fields' ) );
			add_action( 'gform_enqueue_scripts', array( $this, 'enqueue_scripts' ), 90, 2 );
			add_filter( 'gform_field_settings_tabs', array( $this, 'add_abnlookup_formeditor_tab' ), 10, 2 );
			add_filter( 'gform_field_settings_tab_content_abr', array( $this, 'add_abnlookup_formeditor_tab_content' ), 10, 2 );
		}

	/**
	 * BEGIN: patch to allow JS and CSS to load when loading forms through wp-ajax requests
	 *
	 */

		public function enqueue_scripts( $form, $is_ajax ) {
			if ( $this->requires_scripts( $form, $is_ajax ) ) {
				$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

				wp_register_script( 'abnlookup-script', plugins_url( "/js/abnlookup-script{$min}.js", __FILE__ ),  array( 'jquery' ) );

				$this->localize_scripts( $form, $is_ajax );

			}

			if ( $this->requires_styles( $form, $is_ajax ) ) {
				$min = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG || isset( $_GET['gform_debug'] ) ? '' : '.min';

				wp_enqueue_style( 'abnlookup-style',  plugins_url( "/css/abnlookup-style{$min}.css", __FILE__ ) );
			}
		}

		public function requires_scripts( $form, $is_ajax ) {
			if ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX && ! GFCommon::is_form_editor() && is_array( $form ) ) {
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

		public function requires_styles( $form, $is_ajax ) {
			$abnlookup_options = ITSG_GF_AbnLookup::get_options();
			if ( is_admin() && defined( 'DOING_AJAX' ) && DOING_AJAX && ! GFCommon::is_form_editor() && is_array( $form ) ) {
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

		function localize_scripts( $form, $is_ajax ) {
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
			);

			wp_localize_script( 'abnlookup-script', 'gf_abnlookup_settings', $settings_array );

			// Enqueued script with localized data.
			wp_enqueue_script( 'abnlookup-script' );

		}

	/**
	 * END: patch to allow JS and CSS to load when loading forms through wp-ajax requests
	 *
	 */

		/*
		 * This is where server side checks of the fields are performed.
		 * - checks for any ABN Lookup linked fields and sets their values
		 * - ensures 'fake' values are not passed by users on the client side
		 */
		function check_field_values( $form ) {
			if ( is_array( $form ) || is_object( $form ) ) {
				// first we need to get the ABN number for the applicable GST field and get the ABN results
				foreach( $form['fields'] as &$field )  {
					if ( 'abn' == self::is_abnlookup_field( $field ) ) {
						$value = rgpost( "input_{$field['id']}" );
						$is_hidden = RGFormsModel::is_field_hidden( $form, $field, array() );
						$numbersOnly = preg_replace( "/[^0-9]/","",$value );
						$abn_details = ITSG_GF_AbnLookup::do_abnlookup( $numbersOnly );
						$field_values[$field['id']] = $abn_details;
						$field_hidden[$field['id']] = $is_hidden;
					}
				}
				// now we check for linked fields and set their post value
				foreach( $form['fields'] as &$field )  {
					$value = rgpost( "input_{$field['id']}" );
					if ( 'abnlookup_entity_gst' == self::is_abnlookup_field( $field ) ) {
						$keys = array_keys( $field_values );
						foreach( $keys as $key ) {
							if ( $key == $field['field_link_abnlookup'] ) {
								$abn_details = $field_values[ $key ];
								if ( $field_hidden[$key] ) {
									$_POST["input_{$field['id']}"] = '';
								} elseif ( isset( $abn_details->businessEntity ) ) {
									$registered_gst = isset( $abn_details->businessEntity->goodsAndServicesTax ) ? ( '0001-01-01' == $abn_details->businessEntity->goodsAndServicesTax->effectiveTo ) : false;
									$text_yes = __( 'Yes', 'abn-lookup-for-gravity-forms' );
									$text_no = __( 'No', 'abn-lookup-for-gravity-forms' );
									$value_yes = sanitize_text_field( apply_filters( 'itsg_gf_abnlookup_gst_value_yes', $text_yes, $form['id'] ) );
									$value_no = sanitize_text_field( apply_filters( 'itsg_gf_abnlookup_gst_value_no', $text_no, $form['id'] ) );
									if ( $registered_gst ) {
										$_POST["input_{$field['id']}"] = $value_yes;
									} else {
										$_POST["input_{$field['id']}"] = $value_no;
									}
								} else {
									$_POST["input_{$field['id']}"] = '';
								}
							}
						}
					} elseif ( 'abnlookup_entity_type' == self::is_abnlookup_field( $field ) ) {
						$keys = array_keys( $field_values );
						foreach( $keys as $key ) {
							if ( $key == $field['field_link_abnlookup'] ) {
								$abn_details = $field_values[$key];
								if ( $field_hidden[$key] ) {
									$_POST["input_{$field['id']}"] = '';
								} elseif ( isset($abn_details->businessEntity ) ) {
									$entityType = isset( $abn_details->businessEntity->entityType->entityDescription ) ? $abn_details->businessEntity->entityType->entityDescription : '';
									$_POST["input_{$field['id']}"] = $entityType;
								}
							}
						}
					} elseif ( 'abnlookup_entity_name' == self::is_abnlookup_field( $field ) ) {
						$keys = array_keys( $field_values );
						foreach( $keys as $key ) {
							if ( $key == $field['field_link_abnlookup'] ) {
								$abn_details = $field_values[$key];
								if ( $field_hidden[$key] ) {
									$_POST["input_{$field['id']}"] = '';
								} elseif ( isset( $abn_details->businessEntity ) ) {
									$entityTypeCode = $abn_details->businessEntity->entityType->entityTypeCode;
									if ( 'IND' == $entityTypeCode ) {
										$familyName = is_string( $abn_details->businessEntity->legalName->familyName ) ? $abn_details->businessEntity->legalName->familyName : '';
										$givenName = is_string( $abn_details->businessEntity->legalName->givenName ) ? $abn_details->businessEntity->legalName->givenName : '';
										$otherGivenName = is_string( $abn_details->businessEntity->legalName->otherGivenName ) ? $abn_details->businessEntity->legalName->otherGivenName : '';
										$entityName = $familyName . ", " . $givenName . " " .  $otherGivenName;
									} else {
										$entityName = $abn_details->businessEntity->mainName->organisationName;
									}
									$_POST["input_{$field['id']}"] = $entityName;
								}
							}
						}
					} elseif ( 'abnlookup_entity_status' == self::is_abnlookup_field( $field ) ) {
						$keys = array_keys( $field_values );
						foreach( $keys as $key ) {
							if ( $key == $field['field_link_abnlookup'] ) {
								$abn_details = $field_values[$key];
								if ( $field_hidden[$key] ) {
									$_POST["input_{$field['id']}"] = '';
								} elseif ( isset($abn_details->businessEntity ) ) {
									$entityStatus = isset($abn_details->businessEntity->entityStatus->entityStatusCode) ? $abn_details->businessEntity->entityStatus->entityStatusCode : '';
									$_POST["input_{$field['id']}"] = $entityStatus;
								}
							}
						}
					} elseif ( 'abnlookup_entity_postcode' == self::is_abnlookup_field( $field ) ) {
						$keys = array_keys( $field_values );
						foreach( $keys as $key ) {
							if ( $key == $field['field_link_abnlookup'] ) {
								$abn_details = $field_values[$key];
								if ( $field_hidden[$key] ) {
									$_POST["input_{$field['id']}"] = '';
								} elseif ( isset($abn_details->businessEntity ) ) {
									$entityPostcode = isset( $abn_details->businessEntity->mainBusinessPhysicalAddress->postcode ) ? $abn_details->businessEntity->mainBusinessPhysicalAddress->postcode : '';
									$_POST["input_{$field['id']}"] = $entityPostcode;
								}
							}
						}
					} elseif ( 'abnlookup_entity_state' == self::is_abnlookup_field( $field ) ) {
						$keys = array_keys( $field_values );
						foreach( $keys as $key ) {
							if ( $key == $field['field_link_abnlookup'] ) {
								$abn_details = $field_values[$key];
								if ( $field_hidden[$key] ) {
									$_POST["input_{$field['id']}"] = '';
								} elseif ( isset($abn_details->businessEntity ) ) {
									$entityStatecode = isset( $abn_details->businessEntity->mainBusinessPhysicalAddress->stateCode ) ? $abn_details->businessEntity->mainBusinessPhysicalAddress->stateCode : '';
									$_POST["input_{$field['id']}"] = $entityStatecode;
								}
							}
						}
					} elseif ( 'abnlookup_gst_effective_from' == self::is_abnlookup_field( $field ) ) {
						$keys = array_keys( $field_values );
						foreach( $keys as $key ) {
							if ( $key == $field['field_link_abnlookup'] ) {
								$abn_details = $field_values[$key];
								if ( $field_hidden[$key] ) {
									$_POST["input_{$field['id']}"] = '';
								} elseif ( isset($abn_details->businessEntity ) ) {
									$entityGSTEffectiveFrom = isset( $abn_details->businessEntity->goodsAndServicesTax->effectiveFrom ) ? $abn_details->businessEntity->goodsAndServicesTax->effectiveFrom : '';
									if ( '' !== $entityGSTEffectiveFrom && '0001-01-01' !== $entityGSTEffectiveFrom ) {
										// format the date
										$field_date_format = $field->dateFormat;
										switch ( $field_date_format ) {
											case 'dmy':
												$date_format = 'd/m/Y';
												break;
											case 'dmy_dash':
												$date_format = 'd-m-Y';
												break;
											case 'dmy_dot':
												$date_format = 'd.m.Y';
												break;
											case 'ymd_slash':
												$date_format = 'Y/m/d';
												break;
											case 'ymd_dash':
												$date_format = 'Y-m-d';
												break;
											case 'ymd_dot':
												$date_format = 'Y.m.d';
												break;
											default:
												$date_format = 'm/d/Y';
										}
										$formatted_date = date( $date_format, strtotime( $entityGSTEffectiveFrom ) );
										$_POST["input_{$field['id']}"] = $formatted_date;
									}
								}
							}
						}
					} elseif ( 'abnlookup_entity_effective_from' == self::is_abnlookup_field( $field ) ) {
						$keys = array_keys( $field_values );
						foreach( $keys as $key ) {
							if ( $key == $field['field_link_abnlookup'] ) {
								$abn_details = $field_values[$key];
								if ( $field_hidden[$key] ) {
									$_POST["input_{$field['id']}"] = '';
								} elseif ( isset($abn_details->businessEntity ) ) {
									$entityEffectiveFrom = isset( $abn_details->businessEntity->entityStatus->effectiveFrom ) ? $abn_details->businessEntity->entityStatus->effectiveFrom : '';
									if ( '' !== $entityEffectiveFrom && '0001-01-01' !== $entityEffectiveFrom ) {
										// format the date
										$field_date_format = $field->dateFormat;
										switch ( $field_date_format ) {
											case 'dmy':
												$date_format = 'd/m/Y';
												break;
											case 'dmy_dash':
												$date_format = 'd-m-Y';
												break;
											case 'dmy_dot':
												$date_format = 'd.m.Y';
												break;
											case 'ymd_slash':
												$date_format = 'Y/m/d';
												break;
											case 'ymd_dash':
												$date_format = 'Y-m-d';
												break;
											case 'ymd_dot':
												$date_format = 'Y.m.d';
												break;
											default:
												$date_format = 'm/d/Y';
										}
									}
									$formatted_date = date( $date_format, strtotime( $entityEffectiveFrom ) );
									$_POST["input_{$field['id']}"] = $formatted_date;
								}
							}
						}
					}
				}
			}
			return $form;
		}

		/*
		 * Handles custom validation for ABN Lookup and linked fields
		 */
		function validate_abnlookup_fields( $validation_result ) {
			$abnlookup_options = ITSG_GF_AbnLookup::get_options();
			$form = $validation_result['form'];
			$current_page = rgpost( 'gform_source_page_number_' . $form['id'] ) ? rgpost( 'gform_source_page_number_' . $form['id'] ) : 1;
			if ( is_array( $form ) ) {
				foreach( $form['fields'] as &$field )  {
					$field_page = $field->pageNumber;
					$is_hidden = RGFormsModel::is_field_hidden( $form, $field, array() );
					if ( $field_page != $current_page || $is_hidden ) {
						continue;
					}
					if ( 'abn' == self::is_abnlookup_field( $field ) && 'none' !== $field->field_validate_abnlookup ) {
						$value = rgpost( "input_{$field['id']}" );
						$numbersOnly = preg_replace( "/[^0-9]/", "", $value );
						$abn_details = ITSG_GF_AbnLookup::do_abnlookup( $numbersOnly );
						$registered_gst = isset( $abn_details->businessEntity->goodsAndServicesTax ) ? ( '0001-01-01' == $abn_details->businessEntity->goodsAndServicesTax->effectiveTo ) : false;
						$entityStatus = isset( $abn_details->businessEntity->entityStatus->entityStatusCode ) ? $abn_details->businessEntity->entityStatus->entityStatusCode : false;
						if ( '' == $value && $field['isRequired'] ) {
							$validation_result['is_valid'] = false; // set the form validation to false
							$field->failed_validation = true;
					} elseif ( ! empty( $value ) && ( ! $abn_details || isset( $abn_details->exception ) ) ) {
							$validation_result['is_valid'] = false; // set the form validation to false
							$field->failed_validation = true;
							if ( 11 == strlen( $numbersOnly ) ) {
								$field->validation_message = $abnlookup_options['validation_message_not_valid'];
							} else {
								$field->validation_message = $abnlookup_options['validation_message_11_char'];
							}
						} elseif ( 'activeabn' == $field['field_validate_abnlookup'] && 'Active' !== $entityStatus ) {
							$validation_result['is_valid'] = false; // set the form validation to false
							$field->failed_validation = true;
							$field->validation_message = $abnlookup_options['validation_message_activeabn'];
						} elseif ( 'reggst' == $field['field_validate_abnlookup'] && !$registered_gst ) {
							$validation_result['is_valid'] = false; // set the form validation to false
							$field->failed_validation = true;
							$field->validation_message = $abnlookup_options['validation_message_reggst'];
						} elseif ( 'notreggst' == $field['field_validate_abnlookup'] && $registered_gst ) {
							$validation_result['is_valid'] = false; // set the form validation to false
							$field->failed_validation = true;
							$field->validation_message = $abnlookup_options['validation_message_notreggst'];
						}
					}
				}
			}
			//Assign modified $form object back to the validation result
			$validation_result['form'] = $form;
			return $validation_result;
		}

		/*
		 * Customise ABN lookup fields
		 * - forces 'GST' field to be 'Yes' and 'No' options
		 */
		function customise_abnlookup_fields( $form ) {
			if ( is_array( $form ) || is_object( $form ) ) {
				foreach( $form['fields'] as &$field )  {
					if ( 'abnlookup_entity_gst' == self::is_abnlookup_field( $field ) ) {
						// Force GST field 'Yes' and 'No' options
						$text_yes = ABR_YES;
						$text_no = ABR_NO;
						$value_yes = apply_filters( 'itsg_gf_abnlookup_gst_value_yes', $text_yes, $form['id'] );
						$value_no = apply_filters( 'itsg_gf_abnlookup_gst_value_no', $text_no, $form['id'] );
						$field->choices =  array (
							array( 'text' => $text_yes, 'value' => $value_yes ),
							array( 'text' => $text_no, 'value' => $value_no )
						);
					} elseif ( 'abnlookup_gst_effective_from' == self::is_abnlookup_field( $field ) ) {
						// ensure is datepicker
						$field->dateType = 'datepicker';
					} elseif ( 'abnlookup_entity_effective_from' == self::is_abnlookup_field( $field ) ) {
						// ensure is datepicker
						$field->dateType = 'datepicker';
					}
				}
			}
			return $form;
		}

		/*
		 * Customise ABN lookup fields
		 * - in the form editor, display GST field as 'Yes' and 'No' options
		 * - in front end forms add the response HTML below ABN Lookup fields
		 */
		function change_abnlookup_fields( $content, $field, $value, $lead_id, $form_id ) {
			if ( 'gf_entries' != rgget( 'page' ) ) {
				if ( GFCommon::is_form_editor() ) {
					if ('abnlookup_entity_gst' == self::is_abnlookup_field( $field ) ) {
						$override_input_value = '<div class="ginput_container ginput_container_radio">
							<ul class="gfield_radio">
								<li>
									<input type="radio" disabled="disabled">
									<label>' . ABR_YES . '</label>
								</li>
								<li>
									<input type="radio" disabled="disabled">
									<label>' . ABR_NO . '</label>
								</li>
							</ul>
							</div>';
						$content = preg_replace( "~<div class='ginput_container ginput_container_radio'>.*<\/div>~", $override_input_value, $content );
					}
					return $content;
				} elseif ( 'abn' == self::is_abnlookup_field( $field ) ) {
					$entityStatus = '';
					$abn_details_message = '';
					$numbersOnly = preg_replace( "/[^0-9]/","", $value );
					if ( 11 == strlen( $numbersOnly ) ) {
						$abn_details = ITSG_GF_AbnLookup::do_abnlookup( $numbersOnly );
						if ( isset( $abn_details->businessEntity ) ) {
							$entityTypeCode = $abn_details->businessEntity->entityType->entityTypeCode;
							$entityStatus =  $abn_details->businessEntity->entityStatus->entityStatusCode;
							if ( 'IND' == $entityTypeCode ) {
								$familyName = is_string( $abn_details->businessEntity->legalName->familyName ) ? $abn_details->businessEntity->legalName->familyName : '';
								$givenName = is_string( $abn_details->businessEntity->legalName->givenName ) ? $abn_details->businessEntity->legalName->givenName : '';
								$otherGivenName = is_string( $abn_details->businessEntity->legalName->otherGivenName ) ? $abn_details->businessEntity->legalName->otherGivenName : '';
								$entityName = $familyName . ", " . $givenName . " " .  $otherGivenName;
							} else {
								$entityName = $abn_details->businessEntity->mainName->organisationName;
							}
							$abn_details_message = $entityStatus .' - '.$entityName;
						}
					}
					$content = preg_replace( "/\/>/", "/><input type='button' value='" . ABR_CHECK_ABN . "' class='itsg_abnlookup_checkabn itsg_abnlookup_checkabn_{$field['id']} gform_button button' onclick='jQuery( \".gform_abnlookup_field_{$field['id']} input\" ).trigger( \"change\" )'>", $content, 1 );
					$content .= "<div role='alert' class='itsg_abnlookup_response itsg_abnlookup_response_{$field['id']} {$entityStatus}'>{$abn_details_message}</div>";
				} elseif ( 'abnlookup_gst_effective_from' == self::is_abnlookup_field( $field ) || 'abnlookup_entity_effective_from' == self::is_abnlookup_field( $field ) ) {
					// remove datepicker
					$content = str_replace( 'datepicker', '', $content);
				}
			}
			return $content;
		}

		/*
         * Applies CSS classes to ABN Lookup fields
         */
		public function abnlookup_css_class( $classes, $field, $form ) {
			if ( 'abn' == self::is_abnlookup_field( $field ) ) {
				$classes .= " gform_abnlookup_field gform_abnlookup_field_" . $field->id;
			} else {
				$field_link_abnlookup = ( int ) ! empty( $field->field_link_abnlookup ) ? $field->field_link_abnlookup : $this->get_first_abnlookup_field( $form );
				if ( 'abnlookup_entity_gst' == self::is_abnlookup_field( $field ) ) {
					$classes .= " gform_abnlookup_entity_gst_field_" . $field_link_abnlookup;
				} elseif ( 'abnlookup_entity_type' == self::is_abnlookup_field( $field ) ) {
					$classes .= " gform_abnlookup_entity_type_field_" . $field_link_abnlookup;
				} elseif ( 'abnlookup_entity_status' == self::is_abnlookup_field( $field ) ) {
					$classes .= " gform_abnlookup_entity_status_field_" . $field_link_abnlookup;
				} elseif ( 'abnlookup_entity_name' == self::is_abnlookup_field( $field ) ) {
					$classes .= " gform_abnlookup_entity_name_field_" . $field_link_abnlookup;
				} elseif ( 'abnlookup_entity_postcode' == self::is_abnlookup_field( $field ) ) {
					$classes .= " gform_abnlookup_entity_postcode_field_" . $field_link_abnlookup;
				} elseif ( 'abnlookup_entity_state' == self::is_abnlookup_field( $field ) ) {
					$classes .= " gform_abnlookup_entity_state_field_" . $field_link_abnlookup;
				} elseif ( 'abnlookup_gst_effective_from' == self::is_abnlookup_field( $field ) ) {
					$classes .= " gform_abnlookup_gst_effective_from_field_" . $field_link_abnlookup;
				} elseif ( 'abnlookup_entity_effective_from' == self::is_abnlookup_field( $field ) ) {
					$classes .= " gform_abnlookup_entity_effective_from_field_" . $field_link_abnlookup;
			}
			}

            return $classes;
        }

		function get_first_abnlookup_field( $form ) {
			foreach ( $form['fields'] as $field ) {
				$field_type = $field->type;
				if ( 'abn' == self::is_abnlookup_field( $field ) ) {
					return $field->id;
				}
			}
			return;
		}

		/*
         * Field options for the form editor
         */
		public static function abnlookup_field_settings( $position, $form_id ) {
			if ( 25 == $position ) {
				// moved to custom tab function
			}
		}

		/*
         * Tooltip for field in form editor
         */
		public static function field_tooltips( $tooltips ){
			$tooltips["form_field_enable_abnlookup"] = "<h6>".ABR_ENABLE."</h6>".ABR_ENABLE_TOOLTIP;
			$tooltips["form_field_validate_abnlookup"] = "<h6>".ABR_VALIDATION."</h6>".ABR_VALIDATION_TOOLTIP;
			$tooltips["form_field_enable_abnlookup_gst"] = "<h6>".ABR_GST_LINK."</h6>".ABR_GST_LINK_TOOLTIP;
			$tooltips["form_field_link_abnlookup"] = "<h6>".ABR_LINK."</h6>".ABR_LINK_TOOLTIP;
			$tooltips["form_field_enable_abnlookup_entity_results"] = "<h6>".ABR_RESULT."</h6>".ABR_RESULT_TOOLTIP;
			return $tooltips;
		}

		/*
         * Checks if field is abnlook up and returns type
         */
		public static function is_abnlookup_field( $field ) {
			$field_type = $field->type;
			if ( 'text' == $field_type && true == $field['enable_abnlookup'] ) {
				return 'abn';
			} elseif ( 'text' == $field_type && '' !== $field['abnlookup_results_enable'] && '' !== $field['abnlookup_results'] ) {
				return $field['abnlookup_results'];
			} elseif ( 'radio' == $field_type && '' !== $field['abnlookup_enable_gst'] ) {
				return 'abnlookup_entity_gst';
			} elseif ( 'date' == $field_type && '' !== $field['abnlookup_results_enable'] && '' !== $field['abnlookup_results'] ) {
				return $field['abnlookup_results'];
			}
			return false;
		}
		
		
		function add_abnlookup_formeditor_tab( $tabs, $form ) {
			$tabs[] = array(
				'id'             => 'abr',
				'title'          => ABR_LOOKUP,
				'toggle_classes' => array( 'abrbutton' ),
				'body_classes'   => array( 'abr' )
			);
			return $tabs;
		}
		
		public static function add_abnlookup_formeditor_tab_content( $tabs, $form ) {
			?>
				<li class="abnlookup_field_setting field_setting" style="display:list-item;">
					<input type="checkbox" id="field_enable_abnlookup" onclick="itsg_gf_abnlookup_click_function( this )"/>
					<label for="field_enable_abnlookup" class="inline">
						<?php echo ABR_FIELD; ?>
					</label>
					<?php gform_tooltip( "form_field_enable_abnlookup" ) ?><br/>
				</li>

				<li class="abnlookup_validate_field_setting field_setting" style="background:rgb(244, 244, 244) none repeat scroll 0px 0px; padding: 10px; border-bottom: 1px solid grey; margin-top: 10px;">
					<label for="field_validate_abnlookup" >
							<?php echo ABR_VALIDATION; ?>
							<?php gform_tooltip( "form_field_validate_abnlookup" ) ?>
					</label>
					<select id="field_validate_abnlookup" onBlur="SetFieldProperty( 'field_validate_abnlookup', this.value);">
						<option value="none"><?php echo ABR_NONE; ?></option>
						<option value="validabn"><?php echo ABR_VALID; ?></option>
						<option value="activeabn"><?php echo ABR_ACTIVE; ?></option>
						<option value="reggst"><?php echo ABR_REG; ?></option>
						<option value="notreggst"><?php echo ABR_NOTREG; ?></option>
					</select>
				</li>

				<li class="abnlookup_entity_results_setting field_setting" style="display:list-item;">
					<input type="checkbox" id="abnlookup_entity_results" onclick="itsg_gf_abnlookup_click_function( this )"/>
					<label for="abnlookup_entity_results" class="inline">
						<?php echo ABR_RESULT; ?>
					</label>
					<?php gform_tooltip( "form_field_enable_abnlookup_entity_results" ) ?><br/>
				</li>

				<li class="abnlookup_entity_results_field_setting_text field_setting" style="background:rgb(244, 244, 244) none repeat scroll 0px 0px; padding-top: 10px; padding-right: 10px; padding-left: 10px; margin: 10px 0px -10px;" >
					<input type="radio" id="abnlookup_entity_type" name="abnlookup_enable_entity_results" onclick="itsg_gf_abnlookup_click_function( this )"/>
					<label for="abnlookup_entity_type" class="inline">
						<?php echo ABR_TYPE; ?>
					</label><br>
					<input type="radio" id="abnlookup_entity_name" name="abnlookup_enable_entity_results" onclick="itsg_gf_abnlookup_click_function( this )"/>
					<label for="abnlookup_entity_name" class="inline">
						<?php echo ABR_NAME; ?>
					</label><br>
					<input type="radio" id="abnlookup_entity_status" name="abnlookup_enable_entity_results" onclick="itsg_gf_abnlookup_click_function( this )"/>
					<label for="abnlookup_entity_status" class="inline">
						<?php echo ABR_STATUS; ?>
					</label><br>
					<input type="radio" id="abnlookup_entity_postcode" name="abnlookup_enable_entity_results" onclick="itsg_gf_abnlookup_click_function( this )"/>
					<label for="abnlookup_entity_postcode" class="inline">
						<?php echo ABR_POSTCODE; ?>
					</label><br>
					<input type="radio" id="abnlookup_entity_state" name="abnlookup_enable_entity_results" onclick="itsg_gf_abnlookup_click_function( this )"/>
					<label for="abnlookup_entity_state" class="inline">
						<?php echo ABR_STATE; ?>
					</label>
				</li>

				<li class="abnlookup_gst_field_setting field_setting" style="display:list-item;">
					<p><strong><?php echo ABR_LOOKUP; ?></strong></p>
					<input type="checkbox" id="field_enable_abnlookup_gst" onclick="itsg_gf_abnlookup_click_function( this )"/>
					<label for="field_enable_abnlookup_gst" class="inline">
						<?php echo ABR_GST_RESULT; ?>
					</label>
					<?php gform_tooltip( "form_field_enable_abnlookup_gst" ) ?><br/>
				</li>

				<li class="abnlookup_entity_results_field_setting_date field_setting" style="background:rgb(244, 244, 244) none repeat scroll 0px 0px; padding-top: 10px; padding-right: 10px; padding-left: 10px; margin: 10px 0px -10px;" >
					<input type="radio" id="abnlookup_entity_effective_from" name="abnlookup_enable_entity_results_date" onclick="itsg_gf_abnlookup_click_function( this )"/>
					<label for="abnlookup_entity_effective_from" class="inline">
						<?php echo ABR_ENTITY_FROM; ?>
					</label><br>
					<input type="radio" id="abnlookup_gst_effective_from" name="abnlookup_enable_entity_results_date" onclick="itsg_gf_abnlookup_click_function( this )"/>
					<label for="abnlookup_gst_effective_from" class="inline">
						<?php echo ABR_GST_FROM; ?>
					</label><br>
				</li>

				<li class="abnlookup_link_field_setting field_setting" style="background:rgb(244, 244, 244) none repeat scroll 0px 0px; padding: 10px; border-bottom: 1px solid grey; margin-top: 10px;" >
				<label for='field_link_abnlookup' >
					<?php echo ABR_LINK; ?>
					<?php gform_tooltip( "form_field_link_abnlookup" ) ?>
				</label>
				<select id='field_link_abnlookup' onBlur="SetFieldProperty( 'field_link_abnlookup', this.value);">
					<!-- automatically filled using JavaScript -->
				</select>
				</li>
			<?php
		}
	}
new ITSG_GF_AbnLookup_Fields();
}