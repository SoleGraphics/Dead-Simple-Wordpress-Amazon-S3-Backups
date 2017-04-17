<div class="wrap">
	<h1>Dead Simple Wordpress Amazon S3 Backups</h1>
	<form method="POST" action="options.php">
		<?php settings_fields( 'sole_aws_simple_backup_fields' ); ?>
		<?php do_settings_sections( 'sole-settings-page' ); ?>
		<table>
			<?php foreach ( $this->plugin_settings as $name => $option ) { ?>
				<tr>
					<td><?php echo $name; ?></td>
					<td>
						<input type="text" name="<?php echo $option; ?>" value="<?php echo get_option( $option ); ?>" />
					</td>
				</tr>
			<?php } ?>
		</table>
		<?php submit_button(); ?>
	</form>
</div>
