<div class="wrap">
    <h1>Dead Simple Wordpress Amazon S3 Backups</h1>
    <form method="POST" action="admin.php?page=sole-settings-page">
        <?php wp_nonce_field( 'aws_options', self::SETTINGS_NONCE_NAME ); ?>
        <?php settings_fields( $this->settings_group ); ?>
        <?php do_settings_sections( 'sole-settings-page' ); ?>
        <h2>Settings</h2>
        <table>
            <?php foreach ( $this->plugin_settings as $name => $option ) { ?>
                <tr>
                    <td><?php echo $name; ?></td>
                    <td>
                        <?php if( isset( $option['options'] ) ):
                            $current_val = get_option( $option['slug'] ); ?>
                            <select name="<?php echo $option['slug']; ?>">
                                <option value="">Select</option>
                                <?php foreach( $option['options'] as $dd_option ): ?>
                                    <option value="<?php echo $dd_option; ?>" <?php if($dd_option == $current_val ): ?> selected="selected" <?php endif; ?>><?php echo ucwords( $dd_option ); ?></option>
                                <?php endforeach; ?>
                            </select>
                        <?php else: ?>
                            <input type="text" name="<?php echo $option['slug']; ?>" value="<?php echo get_option( $option['slug'] ); ?>" />
                        <?php endif; ?>
                        <?php if( isset( $option['instruction'] ) ): ?>
                            </td><td><?php echo $option['instruction']; ?>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php } ?>
        </table>
        <?php submit_button(); ?>
    </form>
    <h2>Extras</h2>
    <form method="POST" action="">
        <input type="hidden" name="manual-sole-backup-trigger" value="true" />
        <?php submit_button( 'Backup Files & Database' ); ?>
    </form>
    <form method="POST" action="">
        <input type="hidden" name="download-sole-db-backup-trigger" value="true" />
        <?php submit_button( 'Download Database Backup' ); ?>
    </form>
    <form method="POST" action="">
        <input type="hidden" name="download-sole-uploads-backup-trigger" value="true" />
        <?php submit_button( 'Download Uploads Backup' ); ?>
    </form>
</div>
