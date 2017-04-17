<div class="wrap">
	<h1>Dead Simple Wordpress Amazon S3 Backups</h1>
	<form method="POST" action="options.php">
		<?php settings_fields( 'sole_aws_simple_backup_fields' ); ?>
		<?php do_settings_sections( 'sole-settings-page' ); ?>
		<h2>Settings</h2>
		<table>
			<?php foreach ( $this->plugin_settings as $name => $option ) { ?>
				<tr>
					<td><?php echo $name; ?></td>
					<td>
						<?php if( 'array' == gettype( $option ) ): ?>
							<select>
								<option name="<?php echo $option['slug']; ?>" value="">Select</option>
								<?php foreach( $option['options'] as $dd_option ): ?>
									<option name="<?php echo $option['slug']; ?>" value="<?php echo $dd_option; ?>"><?php echo ucwords( $dd_option ); ?></option>
								<?php endforeach; ?>
							</select>
						<?php else: ?>
							<input type="text" name="<?php echo $option; ?>" value="<?php echo get_option( $option ); ?>" />
						<?php endif; ?>
					</td>
				</tr>
			<?php } ?>
		</table>
		<?php submit_button(); ?>
	</form>
	<h2>Extras</h2>
	<form method="POST" action="options.php">
		<input type="hidden" name="manual-sole-backup-trigger" value="true" />
		<?php submit_button( 'Backup Files & Database' ); ?>
	</form>
</div>
