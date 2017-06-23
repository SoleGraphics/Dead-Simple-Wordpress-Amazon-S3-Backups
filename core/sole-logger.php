<?php

/**
 * This class is meant for setting and retriving history logs
 *
 * Also controls user notifications.
 */

class Sole_AWS_Logger {

	const NUM_ROW_DISPLAY    = 15;
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

	// Load the log messages for the current page.
	public function get_log_events() {
		global $wpdb;
		$command = 'SELECT * FROM ' . $wpdb->prefix . self::DB_TABLE_EXTENSION . ' ORDER BY ID DESC LIMIT ' . ( $this->page_on * self::NUM_ROW_DISPLAY ) . ',' . self::NUM_ROW_DISPLAY . ';';
		$results = $wpdb->get_results( $command );
		return $results;
	}

	// Need to add pagination to the table.
	public function the_table_pagination() {
		$base_url = admin_url( 'admin.php?page=sole-settings-page-logs' );
		// Get the previous page URL
		$previous =  ( 1 <= $this->page_on ) ? $base_url . '&sole_log_page=' . $this->page_on : false;
		// +2 is for the pretty URLs being ahead of the actual offset value by 1
		$next = ( $this->max_page > $this->page_on ) ? $base_url . '&sole_log_page=' . ( $this->page_on + 2 ) : false; ?>
		<div class="sole-log-pagination">
			<?php if( $previous ): ?>
				<a href="<?php echo $previous; ?>">Newer Entries</a>
			<?php endif; ?>
			<?php if( $next ): ?>
				<a href="<?php echo $next; ?>">Older Entries</a>
			<?php endif; ?>
		</div>
	<?php }

	/**
	 * Safely setup mailing admin(s) of successful backups
	 */
	public function register_user_email( $msg='' ) {
		if( function_exists( 'wp_mail' ) ) {
			$this->send_user_email( $msg );
		} else {
			// Wait till the function exists and then send the email.
			add_action( 'plugins_loaded', function() use( $msg ) {
				$this->send_user_email( $msg );
			} );
		}
	}

	/**
	 * Actually send the backup upload notification email.
	 */
	public function send_user_email( $msg ) {
		$email_addr = get_option( 'notification_address' );
		$email_subject = get_bloginfo( 'name' ) . ' Backup';

		// If there isn't an address / recipient to send to, return.
		if( empty( $email_addr ) || empty( $msg ) ) {
			return;
		}

		wp_mail( $email_addr, $email_subject, $msg );
	}
}
