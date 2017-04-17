<div class="wrap">
	<h1>Dead Simple Wordpress Amazon S3 Backups</h1>
	<h2>Logs</h2>
	<div class="log-wrapper">
		<?php $log_file = plugin_dir_path( __DIR__ ) . 'error-log';
		$handle = fopen( $log_file, 'r' );
		if( $handle ) {
			while( false !== ( $line = fgets( $handle ) ) ) { ?>
				<div class="single-error"><?php echo $line; ?></div>
			<?php }
		}
		fclose( $handle ); ?>
	</div>
</div>
