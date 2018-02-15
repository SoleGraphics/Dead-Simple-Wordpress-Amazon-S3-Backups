<?php

/**
 * This class is meant for setting and retriving history logs
 *
 * Also controls user notifications.
 */

class Sole_AWS_Logger {

	const TABLE_NAME = 'sole_aws_log';

	public $num_to_display = 10;

	protected static $instance;

	public static function get_instance() {
		if( null === self::$instance ) {
			self::$instance = new Sole_AWS_Logger();
		}
		return self::$instance;
	}

	private function __construct() {
		if( ! $this->table_exists() ) {
			$this->create_log_table();
		}
	}

	/**
	 * Add event to the table
	 *     $msg:    the message to enter into the logger
	 *     $sender: the sending class/object loging the message
	 *     $status: whether the message is an error or generic info logging
	 */
	public function add_log_event( $msg, $sender, $status='error' ) {
		global $wpdb;
		$time_added = date('Y-m-d H:i:s');
		$wpdb->insert( $wpdb->prefix . self::TABLE_NAME, array(
			'log_time'    => $time_added,
			'log_message' => $msg,
			'log_sender'  => $sender,
			'log_status'  => $status,
		) );
	}

	/**
	 * Returns a list of the different log senders in the table
	 */
	public function get_log_senders() {
		global $wpdb;
		$command = 'SELECT DISTINCT log_sender FROM ' . $wpdb->prefix . self::TABLE_NAME . ';';
		$results = $wpdb->get_results( $command );
		return $results;
	}

	/**
	 * Prints out the pagination links for the error logs
	 */
	public function the_pagination_links( $page ) {
		// Get the cuurent link's page
		$protocol = isset($_SERVER["HTTPS"]) ? 'https://' : 'http://';
		$base_link = $protocol . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$current_page_link = 'page_to_display=' . $page;
		$max_pages = $this->get_max_number_results();

		// Only display the previous page if not on the first page
		if( 1 < $page ) {
			$previous_link = str_replace( $current_page_link, 'page_to_display=' . ( $page - 1 ), $base_link );
			echo "<a class=\"pagination_links--previous\" href=\"$previous_link\">Previous</a>";
		}

		// Display the next page link if not on the final pages
		if( ( $page * $this->num_to_display ) < $max_pages ) {
			$next_link = str_replace( $current_page_link, 'page_to_display=' . ( $page + 1 ), $base_link );
			echo "<a href=\"$next_link\">Next</a>";
		}
	}

	/**
	 * Takes an sql results array, and returns a simple array of values from each row associated with a given key
	 */
	public function simplify_array( $rows, $key ) {
		$to_return = array();
		foreach ( $rows as $row ) {
			if( isset( $row->{$key} ) ) {
				$to_return[] = $row->{$key};
			}
		}
		return $to_return;
	}

	/**
	 * Get log messages
	 *     $offset: the message to start at
	 *     $num_to_retrieve: the number of messages to return
	 */
	public function get_log_messages( $offset, $msg_type='error', $sender="" ) {
		global $wpdb;
		// Account for display offset starting at 1, not 0
		$offset--;
		$command = 'SELECT * FROM ' . $wpdb->prefix . self::TABLE_NAME . ' WHERE log_status LIKE \'%' . $msg_type . '%\' AND log_sender LIKE \'%' . $sender . '%\'  ORDER BY log_time DESC LIMIT ' . ( $offset * $this->num_to_display ) . ',' . $this->num_to_display . ';';
		$results = $wpdb->get_results( $command );
		return $results;
	}

	/**
	 * Get max number of pages/logs that can be displayed with current parameters
	 */
	public function get_max_number_results( $msg_type='error', $sender="" ) {
		global $wpdb;
		$command = 'SELECT * FROM ' . $wpdb->prefix . self::TABLE_NAME . ' WHERE log_status LIKE \'%' . $msg_type . '%\' AND log_sender LIKE \'%' . $sender . '%\';';
		$results = $wpdb->get_results( $command );
		return count( $results );
	}

	/**
	 * Create the trove error logger table
	 */
	private function create_log_table() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		global $wpdb;

		$full_table_name = $wpdb->prefix . self::TABLE_NAME;
		$charset = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $full_table_name (
			ID mediumint NOT NULL AUTO_INCREMENT,
			log_time datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
			log_message text DEFAULT '' NOT NULL,
			log_sender text DEFAULT '' NOT NULL,
			log_status text DEFAULT '' NOT NULL,
			PRIMARY KEY (ID)
		) $charset;";

		dbDelta( $sql );
	}

	/**
	 * Checks the log table exists in the database
	 */
	private function table_exists() {
		global $wpdb;
		$full_table_name = $wpdb->prefix . self::TABLE_NAME;

		if( $full_table_name == $wpdb->get_var("SHOW TABLES LIKE '$full_table_name'") ) {
			return true;
		} else {
			return false;
		}
	}
}
