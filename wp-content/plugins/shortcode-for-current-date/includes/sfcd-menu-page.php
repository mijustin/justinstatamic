<?php

// If accessed directly, exit
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SFCD_Menu_Page {

	public static function init() {

		add_action( 'admin_menu', array( __CLASS__, 'sfcd_add_menu_page' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'sfcd_styles' ) );

	}

	public static function sfcd_add_menu_page() {

		global $sfcd_sub_menu_page;

		$sfcd_sub_menu_page = add_submenu_page(
			'options-general.php', // The slug name for the parent menu (or the file name of a standard WordPress admin page).
			__( 'Shortcode for Current Date', 'sfcd' ), // The text to be displayed in the title tags of the page when the menu is selected.
			__( 'Shortcode for Current Date', 'sfcd' ), // The text to be used for the menu.
			'read', // The capability required for this menu to be displayed to the user.
			'sfcd_menu_page', // The slug name to refer to this menu by (should be unique for this menu).
			array( __CLASS__, 'sfcd_menu_page_content' ) // The function to be called to output the content for this page.
		);

	}

	public static function sfcd_menu_page_content() {

		require_once( dirname( plugin_dir_path( __FILE__ ) ) . '/includes/sfcd-menu-page-content.php' );

	}

	public static function sfcd_styles( $hook ) {

		global $sfcd_sub_menu_page;

		// Add style to the welcome page only.
		if ( $hook != $sfcd_sub_menu_page ) {
			return;
		}
		// Welcome page styles.
		wp_enqueue_style(
			'sfcd_menu_style',
			dirname( plugin_dir_url( __FILE__ ) )  . '/assets/admin/css/welcome.css',
			array(),
			'',
			'all'
		);
		wp_enqueue_style(
			'sfcd_bootstrap_style',
			dirname( plugin_dir_url( __FILE__ ) )  . '/assets/admin/css/bootstrap.min.css',
			array(),
			'',
			'all'
		);

	}

}