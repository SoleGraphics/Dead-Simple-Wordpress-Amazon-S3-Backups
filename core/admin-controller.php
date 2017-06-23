<?php

class Sole_Admin_Controller {

	const SETTINGS_PAGE_SLUG = 'sole-settings-page';

	private $plugin_settings;
	private $settings_group;

	// setup the settings array
	public function __construct( $plugin_settings, $settings_group ) {
		$this->plugin_settings = $plugin_settings;
		$this->settings_group = $settings_group;
	}

	// setup all WP hooks/actions
	public function init() {
		// Need to add the admin views
		add_action( 'admin_menu', array( $this, 'add_admin_menu') );
	}

	// Setup the menu in the admin panel
	public function add_admin_menu() {
		add_menu_page( 'Dead Simple Backup', 'Dead Simple Backup', 'administrator', self::SETTINGS_PAGE_SLUG, '', 'dashicons-analytics' );

		// Register submenu for plugin settings - default page for the plugin
		add_submenu_page( self::SETTINGS_PAGE_SLUG, 'Dead Simple Backup Settings', 'Settings', 'administrator', self::SETTINGS_PAGE_SLUG, array( $this, 'display_settings_page' ) );

		// Register submenu for log page
		add_submenu_page( self::SETTINGS_PAGE_SLUG, 'Dead Simple Backup Logs', 'Logs', 'administrator', self::SETTINGS_PAGE_SLUG . '-logs', array( $this, 'display_logs' ) );
	}

	public function display_settings_page() {
		include plugin_dir_path( __DIR__ ) . 'templates/settings-form.php';
	}

	public function display_logs() {
		include plugin_dir_path( __DIR__ ) . 'templates/log-file.php';
	}

}
