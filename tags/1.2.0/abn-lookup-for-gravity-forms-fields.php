<?php

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

/*
 *  Contains all the functions responsible for ABN Lookup fields
 */
 
 if (!class_exists( 'ITSG_GF_AbnLookup_Fields' )) {
    class ITSG_GF_AbnLookup_Fields {
		
		protected static $form = '';
		protected static $form_id = '';
		
		public function __construct() {
			
			// enqueue scripts for front end
			add_action( 'gform_enqueue_scripts', array( 'ITSG_GF_AbnLookup', 'enqueue_scripts' ), 90, 2 );
			
			add_action( 'gform_field_standard_settings', array(&$this, 'abnlookup_field_settings' ), 10, 2 );
			add_action( 'gform_field_css_class', array(&$this, 'abnlookup_css_class' ), 10, 3 );
			add_filter( 'gform_tooltips', array(&$this, 'field_tooltips' ));
			add_action( 'gform_editor_js', array(&$this, 'abnlookup_editor_js_script' ));
			add_action( 'gform_enqueue_scripts',  array(&$this, 'queue_abnlookup_js' ), 10, 2 );
			add_filter( 'gform_field_content', array(&$this, 'change_abnlookup_fields' ), 10, 5 );
			add_filter( 'gform_pre_render', array(&$this, 'customise_abnlookup_fields' ));
			add_filter( 'gform_admin_pre_render', array(&$this, 'customise_abnlookup_fields' ));
			add_filter( 'gform_pre_validation', array(&$this, 'check_field_values' ));

			add_filter( 'gform_validation', array(&$this, 'validate_abnlookup_fields' ));

		} // END __construct
		
		/*
		 * This is where server side checks of the fields are performed.
		 * - checks for any ABN Lookup linked fields and sets their values
		 * - ensures 'fake' values are not passed by users on the client side
		 */
		function check_field_values( $form ) {
			if (is_array($form) || is_object($form)) {
				// first we need to get the ABN number for the applicable GST field and get the ABN results
				foreach( $form['fields'] as &$field )  {
					if ('abn' == self::is_abnlookup_field($field) ) {
						$value = rgpost( "input_{$field['id']}" );
						$is_hidden = RGFormsModel::is_field_hidden( $form, $field, array() );
						$numbersOnly = preg_replace( "/[^0-9]/","",$value);
						$abn_details = ITSG_GF_AbnLookup::do_abnlookup($numbersOnly);
						$field_values[$field['id']] = $abn_details;
						$field_hidden[$field['id']] = $is_hidden;
					}
				}
				// now we check for linked fields and set their post value
				foreach( $form['fields'] as &$field )  {
					$value = rgpost( "input_{$field['id']}" );
					if ( 'abnlookup_entity_gst' == self::is_abnlookup_field($field) ) {
						$keys = array_keys($field_values);
						foreach($keys as $key) {
							if ( $key == $field['field_link_abnlookup'] ) {
								$abn_details = $field_values[$key];
								if ( $field_hidden[$key] ) {
									$_POST["input_{$field['id']}"] = '';
								} elseif ( isset($abn_details->businessEntity) ) {
									$registered_gst = isset($abn_details->businessEntity->goodsAndServicesTax) ? ( '0001-01-01' == $abn_details->businessEntity->goodsAndServicesTax->effectiveTo) : false;
									if ( $registered_gst ) {
										$_POST["input_{$field['id']}"] = 'Yes';
									} else {
										$_POST["input_{$field['id']}"] = 'No';
									}
								} else {
									$_POST["input_{$field['id']}"] = '';
								}
							}
						}
					} elseif ( 'abnlookup_entity_type' == self::is_abnlookup_field($field) ) {
						$keys = array_keys($field_values);
						foreach($keys as $key) {
							if ( $key == $field['field_link_abnlookup'] ) {
								$abn_details = $field_values[$key];
								if ( $field_hidden[$key] ) {
									$_POST["input_{$field['id']}"] = '';
								} elseif ( isset($abn_details->businessEntity ) ) {
									$entityType = isset($abn_details->businessEntity->entityType->entityDescription) ? $abn_details->businessEntity->entityType->entityDescription : '';
									$_POST["input_{$field['id']}"] = $entityType;
								}
							}
						}
					} elseif ( 'abnlookup_entity_name' == self::is_abnlookup_field($field) ) {
						$keys = array_keys($field_values);
						foreach($keys as $key) {
							if ( $key == $field['field_link_abnlookup'] ) {
								$abn_details = $field_values[$key];
								if ( $field_hidden[$key] ) {
									$_POST["input_{$field['id']}"] = '';
								} elseif ( isset($abn_details->businessEntity ) ) {
									$entityTypeCode = $abn_details->businessEntity->entityType->entityTypeCode;
									if ( 'IND' == $entityTypeCode ) {
										$familyName = is_string($abn_details->businessEntity->legalName->familyName) ? $abn_details->businessEntity->legalName->familyName : '';
										$givenName = is_string($abn_details->businessEntity->legalName->givenName) ? $abn_details->businessEntity->legalName->givenName : '';
										$otherGivenName = is_string($abn_details->businessEntity->legalName->otherGivenName) ? $abn_details->businessEntity->legalName->otherGivenName : '';
										$entityName = $familyName . ", " . $givenName . " " .  $otherGivenName;
									} else {
										$entityName = $abn_details->businessEntity->mainName->organisationName;
									}
									$_POST["input_{$field['id']}"] = $entityName;
								}
							}
						}
					} elseif ( 'abnlookup_entity_status' == self::is_abnlookup_field($field) ) {
						$keys = array_keys($field_values);
						foreach($keys as $key) {
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
					} elseif ( 'abnlookup_entity_postcode' == self::is_abnlookup_field($field) ) {
						$keys = array_keys($field_values);
						foreach($keys as $key) {
							if ( $key == $field['field_link_abnlookup'] ) {
								$abn_details = $field_values[$key];
								if ( $field_hidden[$key] ) {
									$_POST["input_{$field['id']}"] = '';
								} elseif ( isset($abn_details->businessEntity ) ) {
									$entityPostcode = isset($abn_details->businessEntity->mainBusinessPhysicalAddress->postcode) ? $abn_details->businessEntity->mainBusinessPhysicalAddress->postcode : '';
									$_POST["input_{$field['id']}"] = $entityPostcode;
								}
							}
						}
					} elseif ( 'abnlookup_entity_state' == self::is_abnlookup_field($field) ) {
						$keys = array_keys($field_values);
						foreach($keys as $key) {
							if ( $key == $field['field_link_abnlookup'] ) {
								$abn_details = $field_values[$key];
								if ( $field_hidden[$key] ) {
									$_POST["input_{$field['id']}"] = '';
								} elseif ( isset($abn_details->businessEntity ) ) {
									$entityPostcode = isset($abn_details->businessEntity->mainBusinessPhysicalAddress->stateCode) ? $abn_details->businessEntity->mainBusinessPhysicalAddress->stateCode : '';
									$_POST["input_{$field['id']}"] = $entityPostcode;
								}
							}
						}
					}
				}
			}
			return $form;
		} // END check_field_values
		
		/*
		 * Handles custom validation for ABN Lookup and linked fields
		 */
		function validate_abnlookup_fields( $validation_result ) {
			$abnlookup_options = ITSG_GF_AbnLookup::get_options();
			$form = $validation_result['form'];
			$current_page = rgpost( 'gform_source_page_number_' . $form['id'] ) ? rgpost( 'gform_source_page_number_' . $form['id'] ) : 1;
			foreach( $form['fields'] as &$field )  {
				$field_page = $field->pageNumber;
				$is_hidden = RGFormsModel::is_field_hidden( $form, $field, array() );
				if ( $field_page != $current_page || $is_hidden ) {
					continue;
				}
				if ( 'abn' == self::is_abnlookup_field( $field ) ) {
					$value = rgpost( "input_{$field['id']}" );
					$numbersOnly = preg_replace( "/[^0-9]/","",$value);
					$abn_details = ITSG_GF_AbnLookup::do_abnlookup( $numbersOnly );
					$registered_gst = isset( $abn_details->businessEntity->goodsAndServicesTax ) ? ( '0001-01-01' == $abn_details->businessEntity->goodsAndServicesTax->effectiveTo ) : false;
					$entityStatus = isset( $abn_details->businessEntity->entityStatus->entityStatusCode ) ? $abn_details->businessEntity->entityStatus->entityStatusCode : false;
					if ( '' == $value && $field['isRequired'] ) {
						$validation_result['is_valid'] = false; // set the form validation to false
						$field->failed_validation = true;
					} elseif ( '' !== $value && isset($abn_details->exception)) {
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
			//Assign modified $form object back to the validation result
			$validation_result['form'] = $form;
			return $validation_result;
		} // END validate_abnlookup_fields

		/*
		 * Customise ABN lookup fields
		 * - forces 'GST' field to be 'Yes' and 'No' options
		 */
		function customise_abnlookup_fields( $form ) {
			if (is_array($form) || is_object($form)) {
				foreach( $form['fields'] as &$field )  {
					if ('abn' == self::is_abnlookup_field($field) ) {
						if ( '' !== $field['field_validate_abnlookup'] && 'validabn' !== $field['field_validate_abnlookup'] ) {
							$field->isRequired =  true;
						}
					} elseif ('abnlookup_entity_gst' == self::is_abnlookup_field($field) ) {
						// Force GST field 'Yes' and 'No' options
							$field->choices =  array (
								array( 'text' => 'Yes', 'value' => 'Yes' ),
								array( 'text' => 'No', 'value' => 'No' )
							);
					}
				}
			}
		   return $form;
		} // END customise_abnlookup_fields

		/*
		 * Customise ABN lookup fields
		 * - in the form editor, display GST field as 'Yes' and 'No' options
		 * - in front end forms add the response HTML below ABN Lookup fields
		 */
		function change_abnlookup_fields( $content, $field, $value, $lead_id, $form_id ) {
			if ( GFCommon::is_form_editor() ) {
				if ('abnlookup_entity_gst' == self::is_abnlookup_field($field) ) {
					$override_input_value = '<div class="ginput_container ginput_container_radio">
						<ul class="gfield_radio">
							<li>
								<input type="radio" disabled="disabled">
								<label>Yes</label>
							</li>
							<li>
								<input type="radio" disabled="disabled">
								<label>No</label>
							</li>
						</ul>
						</div>';
					$content = preg_replace( "~<div class='ginput_container ginput_container_radio'>.*<\/div>~", $override_input_value, $content);
				}
				return $content;
			} elseif ( 'abn' == self::is_abnlookup_field($field) ) {
				$entityStatus = '';
				$abn_details_message = '';
				$numbersOnly = preg_replace( "/[^0-9]/","",$value);
				if ( 11 == strlen( $numbersOnly ) ) {
					$abn_details = ITSG_GF_AbnLookup::do_abnlookup($numbersOnly);
					if (isset($abn_details->businessEntity)) {
						$entityTypeCode = $abn_details->businessEntity->entityType->entityTypeCode;
						$entityStatus =  $abn_details->businessEntity->entityStatus->entityStatusCode;
						if ($entityTypeCode == 'IND' ) {
							$familyName = is_string($abn_details->businessEntity->legalName->familyName) ? $abn_details->businessEntity->legalName->familyName : '';
							$givenName = is_string($abn_details->businessEntity->legalName->givenName) ? $abn_details->businessEntity->legalName->givenName : '';
							$otherGivenName = is_string($abn_details->businessEntity->legalName->otherGivenName) ? $abn_details->businessEntity->legalName->otherGivenName : '';
							$entityName = $familyName . ", " . $givenName . " " .  $otherGivenName;
						} else {
							$entityName = $abn_details->businessEntity->mainName->organisationName;
						}
						$abn_details_message = $entityStatus .' - '.$entityName;
					}
				} 
				$content .= "<div role='alert' class='itsg_abnlookup_response itsg_abnlookup_response_{$field['id']} {$entityStatus}'>{$abn_details_message}</div>";
			} 
			return $content;
		} // END change_abnlookup_fields

		/*
         * Place JavaScript in front-end footer
         */
		function queue_abnlookup_js($form, $is_ajax) {
			self::$form_id = $form['id'];
			self::$form = $form;
			if ( is_array($form['fields']) || is_object($form['fields']) ) {
				foreach ( $form['fields'] as $field ) {
					if ( 'abn' == self::is_abnlookup_field($field) ) {
						add_action( 'wp_footer', array(&$this, 'abnlookup_js_script' ));
					}
				}
			}
		} // END queue_abnlookup_js
				
		/*
         * JavaScript for front-end
		 * - handles field actions and responses from ABN Ajax requests
         */
		function abnlookup_js_script() {
			$abnlookup_options = ITSG_GF_AbnLookup::get_options();
				foreach( self::$form['fields'] as &$field )  {
					if ( 'abn' == self::is_abnlookup_field($field) ) {
					?>
						<script>
						function itsg_gf_abnlookup_function_<?php echo $field['id']; ?>(self){
							(function( $ ) {
								"use strict";
									var checkABR = function(data){
										var request = $.ajax({
											type: 'POST',
											url: '<?php echo admin_url( 'admin-ajax.php' ); ?>',
											data: data,
											tryCount : 0,
											retryLimit : 3,
											beforeSend: function(){
												gform_validation_message.hide();
												itsg_abnlookup_response.html( '<?php echo strip_tags($abnlookup_options['validation_message_loading'], '<strong><a><u><i>'); ?>' );
												itsg_abnlookup_response.addClass( 'loading' );
												itsg_abnlookup_response.removeClass( 'error Active Cancelled validation_message' );
											},
											success: function(response){
												if(typeof response !== 'undefined' ){
													try {
														var result = JSON.parse(response);
														if (result["exception"] != undefined) {
															gform_validation_message.hide();
															if ( 'Search text is not a valid ABN or ACN' == result['exception']['exceptionDescription'] ) {
																itsg_abnlookup_response.html( '<?php echo strip_tags($abnlookup_options['validation_message_not_valid'], '<strong><a><u><i>'); ?>' );
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
															gform_abnlookup_entity_type_field_input.val('').change();
															gform_abnlookup_entity_status_field_input.val('').change();
															gform_abnlookup_entity_name_field_input.val('').change();
															gform_abnlookup_entity_postcode_field_input.val('').change();
															gform_abnlookup_entity_state_field_input.val('').change();
															gform_abnlookup_entity_gst_field_yes.prop( 'disabled', false );
															gform_abnlookup_entity_gst_field_no.prop( 'disabled', false );
															gform_abnlookup_entity_gst_field_yes.prop( 'checked', false );
															gform_abnlookup_entity_gst_field_no.prop( 'checked', false );
															gform_abnlookup_entity_type_field_input.prop( 'readonly', false );
															gform_abnlookup_entity_status_field_input.prop( 'readonly', false );
															gform_abnlookup_entity_name_field_input.prop( 'readonly', false );
															gform_abnlookup_entity_postcode_field_input.prop( 'readonly', false );
															gform_abnlookup_entity_state_field_input.prop( 'readonly', false );
														} else if (result['businessEntity'] != undefined) {
															console.log(result['businessEntity']);
															var entityTypeCode = result['businessEntity']['entityType']['entityTypeCode'];
															var entityType = result['businessEntity']['entityType']['entityDescription'];
															var entityStatus = result['businessEntity']['entityStatus']['entityStatusCode'];
															var entityPostcode = result['businessEntity']['mainBusinessPhysicalAddress']['postcode'];
															var entityState = result['businessEntity']['mainBusinessPhysicalAddress']['stateCode'];
															if (entityTypeCode == 'IND' ) {
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
															itsg_abnlookup_response.removeClass( 'error loading validation_message' );
															itsg_abnlookup_response.addClass( entityStatus );
															if (result['businessEntity']['goodsAndServicesTax'] != undefined && result['businessEntity']['goodsAndServicesTax']['effectiveTo'] == '0001-01-01' ) {
																gform_abnlookup_entity_gst_field_yes.prop( 'checked', true );
																gform_abnlookup_entity_gst_field_yes.prop( 'disabled', false );
																gform_abnlookup_entity_gst_field_no.prop( 'disabled', true );
															} else {
																gform_abnlookup_entity_gst_field_no.prop( 'checked', true );
																gform_abnlookup_entity_gst_field_no.prop( 'disabled', false );
																gform_abnlookup_entity_gst_field_yes.prop( 'disabled', true );
															}
														gform_abnlookup_entity_gst_field.show();
														gform_abnlookup_entity_type_field.show();
														gform_abnlookup_entity_name_field.show();
														gform_abnlookup_entity_status_field.show();
														gform_abnlookup_entity_postcode_field.show();
														gform_abnlookup_entity_state_field.show();
														}
													}
													catch(e){
														console.log(response);
														itsg_abnlookup_response.text(e);
														itsg_abnlookup_response.removeClass( 'loading Active Cancelled' );
														itsg_abnlookup_response.addClass( 'error validation_message' );
													}
												}
											},
											error: function (request, status, error) {
												if ( 'timeout' == status ) {
													this.tryCount++;
													if (this.tryCount <= this.retryLimit) {
														//try again
														$.ajax(this);
														return;
													}            
													itsg_abnlookup_response.text( '<?php echo strip_tags($abnlookup_options['validation_message_error_communicating'], '<strong><a><u><i>'); ?>' );
												} else {
													itsg_abnlookup_response.text(request.responseText);
												}
												itsg_abnlookup_response.removeClass( 'loading Active Cancelled' );
												itsg_abnlookup_response.addClass( 'error validation_message' );
											},
											timeout: 5000 // set timeout to 5 seconds
										});
										return request;
									};

									var request = false;
									
									var gform_abnlookup_field = $( '.gform_abnlookup_field_<?php echo $field['id']; ?> input' );
									var gform_validation_message = $( '.gform_abnlookup_field_<?php echo $field['id']; ?> .gfield_description.validation_message' );
									var itsg_abnlookup_response = $( '.itsg_abnlookup_response_<?php echo $field['id']; ?>' );
									var gform_abnlookup_entity_gst_field = $( '.gform_abnlookup_entity_gst_field_<?php echo $field['id']; ?>' );
									var gform_abnlookup_entity_gst_field_yes = $( '.gform_abnlookup_entity_gst_field_<?php echo $field['id']; ?> input[value="Yes"]' );
									var gform_abnlookup_entity_gst_field_no = $( '.gform_abnlookup_entity_gst_field_<?php echo $field['id']; ?> input[value="No"]' );
									var gform_abnlookup_entity_type_field = $( '.gform_abnlookup_entity_type_field_<?php echo $field['id']; ?>' );
									var gform_abnlookup_entity_type_field_input = $( '.gform_abnlookup_entity_type_field_<?php echo $field['id']; ?> input' );
									var gform_abnlookup_entity_status_field = $( '.gform_abnlookup_entity_status_field_<?php echo $field['id']; ?>' );
									var gform_abnlookup_entity_status_field_input = $( '.gform_abnlookup_entity_status_field_<?php echo $field['id']; ?> input' );
									var gform_abnlookup_entity_name_field = $( '.gform_abnlookup_entity_name_field_<?php echo $field['id']; ?>' );
									var gform_abnlookup_entity_name_field_input = $( '.gform_abnlookup_entity_name_field_<?php echo $field['id']; ?> input' );
									var gform_abnlookup_entity_postcode_field = $( '.gform_abnlookup_entity_postcode_field_<?php echo $field['id']; ?>' );
									var gform_abnlookup_entity_postcode_field_input = $( '.gform_abnlookup_entity_postcode_field_<?php echo $field['id']; ?> input' );
									var gform_abnlookup_entity_state_field = $( '.gform_abnlookup_entity_state_field_<?php echo $field['id']; ?>' );
									var gform_abnlookup_entity_state_field_input = $( '.gform_abnlookup_entity_state_field_<?php echo $field['id']; ?> input' );
									
									if ( '' !== itsg_abnlookup_response.html() ) {
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
									} else {
										// hide linked fields
										gform_abnlookup_entity_gst_field.closest('li.gfield').hide()
										gform_abnlookup_entity_type_field.closest('li.gfield').hide();
										gform_abnlookup_entity_status_field.closest('li.gfield').hide();
										gform_abnlookup_entity_name_field.closest('li.gfield').hide();
										gform_abnlookup_entity_postcode_field.closest('li.gfield').hide();
										gform_abnlookup_entity_state_field.closest('li.gfield').hide();
									}

									gform_abnlookup_field.change(function() {
										self = $(this);
										var numbersOnly = $(this).val().replace(/\D/g, '' );
										if (numbersOnly.length == 11) {
											console.log(numbersOnly);
											var abn = numbersOnly;
											var data = {
												'action': 'itsg_gf_abnlookup_check_ajax',
												'abn': abn
											}
										if(request && request.readyState !== 4){
											console.log( 'Abort! -- another request has been submitted.' )
											request.abort();
										}
										
										request = checkABR(data);
										} else {
											gform_abnlookup_entity_gst_field.hide();
											gform_abnlookup_entity_type_field.hide();
											gform_abnlookup_entity_status_field.hide();
											gform_abnlookup_entity_name_field.hide();
											gform_abnlookup_entity_postcode_field.hide();
											gform_abnlookup_entity_state_field.hide();
											gform_abnlookup_entity_type_field_input.val('').change();
											gform_abnlookup_entity_status_field_input.val('').change();
											gform_abnlookup_entity_name_field_input.val('').change();
											gform_abnlookup_entity_postcode_field_input.val('').change();
											gform_abnlookup_entity_state_field_input.val('').change();
											gform_abnlookup_entity_gst_field_yes.prop( 'disabled', false );
											gform_abnlookup_entity_gst_field_no.prop( 'disabled', false );
											gform_abnlookup_entity_gst_field_yes.prop( 'checked', false );
											gform_abnlookup_entity_gst_field_no.prop( 'checked', false );
											gform_validation_message.hide();
											itsg_abnlookup_response.html( "<?php echo strip_tags($abnlookup_options['validation_message_11_char'], '<strong><a><u><i>' ); ?>" );
											itsg_abnlookup_response.addClass( 'error validation_message' );
											itsg_abnlookup_response.removeClass( 'loading Active Cancelled' );
										}
									});
							}(jQuery));
						}
						
						// runs the main function when the page loads
						jQuery(document).bind( 'gform_post_render', function($) {
							itsg_gf_abnlookup_function_<?php echo $field['id']; ?>(jQuery(this));
							jQuery( "#input_<?php echo self::$form['id']; ?>_<?php echo $field['id']; ?>" ).keydown(function( event ) {
							  if (event.which == 13 || event.keyCode == 13) {
								  event.preventDefault();
								  jQuery(this).trigger("change");
							}
							});
						});
						
						</script>
					<?php
					}
				}
		} // END queue_abnlookup_js
		
		/*
         * Applies CSS classes to ABN Lookup fields
         */
		public static function abnlookup_css_class( $classes, $field, $form ) {
			if ( 'abn' == self::is_abnlookup_field($field) ) {
				$classes .= " gform_abnlookup_field gform_abnlookup_field_".rgar($field, 'id' );
			} elseif ( 'abnlookup_entity_gst' == self::is_abnlookup_field($field) ) {
				$classes .= " gform_abnlookup_entity_gst_field_".rgar($field, 'field_link_abnlookup' );
			} elseif ( 'abnlookup_entity_type' == self::is_abnlookup_field($field) ) {
				$classes .= " gform_abnlookup_entity_type_field_".rgar($field, 'field_link_abnlookup' );
			} elseif ( 'abnlookup_entity_status' == self::is_abnlookup_field($field) ) {
				$classes .= " gform_abnlookup_entity_status_field_".rgar($field, 'field_link_abnlookup' );
			} elseif ( 'abnlookup_entity_name' == self::is_abnlookup_field($field) ) {
				$classes .= " gform_abnlookup_entity_name_field_".rgar($field, 'field_link_abnlookup' );
			} elseif ( 'abnlookup_entity_postcode' == self::is_abnlookup_field($field) ) {
				$classes .= " gform_abnlookup_entity_postcode_field_".rgar($field, 'field_link_abnlookup' );
			} elseif ( 'abnlookup_entity_state' == self::is_abnlookup_field($field) ) {
				$classes .= " gform_abnlookup_entity_state_field_".rgar($field, 'field_link_abnlookup' );
			}
            return $classes;
        } // END abnlookup_css_class
		
		/*
         * Field options for the form editor
         */
		public static function abnlookup_field_settings( $position, $form_id ) {
			if ( 25 == $position ) {
				?>
				<li class="abnlookup_field_setting field_setting" style="display:list-item;">
					<p><strong><?php _e( "ABN Lookup", "itsg_gf_abnlookup" ); ?></strong></p>
					<input type="checkbox" id="field_enable_abnlookup" onclick="itsg_gf_abnlookup_click_function(jQuery(this))"/>
					<label for="field_enable_abnlookup" class="inline">
						<?php _e( "ABN Lookup field", "itsg_gf_abnlookup" ); ?>
					</label>
					<?php gform_tooltip( "form_field_enable_abnlookup" ) ?><br/>
				</li>
				
				<li class="abnlookup_validate_field_setting field_setting" style="display:list-item;">
					<label for="field_validate_abnlookup" class="inline">
							<?php _e( "Validate ABN Lookup field", "itsg_gf_abnlookup" ); ?>
						</label>
					<select id="field_validate_abnlookup" onBlur="SetFieldProperty( 'field_validate_abnlookup', this.value);">											
						<option value="validabn"><?php _e( "Valid ABN", "artsapply_field_layout" ); ?></option>
						<option value="activeabn"><?php _e( "Active ABN", "artsapply_field_layout" ); ?></option>
						<option value="reggst"><?php _e( "Registered for GST", "artsapply_field_layout" ); ?></option>
						<option value="notreggst"><?php _e( "Not registered for GST", "artsapply_field_layout" ); ?></option>
					</select>	
					<?php gform_tooltip( "form_field_validate_abnlookup" ) ?>
					<hr>
				</li>
				
				<li class="abnlookup_entity_results_setting field_setting" style="display:list-item;">
					<input type="checkbox" id="abnlookup_entity_results" onclick="itsg_gf_abnlookup_click_function(jQuery(this))"/>
					<label for="abnlookup_entity_results" class="inline">
						<?php _e( "ABN Lookup results field", "itsg_gf_abnlookup" ); ?>
					</label>
					<?php gform_tooltip( "form_field_enable_abnlookup_entity_results" ) ?><br/>
				</li>
				
				<li class="abnlookup_entity_results_field_setting field_setting" style="display:list-item;">
					<input type="radio" id="abnlookup_entity_type" name="abnlookup_enable_entity_results" onclick="itsg_gf_abnlookup_click_function(this)"/>
					<label for="abnlookup_entity_type" class="inline">
						<?php _e( "Entity type", "itsg_gf_abnlookup" ); ?>
					</label><br>
					<input type="radio" id="abnlookup_entity_name" name="abnlookup_enable_entity_results" onclick="itsg_gf_abnlookup_click_function(this)"/>
					<label for="abnlookup_entity_name" class="inline">
						<?php _e( "Entity name", "itsg_gf_abnlookup" ); ?>
					</label><br>
					<input type="radio" id="abnlookup_entity_status" name="abnlookup_enable_entity_results" onclick="itsg_gf_abnlookup_click_function(this)"/>
					<label for="abnlookup_entity_status" class="inline">
						<?php _e( "ABN status", "itsg_gf_abnlookup" ); ?>
					</label><br>
					<input type="radio" id="abnlookup_entity_postcode" name="abnlookup_enable_entity_results" onclick="itsg_gf_abnlookup_click_function(this)"/>
					<label for="abnlookup_entity_postcode" class="inline">
						<?php _e( "Entity postcode", "itsg_gf_abnlookup" ); ?>
					</label><br>
					<input type="radio" id="abnlookup_entity_state" name="abnlookup_enable_entity_results" onclick="itsg_gf_abnlookup_click_function(this)"/>
					<label for="abnlookup_entity_state" class="inline">
						<?php _e( "Entity state", "itsg_gf_abnlookup" ); ?>
					</label>
				</li>
				
				<li class="abnlookup_gst_field_setting field_setting" style="display:list-item;">
					<p><strong>ABN Lookup</strong></p>
					<input type="checkbox" id="field_enable_abnlookup_gst" onclick="itsg_gf_abnlookup_click_function(this)"/>
					<label for="field_enable_abnlookup_gst" class="inline">
						<?php _e( "GST results field", "itsg_gf_abnlookup" ); ?>
					</label>
					<?php gform_tooltip( "form_field_enable_abnlookup_gst" ) ?><br/>
				</li>
				
				<li class="abnlookup_link_field_setting field_setting" style="display:list-item;">
				<label for='field_link_abnlookup' class="inline">
						<?php _e( "Link ABN Lookup field", "itsg_gf_abnlookup" ); ?>
					</label>
					<select id='field_link_abnlookup' onBlur="SetFieldProperty( 'field_link_abnlookup', this.value);">
						<!-- automatically filled using JavaScript -->
					</select>
					<?php gform_tooltip( "form_field_link_abnlookup" ) ?><hr>
				</li>
			<?php
			}
		} // END abnlookup_field_settings
		
		/*
         * JavaScript for form editor
         */
		function abnlookup_editor_js_script() {
			?>
			<script>
				jQuery(document).bind( 'gform_load_field_settings', function (event, field, form) {
					var field_type = field['type'];
					if ( 'text' == field_type ) {
					
						// the fields
						var abnlookup_field = jQuery( ".abnlookup_field_setting" );
						var abnlookup_field_entity_results = jQuery( ".abnlookup_entity_results_setting" );
						var abnlookup_field_entity_results_setting = jQuery( ".abnlookup_entity_results_field_setting" );
						var abnlookup_field_link = jQuery(this).find( ".abnlookup_link_field_setting" );
						var abnlookup_field_validate = jQuery( ".abnlookup_validate_field_setting" );
						
						// lets display the options in the page
						abnlookup_field_link.show();
						abnlookup_field.show();
						abnlookup_field_entity_results.show();
						abnlookup_field_entity_results_setting.show();
						abnlookup_field_validate.show();
						
						// first remove existing list of options
						abnlookup_field_link.find('select option').remove();
						
						// now to create the list of options and assign to link field
						for( var i = 0; i < form.fields.length; i++ ) {
							if ( 'true' == form.fields[i].enable_abnlookup ) {
								var value = form.fields[i].label;
								var key = form.fields[i].id;
								abnlookup_field_link.find( 'select' ).append( '<option value=' + key + '>' + value + '</option>' );
							}
						}
						
						// now get their values
						var enable_abnlookup_value = (typeof field['enable_abnlookup'] != 'undefined' && field['enable_abnlookup'] != '' ) ? field['enable_abnlookup'] : false;
						var abnlookup_field_entity_value = (typeof field['abnlookup_results_enable'] != 'undefined' && field['abnlookup_results_enable'] != '' ) ? field['abnlookup_results_enable'] : false;
						
						// now set the value to the option field
						if ( enable_abnlookup_value != false ) {
							abnlookup_field.find( "input:checkbox" ).attr( 'checked', 'checked' );
						} else {
							abnlookup_field.find( "input:checkbox" ).removeAttr( 'checked' );
						}
						
						if ( abnlookup_field_entity_value != false ) {
							abnlookup_field_entity_results.find( "input:checkbox" ).attr( 'checked', 'checked' );
						} else {
							abnlookup_field_entity_results.find( "input:checkbox" ).removeAttr( 'checked' );
						}
								
						if ( field["abnlookup_results"] !== undefined ) {
							abnlookup_field_entity_results_setting.find( "input#" + field["abnlookup_results"] ).prop( 'checked', true );
						}
						
						abnlookup_field_validate.find( "select" ).val(field["field_validate_abnlookup"] == undefined ? "validabn" : field["field_validate_abnlookup"]);
						abnlookup_field_link.find( "select" ).val(field['field_link_abnlookup'] == undefined ? "" : field['field_link_abnlookup']);	
						
					} else if ( 'radio' == field_type ) {
					
						// the fields
						var abnlookup_field_gst = jQuery(this).find( ".abnlookup_gst_field_setting" );
						var abnlookup_field_link = jQuery(this).find( ".abnlookup_link_field_setting" );
						
						// lets display the options in the page
						abnlookup_field_gst.show();
						abnlookup_field_link.show();
						
						// now get their values
						var abnlookup_field_gst_value = (typeof field['abnlookup_enable_gst'] != 'undefined' && field['abnlookup_enable_gst'] != '' ) ? field['abnlookup_enable_gst'] : false;
						
						// LINK FIELD - first delete existing list of options 
						abnlookup_field_link.find( 'select option' ).remove();
						
						// now to create the list of options and assign to link field
						for( var i = 0; i < form.fields.length; i++ ) {
							if ( 'true' == form.fields[i].enable_abnlookup ) {
								var value = form.fields[i].label;
								var key = form.fields[i].id;
								abnlookup_field_link.find( 'select' ).append( '<option value=' + key + '>' + value + '</option>' );
							}
						}
						
						// now set the value to the option field
						if (abnlookup_field_gst_value != false) {
							abnlookup_field_gst.find( "input:checkbox" ).attr( 'checked', 'checked' );
						} else {
							abnlookup_field_gst.find( "input:checkbox" ).removeAttr( 'checked' );
						}
						
						abnlookup_field_link.find( "select" ).val(field['field_link_abnlookup'] == undefined ? "" : field['field_link_abnlookup']);						
					}
				});
				
				jQuery( ".abnlookup_field_setting input" ).click(function () {
					if (jQuery(this).is( ":checked" )) {
						SetFieldProperty( 'enable_abnlookup', 'true' );
						SetFieldProperty( 'abnlookup_results_enable', '' ); // force opposite value to off
					} else {
						SetFieldProperty( 'enable_abnlookup', '' );
					}
				});
				
				jQuery( ".abnlookup_entity_results_setting input" ).click(function () {
					if (jQuery(this).is( ":checked" )) {
						SetFieldProperty( 'abnlookup_results_enable', 'true' );
						SetFieldProperty( 'enable_abnlookup', '' ); // force opposite value to off
					} else {
						SetFieldProperty( 'abnlookup_results_enable', '' );
					}
				});
				
				jQuery( ".abnlookup_entity_results_setting input" ).click(function () {
					if (jQuery(this).is( ":checked" )) {
						SetFieldProperty( 'enable_abnlookup', '' ); // make sure enable_abnlookup is not true
					}
				});
				
				jQuery( ".abnlookup_entity_results_field_setting input" ).click(function () {
					if (jQuery(this).is( ":checked" )) {
						SetFieldProperty( 'abnlookup_results', jQuery(this).attr('id') );
					} else {
						SetFieldProperty( 'enable_results', '' );
					}
				});	

			function itsg_gf_abnlookup_click_function(self){
				var abnlookup_enable_gst = (typeof field['abnlookup_enable_gst'] != 'undefined' && field['abnlookup_enable_gst'] != '' ) ? field['abnlookup_enable_gst'] : false;

				if (abnlookup_enable_gst != false) {				
					//check the checkbox if previously checked
					jQuery(self).find( ".choices_setting:visible" ).hide();
					jQuery(self).find( ".other_choice_setting:visible" ).hide();
							
					jQuery(self).find( ".ginput_container ul li:nth-child(1) label" ).text( 'Yes' );
					jQuery(self).find( ".ginput_container ul li:nth-child(2) label" ).text( 'No' );
					jQuery(self).find( ".ginput_container ul li:nth-child(n+3)" ).remove();
				}
			
				// handles displaying the 'Validate ABN Lookup field' select list
				if (jQuery( 'input#field_enable_abnlookup:visible' ).is( ":checked" )) {
					jQuery('.abnlookup_validate_field_setting').show();	// show validate options
					jQuery('input#abnlookup_entity_results').attr('checked', false);	// untick opposite option
				} else {
					jQuery('.abnlookup_validate_field_setting').hide(); // hide validate options
				}
				
				// handles displaying the 'Entity results' radio list
				if (jQuery( 'input#abnlookup_entity_results:visible' ).is( ":checked" )) {
					jQuery('.abnlookup_entity_results_field_setting').show(); // show entity result options
					jQuery('.abnlookup_link_field_setting').show();	// show the 'Link ABN Field' setting
					jQuery('input#field_enable_abnlookup').attr('checked', false);	// untick opposite option
				} else {
					jQuery('.abnlookup_entity_results_field_setting').hide(); // hide entity result options
					jQuery('.abnlookup_link_field_setting').hide(); // hide the 'Link ABN Field' setting
				}
				
				// handles how the GST field is displayed if the GST option is enabled
				jQuery( 'input#field_enable_abnlookup_gst:visible' ).each(function() {
					if (jQuery(this).is( ":checked" )) {
						
						jQuery(this).parent( "li" ).next().show(); // show the 'Link ABN Field' setting
							
						// hide the choices section
						jQuery(this).closest( "ul" ).find( '.choices_setting' ).hide();
						jQuery(this).closest( "ul" ).find( '.other_choice_setting' ).hide();
							
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
						jQuery(this).closest( "li.gfield" ).find( '.ginput_container_radio' ).html(override_input_value);
					} else {	
						// hide the 'Link ABN Field' setting
						jQuery(this).parent( "li" ).next().hide();
							
						// display the choices section
						jQuery(this).closest( "ul" ).find( '.choices_setting' ).show();
						jQuery(this).closest( "ul" ).find( '.other_choice_setting' ).show();
							
						// update the field option preview to what is contained in the choices setion
						InsertFieldChoice(0);
						DeleteFieldChoice(0);
					}
				});
				
			} // END itsg_gf_abnlookup_click_function
		
			// trigger for when field is opened
			jQuery(document).on( 'click', 'ul.gform_fields li.gfield', function(){
				itsg_gf_abnlookup_click_function(jQuery(this));
			});
		</script>	
		<?php
		} // END abnlookup_editor_js_script
		
		/*
         * Tooltip for field in form editor
         */
		public static function field_tooltips( $tooltips ){
			$tooltips["form_field_enable_abnlookup"] = "<h6>".__( "Enable ABN Lookup", "itsg_gf_abnlookup" )."</h6>".__( "Check this box to integrate this field with the Australian Government's ABN Lookup tool.", "itsg_gf_abnlookup" );
			$tooltips["form_field_validate_abnlookup"] = "<h6>".__( "Validate ABN Lookup field", "itsg_gf_abnlookup" )."</h6>".__( "Choose the level of validation required for the ABN Lookup field.", "itsg_gf_abnlookup" );
			$tooltips["form_field_enable_abnlookup_gst"] = "<h6>".__( "Enable ABN Lookup GST", "itsg_gf_abnlookup" )."</h6>".__( "Check this box to link the field with an ABN Lookup field.", "itsg_gf_abnlookup" );
			$tooltips["form_field_link_abnlookup"] = "<h6>".__( "Link ABN Lookup field", "itsg_gf_abnlookup" )."</h6>".__( "Select the ABN Lookup field to link to.", "itsg_gf_abnlookup" );
			$tooltips["form_field_enable_abnlookup_entity_results"] = "<h6>".__( "ABN Lookup results field", "itsg_gf_abnlookup" )."</h6>".__( "Check this box to link the field with an ABN Lookup field.", "itsg_gf_abnlookup" );
			return $tooltips;
		} // END field_tooltips
		
		/*
         * Checks if field is abnlook up and returns type
         */
		public static function is_abnlookup_field( $field ) {
			$field_type = rgar($field, "type" );
			if ( 'text' == $field_type && array_key_exists( 'enable_abnlookup', $field) && true == $field['enable_abnlookup'] ) {
				return 'abn';
			} elseif ( 'text' == $field_type && array_key_exists( 'abnlookup_results_enable', $field) && '' !== $field['abnlookup_results_enable'] && array_key_exists( 'abnlookup_results', $field) && '' !== $field['abnlookup_results'] ) {
				return $field['abnlookup_results'];
			} elseif ( 'radio' == $field_type && array_key_exists( 'abnlookup_enable_gst', $field) && '' !== $field['abnlookup_enable_gst'] ) {
				return 'abnlookup_entity_gst';
			}
			return false;
		} // END is_abnlookup_field
	}
$ITSG_GF_AbnLookup_Fields = new ITSG_GF_AbnLookup_Fields();
} 