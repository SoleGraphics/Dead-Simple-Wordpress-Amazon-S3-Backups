<?php

class Sole_AWS_Backup_Controller {

	public function __construct() {
		$this->aws_controller = Sole_AWS_Controller::get_instance();
		$this->logger         = Sole_AWS_Logger::get_instance();
	}

	public function backup_uploads_dir() {
		$uploads_dir = wp_upload_dir();
		$was_uploaded = $this->aws_controller->upload_dir( $uploads_dir['basedir'] );
		if( $was_uploaded ) {
			$this->logger->add_log_event( 'Site uploads successfully backed up - ' . date('Y-m-d H'), 'successful backup' );
			$this->logger->register_user_email( 'Your Wordpress site\'s uploads have been backup up to your Amazon Bucket! Thank you for choosing Dead Simple Backup.' );
		} else {
			$this->logger->add_log_event( 'Site uploads failed backed up - ' . date('Y-m-d H'), 'failed backup' );
		}
	}

	/**
	 * Need to create a sql dump of the database, and then upload the dump to AWS
	 * Use the native systems sql dump OS command to create the dump.
	 */
	public function backup_database() {
		// First need the correct command and command path
		$mysql_cmd = $this->get_mysql_cmd();
		$mysql_path = $this->get_path_to_mysql( $mysql_cmd );

		// If there is no command path found, abort.
		if( ! $mysql_path ) {
			$this->logger->add_log_event( 'Couldn\'t find path to `mysqldump` command: aborting.', 'database backup error' );
			return;
		}

		// Dump file output info
		$path = plugin_dir_path( __DIR__ );
		$file_name = 'db-backup-' . date('Y-m-d') . '.sql';

		// Build the command
		$cmd = $mysql_path . $mysql_cmd . ' -h ' . escapeshellarg( DB_HOST ) . ' -u ' . escapeshellarg( DB_USER ) . ' -p' . escapeshellarg( DB_PASSWORD ) . ' ' . escapeshellarg( DB_NAME ) . ' > ' . $path . $file_name;

		// Finally, run the command.
		exec( $cmd, $output, $results );

		// Check if there was an error
		if( ! file_exists( $path . $file_name ) ) {
			$this->logger->add_log_event( 'Couldn\'t create the DB backup file.', 'database backup error' );
			return;
		}

		// Upload the dump to the AWS bucket
		$was_uploaded = $this->aws_controller->upload_file( $path, $file_name );
		// If the database was uploaded, register an email event
		if( $was_uploaded ) {
			$this->logger->add_log_event( 'Site database successfully backed up - ' . date('Y-m-d H'), 'successful backup' );
			$this->logger->register_user_email( 'Your Wordpress site\'s database has been backup up to your Amazon Bucket! Thank you for choosing Dead Simple Backup.' );
		} else {
			$this->logger->add_log_event( 'Site database failed backed up - ' . date('Y-m-d H'), 'failed backup' );
		}

		// Delete file now that it's either on amazon OR things went VERY wrong.
		unlink( $path . $file_name );
	}

	// NEED TO GET CORRECT PATH TO THE mysqldump COMMAND!!!!
	public function get_path_to_mysql( $mysql_cmd ) {
		$possible_paths = array(
			'', '/usr/', '/usr/bin/', '/usr/bin/mysql/', '/usr/bin/mysql/bin/', '/usr/local/', '/usr/local/mysql/', '/usr/local/mysql/bin/'
		);
		$return_code = array();
		$output = array();
		foreach( $possible_paths as $path ) {
			exec( $path . $mysql_cmd . ' --help', $output, $return_code );
			if( 0 === $return_code ) {
				return $path;
			}
		}
		// Uh-oh, they can no use mysqldump. Or I'm missing a possible path.
		return false;
	}

	// Because windows wants the '.exe', need to check the OS type
	public function get_mysql_cmd() {
		$is_win = stristr( PHP_OS, 'WIN' );
		$is_dar = stristr( PHP_OS, 'DARWIN' );
		if( $is_win && ! $is_dar ) {
			return 'mysqldump.exe';
		}
		return 'mysqldump';
	}
}
