<?php
/*
Plugin Name: ABN Lookup for Gravity Forms
Description: Connect the Australian Government ABN Lookup tool to Gravity Forms.
Version: 1.0.2
Author: Adrian Gordon
Author URI: http://www.itsupportguides.com 
License: GPL2
Text Domain: itsg_gf_abnlookup

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

add_action( 'admin_notices', array( 'ITSG_GF_AbnLookup', 'admin_warnings' ), 20);

register_activation_hook(__FILE__, array( 'ITSG_GF_AbnLookup', 'activation' ));

/* 
 *   Setup the main plugin class
 */
if (!class_exists( 'ITSG_GF_AbnLookup' )) {
	class ITSG_GF_AbnLookup {
	
		private static $name = 'ABN Lookup for Gravity Forms';
		private static $slug = 'itsg_gf_abnlookup';
		
		/*
         * Construct the plugin object
         */
		function __construct() {
			
			// register plugin functions through 'plugins_loaded' - 
			// this delays the registration until all plugins have been loaded, ensuring it does not run before Gravity Forms is available.
            add_action(  'plugins_loaded', array(&$this, 'register_actions' ) );

		} // END __construct
		
		/*
         * Register plugin functions
         */
		function register_actions() {
            if ((self::is_gravityforms_installed())) {
				// start the plugin

				register_deactivation_hook(__FILE__, array(&$this, 'deactivation' ));
				
				add_action( 'itsg_abnlookup_clear_cache_cron', array(&$this, 'clear_database_cache' ));
				
				//  functions for fields
				require_once(plugin_dir_path( __FILE__ ).'abn-lookup-for-gravity-forms-fields.php' );

				//  the abnlookup.class.php as provided by the Australian Business Reguster
				require_once(plugin_dir_path( __FILE__ ).'abnlookup.class.php' );

				// ajax hook for users that are logged in
				add_action( 'wp_ajax_itsg_gf_abnlookup_check_ajax', array(&$this, 'itsg_gf_abnlookup_check_ajax' ));
				
				// ajax hook for users that are not logged in
				add_action( 'wp_ajax_nopriv_itsg_gf_abnlookup_check_ajax', array(&$this, 'itsg_gf_abnlookup_check_ajax' ));
				
				// plugin 'settings' link on wp-admin installed plugins page
				add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), array(&$this, 'plugin_action_links') );
				
			}
		} // END register_actions
		
		/*
         * Add 'Settings' link to plugin in WordPress installed plugins page
         */
		function plugin_action_links( $links ) {
			$action_links = array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=gf_settings&subview=itsg_gf_abnlookup_settings' ) . '" title="' . esc_attr( __( 'View ABN Lookup Settings', 'itsg_gf_abnlookup' ) ) . '">' . __( 'Settings', 'itsg_gf_abnlookup' ) . '</a>',
			);

			return array_merge( $action_links, $links );
		} // END plugin_action_links
		
		/*
         * Ran when plugin is activated
		 * - adds daily cron job to clear ABN Lookup cache
         */
		public function activation() {
			wp_schedule_event(time(), 'daily', 'itsg_abnlookup_clear_cache_cron' );
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
			$abn = isset($_POST['abn']) ? $_POST['abn'] : null;
			
			$numbersOnly = preg_replace( "/[^0-9]/","",$abn);
			
			if ( is_Null($numbersOnly) || '' == $numbersOnly ) {
				$result = array( 'exception' => array ( 'exceptionDescription' => 'Empty ABN value passed.' ) );
			} else {
				$result = self::do_abnlookup($numbersOnly);; 
			}

			die(json_encode( $result )); 
		} // END itsg_gf_abnlookup_check_ajax
		
		/*
		 * Handles ABN Lookup
		 * - first checks cache
		 * - if not in cache, checks ABN against the ABR
		 * - saves results to cache
		 * - returns results
		 */
		public static function do_abnlookup($abn) {
			$abnlookup_options = self::get_options();
			if ( '' == $abnlookup_options['guid'] ) {
				return array( 'exception' => array ( 'exceptionDescription' => 'ABN Lookup for Gravity Forms has not been configured. The GUID necessary to communicate with the Australia Business Register has not been specified.' ) );
			}

			/** supply from cache **/
			$result_cache = get_option( "itsg_abnlookup_{$abn}", 0 );
			if($result_cache){
				$cache_datetime = strtotime($result_cache->dateRegisterLastUpdated);
				$current_datetime =  strtotime( 'now' );
				if (($current_datetime - $cache_datetime) < 86400) {  //86400 is a day of seconds
					return $result_cache; 
				}
			}
			/** cache end **/
			$abnlookup = new abnlookup($abnlookup_options['guid']);
			$result = $abnlookup->searchByAbn($abn)->ABRPayloadSearchResults->response; 
			/** save the cache **/
			update_option( "itsg_abnlookup_{$abn}",$result);
			/** end cache **/
			return $result; 
		} // END do_abnlookup
		
		/* 
		 * Function for enqueuing all the required scripts
		 */
		public static function enqueue_scripts($form, $is_ajax) {
			if ( is_array($form['fields']) || is_object($form['fields']) ) {
				// get Ajax Upload options
				$abnlookup_options = self::get_options();
				if ( is_array($form['fields']) || is_object($form['fields']) ) {
					foreach ( $form['fields'] as $field ) {
						if ( ITSG_GF_AbnLookup_Fields::is_abnlookup_field($field) ) {						
							if ($abnlookup_options['includecss'] == true) {
								wp_enqueue_style(  'itsg_gfau_css', plugins_url(  'css/abnlookup.css', __FILE__ ) );
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
				'validation_message_not_valid' => 'The ABN provided is not valid. Check the number entered and try again.',
				'validation_message_activeabn' => 'The ABN provided is not active. Entities that do not have an active ABN cannot complete this form.',
				'validation_message_reggst' => 'The ABN provided is not registered for GST. Entities that are not registered for GST cannot complete this form.',
				'validation_message_notreggst' => 'The ABN provided is registered for GST. Entities that are registered for GST cannot complete this form.',
				'validation_message_11_char' => "The information entered does not match a valid ABN. ABN's need to be 11 numbers.",
				'validation_message_loading' => 'Checking ABN with the Australian Business Register.'
			);
			$options = wp_parse_args(get_option( 'gravityformsaddon_itsg_gf_abnlookup_settings_settings' ), $defaults);
			return $options;
		} // END get_options
		
		/*
         * Warning message if Gravity Forms is installed and enabled
         */
		public static function admin_warnings() {
			$abnlookup_options = self::get_options();
			$abnlookup_settings_url = '<a href="' . admin_url( 'admin.php?page=gf_settings&subview=itsg_gf_abnlookup_settings' ) . '" title="' . esc_attr( __( 'View ABN Lookup for Gravity Forms  Settings', self::$slug ) ) . '">' . __( 'ABN Lookup for Gravity Forms Settings', self::$slug ) . '</a>';
			if ( !self::is_gravityforms_installed() ) {
				?>
				<div class="error">
					<h3><?php _e( 'Warning', self::$slug); ?></h3>
					<p>
						<?php _e( 'The plugin ', self::$slug); ?><strong><?php echo self::$name; ?></strong> <?php _e( 'requires Gravity Forms to be installed.', self::$slug) ?><br />
						<?php _e( 'Please ',self::$slug); ?><a target="_blank" href="https://www.e-junkie.com/ecom/gb.php?cl=54585&c=ib&aff=299380"><?php _e( ' download the latest version',self::$slug); ?></a><?php _e( ' of Gravity Forms and try again.',self::$slug) ?>
					</p>
				</div>
				<?php
			} elseif ( '' == $abnlookup_options['guid'] ) {
				?>
				<div class="error">
					<h3><?php _e( 'Warning', self::$slug); ?></h3>
					<p>
						<?php _e( 'The plugin ', self::$slug); ?><strong><?php echo self::$name; ?></strong> <?php _e( 'requires a GUID to communicate with the Australian Business Register.', self::$slug) ?><br />
						<?php _e( 'To receive a GUID see <a target="_blank" href="http://abr.business.gov.au/webservices.aspx">web services registration</a> on the Australian Business Register website.',self::$slug); ?></br>
						<?php _e( 'Once you have a GUID you will need to enter it in the ',self::$slug); 
						echo $abnlookup_settings_url;
						?>
					</p>
				</div>
				<?php
			}
		} // END admin_warnings
		
		/*
         * Check if GF is installed
         */
        private static function is_gravityforms_installed() {
			if ( !function_exists(  'is_plugin_active' ) || !function_exists(  'is_plugin_active_for_network' ) ) {
				require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
			}
			if (is_multisite()) {
				return (is_plugin_active_for_network( 'gravityforms/gravityforms.php' ) || is_plugin_active( 'gravityforms/gravityforms.php' ) );
			} else {
				return is_plugin_active( 'gravityforms/gravityforms.php' );
			}
        } // END is_gravityforms_installed
	}
}
$ITSG_GF_AbnLookup = new ITSG_GF_AbnLookup();

/* 
 *   Setup the settings page for configuring the options
 */
if (class_exists( "GFForms" )) {
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
			add_filter( "gform_submit_button", array($this, "form_submit_button" ), 10, 2);
        } // END init
		
		// Add the text in the plugin settings to the bottom of the form if enabled for this form
		function form_submit_button($button, $form){
			$settings = $this->get_form_settings($form);
			if(isset($settings["enabled"]) && true == $settings["enabled"]){
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
                    "title"  => __( 'Settings', 'itsg_gf_abnlookup' ),
                    "fields" => array(
                        array(
                            "label"   => __( 'GUID', 'itsg_gf_abnlookup' ),
							"name"    => "guid",
                            "tooltip" => __( 'To receive a GUID see <a target="_blank" href="http://abr.business.gov.au/webservices.aspx">web services registration</a> on the Australian Business Register website.', 'itsg_gf_abnlookup' ),
                            "type"    => "guid"
                        ),
						array(
                            "label"   => __( 'Include CSS styles', 'itsg_gf_abnlookup' ),
                            "type"    => "checkbox",
                            "name"    => "includecss",
                            "tooltip" => __( 'This option allows you to control whether to use the CSS styles provided in the plugin. If this is not enabled you will need to apply styles through your theme.', 'itsg_gf_abnlookup' ),
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
                    "title"  => __( 'Validation messages', 'itsg_gf_abnlookup' ),
                    "fields" => array(
                        array(
                            "label"   => __( 'ABN not valid', 'itsg_gf_abnlookup' ),
							"name"    => "validation_message_not_valid",
                            "tooltip" => __( 'This message is displayed to the user if they enter a ABN that is not valid.', 'itsg_gf_abnlookup' ),
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_not_valid']
                        ),
						array(
                            "label"   => __( 'ABN not active', 'itsg_gf_abnlookup' ),
							"name"    => "validation_message_activeabn",
                            "tooltip" => __( 'This message is displayed to the user if they enter a ABN is not active.', 'itsg_gf_abnlookup' ),
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_activeabn']
                        ),
						array(
                            "label"   => __( 'ABN not registered for GST', 'itsg_gf_abnlookup' ),
							"name"    => "validation_message_reggst",
                            "tooltip" => __( 'This message is displayed to the user if they enter a ABN that is not registered for GST and the field validation is set to only allow ABNs that are GST registered.', 'itsg_gf_abnlookup' ),
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_reggst']
                        ),
						array(
                            "label"   => __( 'ABN registered for GST', 'itsg_gf_abnlookup' ),
							"name"    => "validation_message_notreggst",
                            "tooltip" => __( 'This message is displayed to the user if they enter a ABN is registered for GST and the field validation is set to only allow ABNs that are not registered for GST.', 'itsg_gf_abnlookup' ),
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_notreggst']
                        ),
						array(
                            "label"   => __( 'ABN not correct length', 'itsg_gf_abnlookup' ),
							"name"    => "validation_message_11_char",
                            "tooltip" => __( 'This message is displayed to the user if they enter a value into the ABN field that does not contain the required 11 characters that make up an ABN.', 'itsg_gf_abnlookup' ),
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_11_char']
                        ),
						array(
                            "label"   => __( 'Loading message', 'itsg_gf_abnlookup' ),
							"name"    => "validation_message_loading",
                            "tooltip" => __( 'This message is displayed to the user when the ABN Lookup is running.', 'itsg_gf_abnlookup' ),
                            "type"    => "textarea",
                            "class"   => "medium",
							"default_value" => $abnlookup_options['validation_message_loading']
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
				?>
            <div>
                <p><?php echo __( 'To receive a GUID see <a target="_blank" href="http://abr.business.gov.au/webservices.aspx">web services registration</a> on the Australian Business Register website.', 'itsg_gf_abnlookup' ) ?></p>
            </div>
            <?php
        } // END settings_guid
		
	
    }
    new ITSG_GF_AbnLookup_Settings();
}