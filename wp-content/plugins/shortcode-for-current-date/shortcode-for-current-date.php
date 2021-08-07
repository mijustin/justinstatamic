<?php

/**
 * Plugin Name: Shortcode For Current Date
 * Plugin URI: http://wordpress.org/plugins/shortcode-for-current-date
 * Description: Insert current Date, Month or Year anywhere with a simple shortcode.
 * Version: 1.2.5
 * Author: Imtiaz Rayhan
 * Author URI: http://imtiazrayhan.com
 * License: GPLv2 or later
 * Text Domain: sfcd
 * @package sfcd_plugin
 * @author Imtiaz Rayhan
 */

// If accessed directly, exit
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( !class_exists( 'Shortcode_For_Current_Date' ) ) {

	/**
	 * Class Shortcode_For_Current_Date. Contains everything.
	 *
	 * @since 1.0
	 */
	class Shortcode_For_Current_Date {

		/**
		 * Shortcode_For_Current_Date constructor.
		 * Adds filters to execute shortcodes in necessary places.
		 *
		 * @since 1.0
		 */
		public function __construct() {

			add_shortcode( 'current_date', array( $this, 'sfcd_date_shortcode' ) );

		}

		/**
		 * Month Shortcode build.
		 *
		 * @since 1.0
		 */
		public function sfcd_date_shortcode( $atts ) {

			/**
			 * Shortcode attributes.
			 *
			 * @since 1.0
			 */
			$atts = shortcode_atts(
				array(
					'format' => ''
				), $atts
			);

			if ( !empty( $atts['format'] ) ) {
				$dateFormat = $atts['format'];
			} else {
				$dateFormat = 'jS F Y';
			}

			/* Changed [return date( $dateFormat );]
			 * to the following line in order to retrieve
			 * WP time as opposed to server time.
			 *
			 * @author UN_Rick
			 */
			if ( $dateFormat == 'z' ) {
				return current_time( $dateFormat ) + 1;
			} else {
				return current_time( $dateFormat );
			}

		}

	}

}

new Shortcode_For_Current_Date();

require_once plugin_dir_path( __FILE__ )  . '/includes/sfcd-welcome-page.php';
require_once plugin_dir_path( __FILE__ )  . '/includes/sfcd-menu-page.php';

SFCD_Welcome_Page::init();
SFCD_Menu_Page::init();

register_activation_hook( __FILE__, array( 'SFCD_Welcome_Page', 'sfcd_welcome_activate' ) );
register_deactivation_hook( __FILE__, array( 'SFCD_Welcome_Page', 'sfcd_welcome_deactivate' ) );

add_filter( 'the_title', 'do_shortcode' );