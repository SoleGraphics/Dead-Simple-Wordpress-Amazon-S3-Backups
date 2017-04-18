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
	// Plugin Options to register & display
	private $plugin_settings = array(
		'Access Key'    => array(
			'slug' => 'sole_aws_access_key',
		),
		'Access Secret' => array(
			'slug' =>'sole_aws_access_secret',
		),
		'Bucket'        => array(
			'slug' => 'sole_aws_bucket',
		),
		'Region'        => array(
			'slug'        => 'sole_aws_region',
			'instruction' => 'To find your region check <a href="http://docs.aws.amazon.com/general/latest/gr/rande.html#s3_region" target="__blank">Amazon\'s documentation',
		),
		'Database Backup Frequency' => array(
			'slug'    => 'sole_aws_db_backup_frequency',
			'options' => array(
				'daily', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday',
			)
		),
		'Database Backup Time' => array(
			'slug' => 'sole_aws_db_backup_timestamp',
			'instruction' => 'Enter time in a 24 hour "HH:MM" format',
		),
		'Uploads Backup Frequency' => array(
			'slug'    => 'sole_aws_db_uploads_frequency',
			'options' => array(
				'daily', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday',
			)
		),
		'Uploads Backup Time' => array(
			'slug' => 'sole_aws_uploads_backup_timestamp',
			'instruction' => 'Enter time in a 24 hour "HH:MM" format',
		)
	);

	function __construct() {
		// Need to add the admin views
		add_action( 'admin_menu', array( $this, 'add_admin_menu') );
		add_action( 'admin_init', array( $this, 'register_plugin_settings') );

		// Need to check that the timestamps are valid times
		add_filter( 'pre_update_option_sole_aws_db_backup_timestamp', array( $this,'check_if_is_valid_timestamp' ), 10, 2 );
		add_filter( 'pre_update_option_sole_aws_uploads_backup_timestamp', array( $this,'check_if_is_valid_timestamp' ), 10, 2 );

		// TODO: add the cron jobs

		// Check if user wants to manually backup the DB & uploads
		if( isset( $_POST['manual-sole-backup-trigger'] ) ) {

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

	// Need to register all the settings
	public function register_plugin_settings() {
		// Settings defined at top of class
		foreach ( $this->plugin_settings as $setting ) {
			register_setting( self::SETTINGS_GROUP, $setting['slug'] );
		}
	}

	// Callback function added in `add_admin_menu()`
	public function display_settings_page() {
		include 'templates/settings-form.php';
	}

	public function display_logs() {
		include 'templates/log-file.php';
	}

	// Check if a given value is a timestamp or not.
	// If not, return the old value.
	public function check_if_is_valid_timestamp( $new, $old ) {
		if( preg_match("/^(2[0-3]|[01][0-9]):([0-5][0-9])$/", $new ) ) {
			return $new;
		}
		return $old;
	}
}

new Sole_AWS_Backup();
