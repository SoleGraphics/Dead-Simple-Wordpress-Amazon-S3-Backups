<?php

/**
 * This class is the gateway to AWS
 *
 * Assumes that AWS S3 has been loaded
 */

use Aws\S3\S3Client;
use Aws\Exception\AwsException;
use Aws\Credentials\Credentials;

class Sole_AWS_Controller {

	protected static $instance;

	public static function get_instance() {
		if( self::$instance === null ) {
			self::$instance = new Sole_AWS_Controller();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->access_key    = get_option( 'sole_aws_access_key' );
		$this->access_secret = get_option( 'sole_aws_access_secret' );
		$this->bucket        = get_option( 'sole_aws_bucket' );
		$this->region        = get_option( 'sole_aws_region' );
		$this->logger        = Sole_AWS_Logger::get_instance();
	}

	public function upload_dir( $dir_path ) {
		$s3_client = $this->get_s3_client();
		try {
			error_log('putting object');
			// $s3_client->uploadDirectory( $dir_path, $this->bucket, 'uploads' );
		}
		catch( S3Exception $e ) {
			$this->logger->add_log_event( $e->getMessage(), 'uploads backup error' );
		}
	}

	public function upload_file( $file_path, $file_name ) {
		$s3_client = $this->get_s3_client();
		try {
			error_log('putting object');
			// $result = $s3_client->putObject([
			//     'Bucket'     => $this->bucket,
			//     'Key'        => $file_name,
			//     'SourceFile' => $file_path . $file_name,
			// ]);
		}
		catch( S3Exception $e ) {
			$this->logger->add_log_event( $e->getMessage(), 'database backup error' );
		}
	}

	// Common setup for both upload procedures
	private function get_s3_client() {
		$s3_client = new S3Client([
		    'region'      => $this->region,
		    'version'     => 'latest',
		    'credentials' => array(
				'key'    => $this->access_key,
				'secret' => $this->access_secret,
			),
		]);
		return $s3_client;
	}
}
