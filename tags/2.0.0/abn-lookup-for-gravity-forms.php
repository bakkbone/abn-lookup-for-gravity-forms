<?php
/**
 * Plugin Name: ABN Lookup for Gravity Forms
 * Plugin URI: https://docs.bkbn.au/v/abr/
 * Description: Connect the Australian Government ABN Lookup tool to Gravity Forms.
 * Version: 2.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
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

load_plugin_textdomain( 'abn-lookup-for-gravity-forms', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

add_action( 'admin_notices', array( 'ITSG_GF_AbnLookup', 'admin_warnings' ), 20);

//register_activation_hook( __FILE__, array( 'ITSG_GF_AbnLookup', 'activation' ) ); // redundant - using native WordPress transients

/*
 *   Setup the main plugin class
 */
if ( !class_exists( 'ITSG_GF_AbnLookup' ) ) {
	class ITSG_GF_AbnLookup {

		private static $slug = 'itsg_gf_abnlookup';

		/*
         * Construct the plugin object
         */
		function __construct() {

			// register plugin functions through 'gform_loaded' -
			// this delays the registration until Gravity Forms has loaded, ensuring it does not run before Gravity Forms is available.
            add_action( 'gform_loaded', array( $this, 'register_actions' ) );

		}

		/*
         * Register plugin functions
         */
		function register_actions() {
				// start the plugin

				//register_deactivation_hook(__FILE__, array( $this, 'deactivation' ) ); // redundant - using native WordPress transients

				//add_action( 'itsg_abnlookup_clear_cache_cron', array( $this, 'clear_database_cache' ) ); // redundant - using native WordPress transients

				//  functions for fields
				require_once( plugin_dir_path( __FILE__ ).'abn-lookup-for-gravity-forms-fields.php' );

				// addon framework
				require_once( plugin_dir_path( __FILE__ ).'localisation.php' );
				
				// localisation
				require_once( plugin_dir_path( __FILE__ ).'abn-lookup-for-gravity-forms-fields.php' );

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
				'settings' => '<a href="' . admin_url( 'admin.php?page=gf_settings&subview=itsg_gf_abnlookup_settings' ) . '" title="' . esc_attr( ABR_1 ) . '">' . ABR_SETTINGS . '</a>',
			);

			return array_merge( $action_links, $links );
		}

		/*
         * Ran when plugin is activated
		 * - adds daily cron job to clear ABN Lookup cache
         */
		public static function activation() {
			wp_schedule_event( time(), 'daily', 'itsg_abnlookup_clear_cache_cron' );
		}

		/*
         * Ran when plugin is deactivated
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
			$result  = $wpdb->query( "DELETE FROM ".$table_incomplete." WHERE `option_name` like 'itsg_abnlookup_%'" );
		}

		/*
         * Handles Ajax request for ABN Lookup
		 */
		public static function itsg_gf_abnlookup_check_ajax() {
			// get abn from post request
			$abn = isset( $_POST['abn'] ) ? $_POST['abn'] : null;

			$numbersOnly = preg_replace( "/[^0-9]/","", $abn );

			if ( is_Null($numbersOnly) || '' == $numbersOnly ) {
				$result = array( 'exception' => array ( 'exceptionDescription' => ABR_EMPTY_VALUE ) );
			} else {
				$result = self::do_abnlookup( $numbersOnly );
			}

			die( json_encode( $result ) );
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
				return array( 'exception' => array ( 'exceptionDescription' => ABR_UNCONFIGURED ) );
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

			$url = "https://abr.business.gov.au/ABRXMLSearch/AbrXmlSearch.asmx/ABRSearchByABN?searchString={$abn}&includeHistoricalDetails=N&authenticationGuid={$abnlookup_options['guid']}";

			$result = wp_remote_get( $url );
			$result = simplexml_load_string( $result['body'] )->response;
			$result = json_encode( $result );
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
				'validation_message_not_valid' => ABR_VALIDATION_MESSAGE_NOT_VALID,
				'validation_message_activeabn' => ABR_VALIDATION_MESSAGE_ACTIVEABN,
				'validation_message_reggst' => ABR_VALIDATION_MESSAGE_REGGST,
				'validation_message_notreggst' => ABR_VALIDATION_MESSAGE_NOTREGGST,
				'validation_message_11_char' => ABR_VALIDATION_MESSAGE_11_CHAR,
				'validation_message_loading' => ABR_VALIDATION_MESSAGE_LOADING,
				'validation_message_error_communicating' => ABR_VALIDATION_MESSAGE_ERROR_COMMUNICATING,
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
						__( 'Warning', 'abn-lookup-for-gravity-forms' ),
						sprintf ( ABR_REQUIRE_GF, '<strong>'.ABR_PLUGIN_TITLE.'</strong>' ),
						sprintf ( ABR_UPDATE_GF, '<a target="_blank" href="https://rocketgenius.pxf.io/bakkbone">', '</a>' )
				);
				echo $html;
			} elseif ( '' == $abnlookup_options['guid'] ) {
				$html = sprintf(
					'<div class="error"><h3>%s</h3><p>%s</p><p>%s</p><p>%s</p></div>',
						__( 'Warning', 'abn-lookup-for-gravity-forms' ),
						sprintf ( ABR_REQ_GUID, '<strong>'.ABR_PLUGIN_TITLE.'</strong>' ),
						sprintf ( ABR_GET_GUID, '<a target="_blank" href="http://abr.business.gov.au/webservices.aspx">', '</a>' ),
						sprintf ( ABR_USE_GUID, '<a href="' . admin_url( 'admin.php?page=gf_settings&subview=itsg_gf_abnlookup_settings' ) .'">', '</a>' )
				);
				echo $html;
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