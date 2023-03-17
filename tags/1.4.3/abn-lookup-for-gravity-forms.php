<?php
/*
Plugin Name: ABN Lookup for Gravity Forms
Description: Connect the Australian Government ABN Lookup tool to Gravity Forms.
Version: 1.4.3
Author: Adrian Gordon
Author URI: http://www.itsupportguides.com 
License: GPL2
Text Domain: abn-lookup-for-gravity-forms

------------------------------------------------------------------------
Copyright 2016 Adrian Gordon

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
*/

if ( ! defined(  'ABSPATH' ) ) {
	die();
}

load_plugin_textdomain( 'abn-lookup-for-gravity-forms', false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );

add_action( 'admin_notices', array( 'ITSG_GF_AbnLookup', 'admin_warnings' ), 20);

register_activation_hook( __FILE__, array( 'ITSG_GF_AbnLookup', 'activation' ) );

/* 
 *   Setup the main plugin class
 */
if ( !class_exists( 'ITSG_GF_AbnLookup' ) ) {
	class ITSG_GF_AbnLookup {
	
		private static $name = 'ABN Lookup for Gravity Forms';
		private static $slug = 'itsg_gf_abnlookup';
		
		/*
         * Construct the plugin object
         */
		function __construct() {
			
			// register plugin functions through 'gform_loaded' - 
			// this delays the registration until Gravity Forms has loaded, ensuring it does not run before Gravity Forms is available.
            add_action( 'gform_loaded', array( &$this, 'register_actions' ) );

		} // END __construct
		
		/*
         * Register plugin functions
         */
		function register_actions() {
            if ( self::is_gravityforms_installed() ) {
				// start the plugin

				register_deactivation_hook(__FILE__, array( &$this, 'deactivation' ) );
				
				add_action( 'itsg_abnlookup_clear_cache_cron', array( &$this, 'clear_database_cache' ) );
				
				//  functions for fields
				require_once( plugin_dir_path( __FILE__ ).'abn-lookup-for-gravity-forms-fields.php' );
				
				// addon framework
				require_once( plugin_dir_path( __FILE__ ).'abn-lookup-for-gravity-forms-addon.php' );

				// ajax hook for users that are logged in
				add_action( 'wp_ajax_itsg_gf_abnlookup_check_ajax', array( &$this, 'itsg_gf_abnlookup_check_ajax' ) );
				
				// ajax hook for users that are not logged in
				add_action( 'wp_ajax_nopriv_itsg_gf_abnlookup_check_ajax', array( &$this, 'itsg_gf_abnlookup_check_ajax' ) );
				
				// plugin 'settings' link on wp-admin installed plugins page
				add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array( &$this, 'plugin_action_links') );
				
			}
		} // END register_actions
		
		/*
         * Add 'Settings' link to plugin in WordPress installed plugins page
         */
		function plugin_action_links( $links ) {
			$action_links = array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=gf_settings&subview=itsg_gf_abnlookup_settings' ) . '" title="' . esc_attr( __( 'View ABN Lookup Settings', 'abn-lookup-for-gravity-forms' ) ) . '">' . __( 'Settings', 'abn-lookup-for-gravity-forms' ) . '</a>',
			);

			return array_merge( $action_links, $links );
		} // END plugin_action_links
		
		/*
         * Ran when plugin is activated
		 * - adds daily cron job to clear ABN Lookup cache
         */
		public static function activation() {
			wp_schedule_event( time(), 'daily', 'itsg_abnlookup_clear_cache_cron' );
		} // END activation

		/*
         * Ran when plugin is deactivated
		 * - clear ABN Lookup cache
		 * - delete daily cron job that clears ABN Lookup cache
		 */
		public function deactivation() {
			self::clear_database_cache();
			wp_clear_scheduled_hook( 'itsg_abnlookup_clear_cache_cron' );
		} // END deactivation

		/*
         * Clears ABN Lookup cache
		 * - triggered through daily cron job and when plugin is deactivated
		 */
		public function clear_database_cache() {
			global $wpdb;
			$table_incomplete = $wpdb->prefix . "options";
			$result  = $wpdb->query( "DELETE FROM ".$table_incomplete." WHERE `option_name` like 'itsg_abnlookup_%'" );
		} // END clear_database_cache
		
		/*
         * Handles Ajax request for ABN Lookup
		 */
		public static function itsg_gf_abnlookup_check_ajax() {
			// get abn from post request
			$abn = isset( $_POST['abn'] ) ? $_POST['abn'] : null;
			
			$numbersOnly = preg_replace( "/[^0-9]/","", $abn );
			
			if ( is_Null($numbersOnly) || '' == $numbersOnly ) {
				$result = array( 'exception' => array ( 'exceptionDescription' => 'Empty ABN value passed.' ) );
			} else {
				$result = self::do_abnlookup( $numbersOnly );
			}

			die( json_encode( $result ) ); 
		} // END itsg_gf_abnlookup_check_ajax
		
		/*
		 * Handles ABN Lookup
		 * - first checks cache
		 * - if not in cache, checks ABN against the ABR
		 * - saves results to cache
		 * - returns results
		 */
		public static function do_abnlookup( $abn ) {
			if ( empty( $abn )  ) {
				return;
			}

			$abnlookup_options = self::get_options();
			if ( '' == $abnlookup_options['guid'] ) {
				return array( 'exception' => array ( 'exceptionDescription' => 'ABN Lookup for Gravity Forms has not been configured. The GUID necessary to communicate with the Australia Business Register has not been specified.' ) );
			}

			/** supply from cache **/
			$result_cache = get_option( "itsg_abnlookup_{$abn}", 0 );
			if( $result_cache ){
				$cache_datetime = strtotime( $result_cache->dateRegisterLastUpdated );
				$current_datetime =  strtotime( 'now' );
				if ( ( $current_datetime - $cache_datetime ) < 86400 ) {  //86400 is a day of seconds
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
			update_option( "itsg_abnlookup_{$abn}", $result );
			/** end cache **/
			return $result; 
		} // END do_abnlookup
		
		/* 
		 * Function for enqueuing all the required scripts
		 */
		public static function enqueue_scripts( $form, $is_ajax ) {
			if ( is_array( $form['fields'] ) || is_object( $form['fields'] ) ) {
				// get Ajax Upload options
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
		} // END enqueue_scripts		
		
		/* 
		 *   Handles the plugin options.
		 *   Default values are stored in an array.
		 */ 
		public static function get_options(){
			$defaults = array(
				'guid' => '',
				'includecss' => true,
				'validation_message_not_valid' => 'The ABN provided is not valid. Check the number entered and try again, or use the <a href="https://abr.business.gov.au/" target="_blank">ABN Lookup website</a> to confirm for your ABN.',
				'validation_message_activeabn' => 'The ABN provided is not active. Entities that do not have an active ABN cannot complete this form.',
				'validation_message_reggst' => 'The ABN provided is not registered for GST. Entities that are not registered for GST cannot complete this form.',
				'validation_message_notreggst' => 'The ABN provided is registered for GST. Entities that are registered for GST cannot complete this form.',
				'validation_message_11_char' => "The information entered does not match a valid ABN. ABN's need to be 11 digits.",
				'validation_message_loading' => 'Checking ABN with the Australian Business Register.',
				'validation_message_error_communicating' => 'Error comminicating with the Australian Business Register.'
			);
			$options = wp_parse_args( get_option( 'gravityformsaddon_itsg_gf_abnlookup_settings_settings' ), $defaults );
			return $options;
		} // END get_options
		
		/*
         * Warning message if Gravity Forms is installed and enabled
         */
		public static function admin_warnings() {
			$abnlookup_options = self::get_options();
			if ( !self::is_gravityforms_installed() ) {				
				$html = sprintf(
					'<div class="error"><h3>%s</h3><p>%s</p><p>%s</p></div>',
						__( 'Warning', 'abn-lookup-for-gravity-forms' ),
						sprintf ( __( 'The plugin %s requires Gravity Forms to be installed.', 'abn-lookup-for-gravity-forms' ), '<strong>'.self::$name.'</strong>' ),
						sprintf ( __( 'Please %sdownload the latest version%s of Gravity Forms and try again.', 'abn-lookup-for-gravity-forms' ), '<a target="_blank" href="https://www.e-junkie.com/ecom/gb.php?cl=54585&c=ib&aff=299380">', '</a>' )
				);
				echo $html;
			} elseif ( '' == $abnlookup_options['guid'] ) {
				$html = sprintf(
					'<div class="error"><h3>%s</h3><p>%s</p><p>%s</p><p>%s</p></div>',
						__( 'Warning', 'abn-lookup-for-gravity-forms' ),
						sprintf ( __( 'The plugin %s requires a GUID to communicate with the Australian Business Register.', 'abn-lookup-for-gravity-forms' ), '<strong>'.self::$name.'</strong>' ),
						sprintf ( __( 'To receive a GUID see %sweb services registration%s on the Australian Business Register website.', 'abn-lookup-for-gravity-forms' ), '<a target="_blank" href="http://abr.business.gov.au/webservices.aspx">', '</a>' ),
						sprintf ( __( 'Once you have a GUID you will need to enter it in the %sABN Lookup for Gravity Forms Settings%s page.', 'abn-lookup-for-gravity-forms' ), '<a href="' . admin_url( 'admin.php?page=gf_settings&subview=itsg_gf_abnlookup_settings' ) .'">', '</a>' )
				);
				echo $html;
			}
		} // END admin_warnings
		
		/*
         * Check if GF is installed
         */
        private static function is_gravityforms_installed() {
			if ( !function_exists( 'is_plugin_active' ) || !function_exists(  'is_plugin_active_for_network' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}
			if ( is_multisite() ) {
				return (is_plugin_active_for_network( 'gravityforms/gravityforms.php' ) || is_plugin_active( 'gravityforms/gravityforms.php' ) );
			} else {
				return is_plugin_active( 'gravityforms/gravityforms.php' );
			}
        } // END is_gravityforms_installed
	}
}
$ITSG_GF_AbnLookup = new ITSG_GF_AbnLookup();