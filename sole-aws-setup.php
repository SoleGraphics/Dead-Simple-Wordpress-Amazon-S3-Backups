<?php
/*
	Plugin Name: Dead Simple Wordpress Amazon S3 Backups
	Plugin URI: https://github.com/SoleGraphics/Dead-Simple-Wordpress-Amazon-S3-Backups
	Description: Simple site backup of your database and uploads directory to an AWS bucket.
	Author: Sole Graphics
	Author URI: http://www.solegraphics.com/
	Version: 0.2
	License:
*/

/*
	TODO: Use a CRON controller for scheduling related tasks
*/

// Amazons S3 SDK library
require_once( 'vendor/autoload.php' );

// Custom controllers for the backup process
require_once( 'core/sole-logger.php' );
require_once( 'core/admin-controller.php' );
require_once( 'core/aws-controller.php' );
require_once( 'core/backup-controller.php' );

class Sole_AWS_Backup {

	const SETTINGS_GROUP        = 'sole-settings-group';
	const DB_BACKUP_EVENT       = 'sole_db_event_hook';
	const UPLOADS_BACKUP_EVENT  = 'sole_uploads_event_hook';

	private $plugin_settings;

	function __construct() {
		// Load the settings
		if( ! file_exists( __DIR__ . '/plugin-settings.ini' ) ) {
			throw new Exception( 'No plugin settings file found!', 1 );
		}
		$this->plugin_settings = parse_ini_file( 'plugin-settings.ini', true );

		// Create controllers
		$this->backup_controller = new Sole_AWS_Backup_Controller();
		$this->admin_controller  = new Sole_Admin_Controller(
			$this->plugin_settings,
			self::SETTINGS_GROUP );
		$this->logger            = Sole_AWS_Logger::get_instance();
	}

	public function init() {
		$this->admin_controller->init();

		// Setup the tables
		register_activation_hook( __FILE__, array( $this->logger, 'build_database' ) );

		// Setup the plugin options
		add_action( 'admin_init', array( $this, 'register_plugin_settings') );

		// Need to check that the timestamps are valid times
		add_filter( 'pre_update_option_sole_aws_db_backup_timestamp', array( $this,'check_if_is_valid_timestamp' ), 10, 2 );
		add_filter( 'pre_update_option_sole_aws_uploads_backup_timestamp', array( $this,'check_if_is_valid_timestamp' ), 10, 2 );

		// Need to add a weekly CRON job option
		add_filter( 'cron_schedules', array( $this, 'add_weekly_cron_job_option' ) );

		// Add the scheduled events
		add_action( self::DB_BACKUP_EVENT, array( $this, 'sole_db_backup' ) );
		add_action( self::UPLOADS_BACKUP_EVENT, array( $this, 'sole_uploads_backup' ) );

		// Need to check if the scheduled events settings were set
		add_action( 'update_option', array( $this, 'clear_outdated_schedules' ), 10, 3 );

		// Need to check on updating schedules/CRON jobs AFTER checking if there is updated option.
		add_action( 'shutdown', array( $this, 'verify_schedules_updated' ) );

		// Check if user wants to manually backup the DB & uploads
		if( isset( $_POST['manual-sole-backup-trigger'] ) ) {
			$this->backup_controller->backup_database();
			$this->backup_controller->backup_uploads_dir();
		}

		// Need to remove CRON jobs on deactivation
		register_deactivation_hook( __FILE__, array( $this, 'clear_plugin_info' ) );
		// Need to remove our custom table from the DB
		register_uninstall_hook( __FILE__, array( 'Sole_AWS_Logger', 'destroy_table' ) );
	}



	public function sole_db_backup() {
		$this->backup_controller->backup_database();
	}

	public function sole_uploads_backup() {
		$this->backup_controller->backup_uploads_dir();
	}

	// Need to register all the settings
	public function register_plugin_settings() {
		foreach ( $this->plugin_settings as $setting ) {
			register_setting( self::SETTINGS_GROUP, $setting['slug'] );
		}
	}

	/**
	 * ----------------------------------------------------------
	 *      CRON Job Related Functionality
	 * ----------------------------------------------------------
	 */

	// Check if a given value is a timestamp or not.
	// If not, return the old value.
	public function check_if_is_valid_timestamp( $new, $old ) {
		if( preg_match("/^(2[0-3]|[01][0-9]):([0-5][0-9])$/", $new ) ) {
			return $new;
		}
		return $old;
	}

	// Need to add a weekly CRON job option (if it doesn't already exist)
	public function add_weekly_cron_job_option( $schedules ) {
		if( ! isset( $schedules['weekly'] ) ) {
			$schedules['weekly'] = array(
				'interval' => 604800,
				'display'  => __('Once Weekly'),
			);
		}
		return $schedules;
	}

	// For both uploads and DB, checks if there is POST data
	// If so, need to clear the old scheduled event
	public function clear_outdated_schedules( $option, $old, $new ) {
		if( false !== strpos( $option, 'sole_aws_db_backup_' ) ) {
			wp_clear_scheduled_hook( self::DB_BACKUP_EVENT );
		}
		else if ( false !== strpos( $option, 'sole_aws_uploads_backup_' ) ) {
			wp_clear_scheduled_hook( self::UPLOADS_BACKUP_EVENT );
		}
	}

	// Checks if an event is scheduled. If not, attempt to create it.
	public function verify_schedules_updated() {
		if( ! wp_next_scheduled( self::DB_BACKUP_EVENT ) ) {
			$this->create_new_schedule(
				self::DB_BACKUP_EVENT,
				'sole_aws_db_backup_frequency',
				'sole_aws_db_backup_timestamp'
			);
		}
		if( ! wp_next_scheduled( self::UPLOADS_BACKUP_EVENT ) ) {
			$this->create_new_schedule(
				self::UPLOADS_BACKUP_EVENT,
				'sole_aws_uploads_backup_frequency',
				'sole_aws_uploads_backup_timestamp'
			);
		}
	}

	// Set a schedule for a given event IF all info is present for that event
	public function create_new_schedule( $event, $frequency, $time ) {
		// Make sure that the settings for event frequency && time of day
		$backup_frequency = get_option( $frequency );
		$backup_time = get_option( $time );
		// Only want to set if both are set
		if( $backup_frequency && $backup_time ) {
			// Get the UNIX timestamp
			$start_time = $this->get_start_time( $backup_frequency, $backup_time );
			$cron_frequency = ( 'daily' == $backup_frequency ) ? 'daily' : 'weekly';
			wp_schedule_event( $start_time, $cron_frequency, $event );
			$this->logger->add_log_event( 'Set scheduled event for ' . $event . ': ' . $backup_frequency . ' at ' . $backup_time, 'schedule change' );
		}
	}

	// Helper function to take a day of the week & a time and create a UNIX timestamp equivalent
	public function get_start_time( $frequency, $time ) {
		$start_time = 0;
		if( 'daily' != $frequency ) {
			$start_time = strtotime( $frequency . ' ' . $time );
		} else {
			$start_time = strtotime( $time );
		}
		return $start_time;
	}

	// Plugin is being deactivated, need to remove CRON jobs
	public function clear_plugin_info() {
		wp_clear_scheduled_hook( self::DB_BACKUP_EVENT );
		wp_clear_scheduled_hook( self::UPLOADS_BACKUP_EVENT );
	}
}

$sole_aws_backup = new Sole_AWS_Backup();
$sole_aws_backup->init();
