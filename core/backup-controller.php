<?php

class Sole_AWS_Backup_Controller {

	public function __construct() {
		$this->aws_controller = Sole_AWS_Controller::get_instance();
	}

	public function backup_uploads_dir() {
		$uploads_dir = wp_upload_dir();
		$this->aws_controller->upload_dir( $uploads_dir['basedir'] );
	}

	public function backup_database() {
		// First need the correct command and command path
		$mysql_cmd = $this->get_mysql_cmd();
		$mysql_path = $this->get_path_to_mysql( $mysql_cmd );

		// File output info
		$path = plugin_dir_path( __DIR__ );
		$file_name = 'db-backup-' . date('Y-m-d') . '.sql';

		// Build the command
		$cmd = $mysql_path . $mysql_cmd . ' -h ' . escapeshellarg( DB_HOST ) . ' -u ' . escapeshellarg( DB_USER ) . ' -p' . escapeshellarg( DB_PASSWORD ) . ' ' . escapeshellarg( DB_NAME ) . ' > ' . $path . $file_name . ' 2>> ' . $path . 'error.log';

		// Finally can run the command.
		exec( $cmd, $output, $results );

		// Check if there was an error
		if( ! file_exists( $path . $file_name ) ) {
			return;
		}

		$this->aws_controller->upload_file( $path, $file_name );

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
			// use $mysql_cmd to check if the path hosts the mysqldump command
			exec( $path . 'mysqldump --help', $output, $return_code );
			if( 0 === $return_code ) {
				return $path;
			}
		}
		// Uh-oh, they can no use mysqldump. Or I'm missing a possible path
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
