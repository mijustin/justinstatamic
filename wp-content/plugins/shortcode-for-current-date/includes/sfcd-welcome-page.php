<?php

// If accessed directly, exit
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SFCD_Welcome_Page {

	public static function init() {

		add_action( 'admin_menu', array( __CLASS__, 'sfcd_welcome_menu_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'sfcd_safe_welcome_redirect' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'sfcd_styles' ) );
		add_action( 'admin_head', array( __CLASS__, 'remove_menu_entry' ) );

	}

	public static function sfcd_welcome_activate() {

		set_transient('_sfcd_redirect_welcome', true, 30 );

	}

	public static function sfcd_welcome_deactivate() {

		delete_transient( '_sfcd_redirect_welcome' );

	}

	public static function sfcd_safe_welcome_redirect() {

		// Bail if no activation redirect transient is present.
		if ( ! get_transient( '_sfcd_redirect_welcome' ) ) {
			return;
		}

		// Delete the redirect transient.
		delete_transient( '_sfcd_redirect_welcome' );

		// Bail if activating from network or bulk sites.
		if ( is_network_admin() || isset( $_GET['activate-multi'] ) ) {
			return;
		}

		// Redirect to Welcome Page.
		wp_safe_redirect( add_query_arg( array( 'page' => 'sfcd_welcome_menu_page' ), admin_url( 'index.php' ) ) );

	}

	public static function sfcd_welcome_menu_page() {

		global $sfcd_sub_menu;

		$sfcd_sub_menu = add_submenu_page(
			'index.php', // The slug name for the parent menu (or the file name of a standard WordPress admin page).
			__( 'Shortcode for Current Date', 'sfcd' ), // The text to be displayed in the title tags of the page when the menu is selected.
			__( 'Shortcode for Current Date', 'sfcd' ), // The text to be used for the menu.
			'read', // The capability required for this menu to be displayed to the user.
			'sfcd_welcome_menu_page', // The slug name to refer to this menu by (should be unique for this menu).
			array( __CLASS__, 'sfcd_welcome_page_content' ) // The function to be called to output the content for this page.
		);

	}

	public static function sfcd_welcome_page_content() {

		require_once( dirname( plugin_dir_path( __FILE__ ) ) . '/includes/sfcd-welcome-page-content.php' );

	}

	public static function sfcd_styles( $hook ) {

		global $sfcd_sub_menu;

		// Add style to the welcome page only.
		if ( $hook != $sfcd_sub_menu ) {
			return;
		}
		// Welcome page styles.
		wp_enqueue_style(
			'sfcd_welcome_style',
			dirname( plugin_dir_url( __FILE__ ) )  . '/assets/admin/css/welcome.css',
			array(),
			'',
			'all'
		);
	}

	static function remove_menu_entry() {

		remove_submenu_page( 'index.php', 'sfcd_welcome_menu_page' );

	}

}