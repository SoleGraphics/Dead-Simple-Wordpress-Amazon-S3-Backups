<?php
/*
	Plugin Name: Dead Simple Wordpress Amazon S3 Backups
	Plugin URI: https://github.com/SoleGraphics/Dead-Simple-Wordpress-Amazon-S3-Backups
	Description: Simple site backup of your database and uploads directory to an AWS bucket.
	Author: Sole Graphics
	Author URI: http://www.solegraphics.com/
	Version: 0.2.1
	License: MIT
*/

// Amazons S3 SDK library
require_once( 'vendor/autoload.php' );

// Custom controllers for the backup process
require_once( 'core/sole-logger.php' );
require_once( 'core/admin-controller.php' );
require_once( 'core/aws-controller.php' );
require_once( 'core/backup-controller.php' );
require_once( 'core/schedule-controller.php' );

class Sole_AWS_Backup {

	const SETTINGS_GROUP = 'sole-settings-group';

	private $plugin_settings;

	function __construct() {
		// Load the settings
		if( ! file_exists( __DIR__ . '/plugin-settings.ini' ) ) {
			throw new Exception( 'No plugin settings file found!', 1 );
		}
		$this->plugin_settings = parse_ini_file( 'plugin-settings.ini', true );

		// Create controllers
		$this->backup_controller   = new Sole_AWS_Backup_Controller();
		$this->admin_controller    = new Sole_Admin_Controller(
			$this->plugin_settings,
			self::SETTINGS_GROUP );
		$this->schedule_controller = new Sole_Schedule_Controller( $this->backup_controller );
		$this->logger              = Sole_AWS_Logger::get_instance();
	}

	public function init() {
		// Controller setup
		$this->admin_controller->init();
		$this->schedule_controller->init();

		// Setup the tables
		register_activation_hook( __FILE__, array( $this->logger, 'build_database' ) );

		// Setup the plugin options
		add_action( 'admin_init', array( $this, 'register_plugin_settings') );

		// Check if user wants to manually backup the DB & uploads
		// Only allow this for admins
		if( isset( $_POST['manual-sole-backup-trigger'] ) &&
			is_admin() ) {
			$this->backup_controller->backup_database();
			$this->backup_controller->backup_uploads_dir();
		}

		// Need to remove CRON jobs on deactivation
		register_deactivation_hook( __FILE__, array( $this->schedule_controller, 'clear_plugin_info' ) );
		// Need to remove our custom table from the DB
		register_uninstall_hook( __FILE__, array( 'Sole_AWS_Logger', 'destroy_table' ) );
	}

	// Need to register all the settings
	public function register_plugin_settings() {
		foreach ( $this->plugin_settings as $setting ) {
			register_setting( self::SETTINGS_GROUP, $setting['slug'] );
		}
	}
}

$sole_aws_backup = new Sole_AWS_Backup();
$sole_aws_backup->init();
