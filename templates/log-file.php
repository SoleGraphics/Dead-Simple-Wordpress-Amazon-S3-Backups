<div class="wrap">
	<h1>Dead Simple Wordpress Amazon S3 Backups</h1>
	<h2>Logs</h2>
	<div class="log-wrapper">
		<?php $logger = Sole_AWS_Logger::get_instance(); ?>
		<?php $log_messages = $logger->get_log_events(); ?>
		<table>
			<?php foreach ( $log_messages as $message ): ?>
				<tr class="message-container">
					<td><?php echo $message->log_time; ?></td>
					<td><?php echo $message->log_type; ?></td>
					<td><?php echo $message->log_message; ?></td>
				</tr>
			<?php endforeach; ?>
		</table>
		<?php $logger->the_table_pagination(); ?>
	</div>
</div>
