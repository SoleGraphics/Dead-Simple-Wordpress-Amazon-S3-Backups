<?php

/**
 * This class is meant for setting and retriving history logs
 *
 * Also controls user notifications.
 */

require_once( 'sole-database-manager.php' );

class Sole_AWS_Logger extends Database_Table_Manager {

	public $num_to_display = 10;

	protected static $instance;

	public static function get_instance() {
		if( null === self::$instance ) {
			self::$instance = new Sole_AWS_Logger();
		}
		return self::$instance;
	}

	// Use the DB manager to create/update the database
	public function __construct() {
		$db_path = plugin_dir_path( __DIR__ ) . 'database.ini';
		$this->init_db( $db_path );
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
		$wpdb->insert( $wpdb->prefix . $this->table_name, array(
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
		$command = 'SELECT DISTINCT log_sender FROM ' . $wpdb->prefix . $this->table_name . ';';
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
		$command = 'SELECT * FROM ' . $wpdb->prefix . $this->table_name . ' WHERE log_status LIKE \'%' . $msg_type . '%\' AND log_sender LIKE \'%' . $sender . '%\'  ORDER BY log_time DESC LIMIT ' . ( $offset * $this->num_to_display ) . ',' . $this->num_to_display . ';';
		$results = $wpdb->get_results( $command );
		return $results;
	}

	/**
	 * Get max number of pages/logs that can be displayed with current parameters
	 */
	public function get_max_number_results( $msg_type='error', $sender="" ) {
		global $wpdb;
		$command = 'SELECT * FROM ' . $wpdb->prefix . $this->table_name . ' WHERE log_status LIKE \'%' . $msg_type . '%\' AND log_sender LIKE \'%' . $sender . '%\';';
		$results = $wpdb->get_results( $command );
		return count( $results );
	}

	// ---------------------------------------------------------------
	// Resgister sending site admin an email that backups where made
	// ---------------------------------------------------------------
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
