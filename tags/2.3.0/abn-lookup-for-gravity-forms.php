<?php
/**
 * Plugin Name: ABN Lookup for Gravity Forms
 * Plugin URI: https://plugins.bkbn.au/docs/abr/
 * Description: Connect the Australian Government ABN Lookup tool to Gravity Forms.
 * Version: 2.3.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Tested up to: 6.7
 * Author: BAKKBONE Australia
 * Author URI: https://www.bakkbone.com.au/
 * License: GNU General Public License (GPL) 3.0 or later
 * License URI: https://www.gnu.org/licenses/gpl.html
 * Text Domain: abn-lookup-for-gravity-forms
 * Copyright 2016 Adrian Gordon
 * Copyright 2023 BAKKBONE Australia
**/

if ( ! defined(  'ABSPATH' ) ) {
	die();
}

add_action( 'admin_notices', array( 'ITSG_GF_AbnLookup', 'admin_warnings' ), 20);

if ( !class_exists( 'ITSG_GF_AbnLookup' ) ) {
	class ITSG_GF_AbnLookup {

		private static $slug = 'itsg_gf_abnlookup';

		function __construct() {
            add_action( 'gform_loaded', array( $this, 'register_actions' ) );
		}

		/*
         * Register plugin functions
         */
		function register_actions() {
				// start the plugin

				//  functions for fields
				require_once( plugin_dir_path( __FILE__ ).'abn-lookup-for-gravity-forms-fields.php' );

				// addon framework
				require_once( plugin_dir_path( __FILE__ ).'abn-lookup-for-gravity-forms-addon.php' );

				// ajax hook for users that are logged in
				add_action( 'wp_ajax_itsg_gf_abnlookup_check_ajax', array( $this, 'itsg_gf_abnlookup_check_ajax' ) );

				// ajax hook for users that are not logged in
				add_action( 'wp_ajax_nopriv_itsg_gf_abnlookup_check_ajax', array( $this, 'itsg_gf_abnlookup_check_ajax' ) );

				// plugin 'settings' link on wp-admin installed plugins page
				add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( $this, 'plugin_action_links') );
		}

		/*
         * Add 'Settings' link to plugin in WordPress installed plugins page
         */
		function plugin_action_links( $links ) {
			$action_links = array(
				'settings' => '<a href="' . esc_url( add_query_arg( array( 'page' => 'gf_settings', 'subview' => 'itsg_gf_abnlookup_settings' ), get_admin_url() . 'admin.php' ) ) . '" title="' . esc_attr( __('View ABN Lookup Settings', 'abn-lookup-for-gravity-forms') ) . '">' . __('Settings', 'abn-lookup-for-gravity-forms') . '</a>',
			);

			return array_merge( $action_links, $links );
		}

		/*
         * Run when plugin is activated
		 * - adds daily cron job to clear ABN Lookup cache
         */
		public static function activation() {
			wp_schedule_event( time(), 'daily', 'itsg_abnlookup_clear_cache_cron' );
		}

		/*
         * Run when plugin is deactivated
		 * - clear ABN Lookup cache
		 * - delete daily cron job that clears ABN Lookup cache
		 */
		public function deactivation() {
			self::clear_database_cache();
			wp_clear_scheduled_hook( 'itsg_abnlookup_clear_cache_cron' );
		}

		/*
         * Clears ABN Lookup cache
		 * - triggered through daily cron job and when plugin is deactivated
		 */
		public function clear_database_cache() {
			global $wpdb;
			$table_incomplete = $wpdb->prefix . "options";
			$result  = $wpdb->query( $wpdb->prepare("DELETE FROM ".$table_incomplete." WHERE `option_name` like 'itsg_abnlookup_%'") );
		}

		/*
         * Handles Ajax request for ABN Lookup
		 */
		public static function itsg_gf_abnlookup_check_ajax() {
			error_log(check_ajax_referer('abnlookup', 'nonce'));
			error_log('nonce: '.$_POST['nonce']);
			/**if (!check_ajax_referer('abnlookup', 'nonce')) {
			    wp_nonce_ays();
			}**/
			$abn = isset( $_POST['abn'] ) ? preg_replace( "/[^0-9]/","", sanitize_text_field($_POST['abn']) ) : null;

			if ( is_null($abn) || '' == $abn ) {
				$result = array( 'exception' => array ( 'exceptionDescription' => __('Empty ABN value passed.', 'abn-lookup-for-gravity-forms') ) );
			} else {
				$result = self::do_abnlookup( $abn );
			}

			die( wp_json_encode( $result ) );
		}
		
		/*
		 * Handles ABN Lookup
		 * - first checks cache
		 * - if not in cache, checks ABN against the ABR
		 * - saves results to cache
		 * - returns results
		 */
		public static function do_abnlookup( $abn ) {
			if ( empty( $abn )  ) {
				return false;
			}

			$abn = sanitize_text_field( $abn );

			$abnlookup_options = self::get_options();

			if ( '' == $abnlookup_options['guid'] ) {
				return array( 'exception' => array ( 'exceptionDescription' => __('ABN Lookup for Gravity Forms has not been configured. The GUID necessary to communicate with the Australian Business Register has not been specified.', 'abn-lookup-for-gravity-forms') ) );
			}

			/** supply from cache **/
			//$result_cache = get_option( "itsg_abnlookup_{$abn}", 0 ); // redundant - using native WordPress transients
			$result_cache = get_transient( "itsg_abnlookup_{$abn}" );
			if( $result_cache ){
				$cache_datetime = strtotime( $result_cache->dateRegisterLastUpdated );
				$current_datetime =  strtotime( 'now' );
				if ( ( $current_datetime - $cache_datetime ) < DAY_IN_SECONDS ) {
					return $result_cache;
				}
			}
			/** cache end **/
			//$abnlookup = new abnlookup($abnlookup_options['guid']);
			//$result = $abnlookup->searchByAbn($abn)->ABRPayloadSearchResults->response;

			$url = "https://abr.business.gov.au/ABRXMLSearch/AbrXmlSearch.asmx/SearchByABNv202001?searchString={$abn}&includeHistoricalDetails=N&authenticationGuid={$abnlookup_options['guid']}";

			$result = wp_remote_get( $url );
			$result = simplexml_load_string( $result['body'] )->response;
			$result = wp_json_encode( $result );
			$result = json_decode( $result );

			/** save the cache **/
			//update_option( "itsg_abnlookup_{$abn}", $result ); // redundant - using native WordPress transients
			set_transient( "itsg_abnlookup_{$abn}" , $result, DAY_IN_SECONDS ); // transient will live for a day
			/** end cache **/
			return $result;
		}

		/*
		 * Function for enqueuing all the required scripts
		 */
		public static function enqueue_scripts( $form, $is_ajax ) {
			if ( is_array( $form['fields'] ) || is_object( $form['fields'] ) ) {
				$abnlookup_options = self::get_options();
				if ( is_array( $form['fields'] ) || is_object( $form['fields'] ) ) {
					foreach ( $form['fields'] as $field ) {
						if ( ITSG_GF_AbnLookup_Fields::is_abnlookup_field( $field ) ) {
							if ( true == $abnlookup_options['includecss'] ) {
								wp_enqueue_style(  'itsg_gfabnlookup_css', plugins_url(  'css/abnlookup.css', __FILE__ ) );
							}
						}
					}
				}
			}
		}

		/*
		 *   Handles the plugin options.
		 *   Default values are stored in an array.
		 */
		public static function get_options(){
			$defaults = array(
				'guid' => '',
				'includecss' => true,
				'validation_message_not_valid' => __('The ABN provided is not valid. Check the number entered and try again, or use the https://abr.business.gov.au/ website to confirm your ABN.', 'abn-lookup-for-gravity-forms'),
				'validation_message_activeabn' => __('The ABN provided is not active. Entities that do not have an active ABN cannot complete this form.', 'abn-lookup-for-gravity-forms'),
				'validation_message_reggst' => __('The ABN provided is not registered for GST. Entities that are not registered for GST cannot complete this form.', 'abn-lookup-for-gravity-forms'),
				'validation_message_notreggst' => __('The ABN provided is registered for GST. Entities that are registered for GST cannot complete this form.', 'abn-lookup-for-gravity-forms'),
				'validation_message_11_char' => __('The information entered does not match a valid ABN. ABNs need to be 11 digits.', 'abn-lookup-for-gravity-forms'),
				'validation_message_loading' => __('Checking ABN with the Australian Business Register.', 'abn-lookup-for-gravity-forms'),
				'validation_message_error_communicating' => __('Error communicating with the Australian Business Register.', 'abn-lookup-for-gravity-forms'),
				'lookup_timeout' => 5,
				'lookup_retries' => 3,
			);
			$options = wp_parse_args( get_option( 'gravityformsaddon_itsg_gf_abnlookup_settings_settings' ), $defaults );
			return $options;
		}

		/*
         * Warning message if Gravity Forms is installed and enabled
         */
		public static function admin_warnings() {
			$abnlookup_options = self::get_options();
			if ( !self::is_gravityforms_installed() ) {
				$html = sprintf(
					'<div class="error"><h3>%s</h3><p>%s</p><p>%s</p></div>',
						__('Warning', 'abn-lookup-for-gravity-forms'),
						sprintf ( __('The plugin %s requires Gravity Forms to be installed.', 'abn-lookup-for-gravity-forms'), '<strong>'.__('ABN Lookup for Gravity Forms', 'abn-lookup-for-gravity-forms').'</strong>' ),
						sprintf ( __('Please %sdownload the latest version%s of Gravity Forms and try again.', 'abn-lookup-for-gravity-forms'), '<a target="_blank" href="https://rocketgenius.pxf.io/bakkbone">', '</a>' )
				);
				echo wp_kses_post($html);
			} elseif ( '' == $abnlookup_options['guid'] ) {
				$html = sprintf(
					'<div class="error"><h3>%s</h3><p>%s</p><p>%s</p><p>%s</p></div>',
						__('Warning', 'abn-lookup-for-gravity-forms'),
						sprintf ( __('The plugin %s requires a GUID to communicate with the Australian Business Register.', 'abn-lookup-for-gravity-forms'), '<strong>'.__('ABN Lookup for Gravity Forms', 'abn-lookup-for-gravity-forms').'</strong>' ),
						sprintf ( __('To receive a GUID see %sweb services registration%s on the Australian Business Register website.', 'abn-lookup-for-gravity-forms'), '<a target="_blank" href="http://abr.business.gov.au/webservices.aspx">', '</a>' ),
						sprintf ( __('Once you have a GUID you will need to enter it in the %sABN Lookup for Gravity Forms Settings%s page.', 'abn-lookup-for-gravity-forms'), '<a href="' . admin_url( 'admin.php?page=gf_settings&subview=itsg_gf_abnlookup_settings' ) .'">', '</a>' )
				);
				echo wp_kses_post($html);
			}
		}

		/*
         * Check if GF is installed
         */
        private static function is_gravityforms_installed() {
			return class_exists( 'GFCommon' );
        }
	}
}
$ITSG_GF_AbnLookup = new ITSG_GF_AbnLookup();