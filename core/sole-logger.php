<?php

/**
 * This class is meant for setting and retriving history logs
 */

class Sole_AWS_Logger {

	const DB_TABLE_EXTENSION = 'sole_aws_log';
	protected static $instance;

	public static function get_instance() {
		if( null === self::$instance ) {
			self::$instance = new Sole_AWS_Logger();
		}
		return self::$instance;
	}

	private function __construct() {
		// Page offset for displaying logs to the admins
		$page_on = isset( $_GET['sole_log_page'] ) ? $_GET['sole_log_page'] : 1;
		$this->page_on = max( 1, $page_on ) - 1;
	}

	// Add table to the database, should only be called on plugin activation
	public function build_database() {
		global $wpdb;
		$full_table_name = $wpdb->prefix . self::DB_TABLE_EXTENSION;
		// If the table is already there, we don't want to recreate it.
		if( $full_table_name == $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") ) {
			return;
		}
		// Create SQL to add table & execute
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		$charset = $wpdb->get_charset_collate();
		$sql = "CREATE TABLE $full_table_name (
			ID mediumint NOT NULL AUTO_INCREMENT,
			log_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			log_message text DEFAULT '' NOT NULL,
			PRIMARY KEY (ID)
		) $charset;";
		dbDelta( $sql );
	}

	// Should only be run on plugin uninstall.
	public function destroy_table() {
		// TODO: delete table
	}

	public function add_log_event( $msg, $type ) {
		// TODO: log event
	}

	public function get_log_events() {
		// TODO: get events based on offset
	}

	public function get_table_pagination() {
		// display pagination for table
	}
}
