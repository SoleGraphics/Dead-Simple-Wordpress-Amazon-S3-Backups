<?php

/**
 * This class is meant for setting and retriving history logs
 */

class Sole_AWS_Logger {

	const NUM_ROW_DISPLAY    = 3;
	const DB_TABLE_EXTENSION = 'sole_aws_log';
	protected static $instance;

	public static function get_instance() {
		if( null === self::$instance ) {
			self::$instance = new Sole_AWS_Logger();
		}
		return self::$instance;
	}

	private function __construct() {
		global $wpdb;
		// Page offset for displaying logs to the admins
		$page_on = isset( $_GET['sole_log_page'] ) ? $_GET['sole_log_page'] : 1;
		$this->page_on = max( 1, $page_on ) - 1;

		// Need to get the maximum number of pages allowed
		$max_num_sql = 'SELECT COUNT(*) FROM ' . $wpdb->prefix . self::DB_TABLE_EXTENSION;
		$num_rows = $wpdb->get_var( $max_num_sql );
		$this->max_page = ceil( $num_rows / self::NUM_ROW_DISPLAY ) - 1;
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
			log_type text DEFAULT '' NOT NULL,
			PRIMARY KEY (ID)
		) $charset;";
		dbDelta( $sql );
	}

	// Should only be run on plugin uninstall.
	public static function destroy_table() {
		global $wpdb;
		$sql = "DROP TABLE IF EXISTS " . $wpdb->prefix . self::DB_TABLE_EXTENSION;
		$wpdb->query( $sql );
	}

	public function add_log_event( $msg, $type='event' ) {
		global $wpdb;
		$time_added = date('Y-m-d H:i:s');
		$wpdb->insert( $wpdb->prefix . self::DB_TABLE_EXTENSION, array(
			'log_time'    => $time_added,
			'log_message' => $msg,
			'log_type'    => $type,
		) );
	}

	public function get_log_events() {
		global $wpdb;
		$command = 'SELECT * FROM ' . $wpdb->prefix . self::DB_TABLE_EXTENSION . ' ORDER BY ID ASC LIMIT ' . ( $this->page_on * self::NUM_ROW_DISPLAY ) . ',' . self::NUM_ROW_DISPLAY . ';';
		$results = $wpdb->get_results( $command );
		return $results;
	}

	public function the_table_pagination() {
		$base_url = admin_url( 'admin.php?page=sole-settings-page-logs' );
		$previous =  ( 1 <= $this->page_on ) ? $base_url . '&sole_log_page=' . $this->page_on : false;
		// +2 is for the pretty URLs being ahead of the actual offset value by 1
		$next = ( $this->max_page > $this->page_on ) ? $base_url . '&sole_log_page=' . ( $this->page_on + 2 ) : false; ?>
		<div class="sole-log-pagination">
			<?php if( $previous ): ?>
				<a href="<?php echo $previous; ?>">Previous Page</a>
			<?php endif; ?>
			<?php if( $next ): ?>
				<a href="<?php echo $next; ?>">Next Page</a>
			<?php endif; ?>
		</div>
	<?php }
}
