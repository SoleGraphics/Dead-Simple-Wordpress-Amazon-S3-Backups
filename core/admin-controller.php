<?php

class Sole_Admin_Controller {

	const SETTINGS_PAGE_SLUG = 'sole-settings-page';

	private $plugin_settings;
	private $settings_group;
	private $table_controller;

	// setup the settings array
	public function __construct( $plugin_settings, $settings_group ) {
		$this->plugin_settings = $plugin_settings;
		$this->settings_group = $settings_group;
		$this->table_controller = Sole_AWS_Logger::get_instance();
	}

	// setup all WP hooks/actions
	public function init() {
		// Need to add the admin views. Should be network settings if we're in a multisite.
		if( is_multisite() ) {
			add_action( 'network_admin_menu', array( $this, 'add_admin_menu') );
		} else {
			add_action( 'admin_menu', array( $this, 'add_admin_menu') );
		}
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
		// Check if a page is set
		$page           = isset( $_GET['page_to_display'] ) ? $_GET['page_to_display']: 1;
		$page 			= max( $page, 1 );
		$type			= isset( $_GET['msg_type'] ) ? $_GET['msg_type'] : 'error';

		// Get sender information
		$sender  = isset( $_GET['sender'] ) ? $_GET['sender'] : '';
		$senders = $this->table_controller->get_log_senders();
		$senders = $this->table_controller->simplify_array( $senders, 'log_sender' );

		// Log results to display to the user
		$logs = $this->table_controller->get_log_messages( $page, $type, $sender );

		// Get the number of pages
		$total_pages = ceil( $this->table_controller->get_max_number_results() / $this->table_controller->num_to_display );
		include plugin_dir_path( __DIR__ ) . 'templates/error-logs.php';
	}

}
