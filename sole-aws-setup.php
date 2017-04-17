<?php
/*
Plugin Name: Dead Simple Wordpress Amazon S3 Backups
Plugin URI:
Description: Simple site backup of your database and uploads directory to an AWS bucket.
Author: Sole Graphics
Author URI: http://www.solegraphics.com/
Version: 0.1
License:
*/

// Amazons S3 SDK library
require_once( 'vendor/autoload.php' );

// Custom controllers for the backup process
require_once( 'core/aws-controller.php' );
require_once( 'core/db-controller.php' );

class Sole_AWS_Backup {

	const SETTINGS_PAGE_SLUG = 'sole-settings-page';
	const SETTINGS_GROUP     = 'sole-settings-group';
	private $plugin_settings = array(
		'Access Key'    => 'sole_aws_access_key',
		'Access Secret' => 'sole_aws_access_secret',
		'Bucket'        => 'sole_aws_bucket',
		'Region'        => 'sole_aws_region',
	);

	function __construct() {
		// Need to add the admin views
		add_action( 'admin_menu', array( $this, 'add_admin_menu') );
		add_action( 'admin_init', array( $this, 'register_plugin_settings') );

		// TODO: add the cron jobs
	}

	public function add_admin_menu() {
		add_menu_page( 'Dead Simple Backup Settings', 'Dead Simple Backup Settings', 'administrator', self::SETTINGS_PAGE_SLUG, array( $this, 'display_settings_page' ), 'dashicons-analytics' );
	}

	public function register_plugin_settings() {
		foreach ( $this->plugin_settings as $setting ) {
			register_setting( self::SETTINGS_GROUP, $setting );
		}
	}

	// Callback function added in `add_admin_menu()`
	public function display_settings_page() {
		include 'templates/settings-form.php';
	}
}

new Sole_AWS_Backup();
