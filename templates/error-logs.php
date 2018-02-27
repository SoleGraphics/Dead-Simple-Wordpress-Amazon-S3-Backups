<div class="wrap">
    <h1>Error Logs</h1>
    <form method="GET" action="">
        <input type="hidden" name="page" value="sole-settings-page-logs" /><br/>
        <table>
            <tr>
                <td><label>Message Types</label></td>
                <td><select name="msg_type">
                    <option value="">All</option>
                    <option value="error" <?php if( 'error' == $type ): ?>selected="selected"<?php endif; ?>>Errors</option>
                    <option value="general" <?php if( 'general' == $type ): ?>selected="selected"<?php endif; ?>>General</option>
                </select></td>
            </tr>
            <tr>
                <td><label>Error Origin</label></td>
                <td><select name="sender">
                    <option value="">All Senders</option>
                    <?php foreach( $senders as $curr_sender ): ?>
                        <option value="<?php echo $curr_sender; ?>" <?php if( $curr_sender == $sender ): ?>selected="selected"<?php endif; ?>><?php echo $curr_sender; ?></option>
                    <?php endforeach; ?>
                </select></td>
            </tr>
            <tr>
                <td><label>Page Number</label></td>
                <td><input type="text" name="page_to_display" placeholder="Page Number" value="<?php echo $page; ?>" /> / <?php echo $total_pages; ?></td>
            </tr>
        </table>
        <input type="submit" value="Search" />
    </form>
    <hr/>
    <?php if( 0 < count( $logs ) ): ?>
        <table class="log-wrapper">
            <tr>
                <td>Error Origin</td>
                <td>Time</td>
                <td>Message</td>
            </tr>
            <?php foreach ( $logs as $log_entry ): ?>
                <tr>
                    <td><?php echo $log_entry->log_sender; ?></td>
                    <td><?php echo $log_entry->log_time; ?></td>
                    <td><?php echo $log_entry->log_message; ?></td>
                </tr>
            <?php endforeach; ?>
        </table>
        <p class="pagination_links">
            <?php $this->table_controller->the_pagination_links( $page ); ?>
        </p>
    <?php else: ?>
        <p>
            No log files found.
        </p>
    <?php endif; ?>
    <style>
        .log-wrapper {
            margin-top: 20px;
            border-spacing: 0;
        }
        .log-wrapper tr:nth-child(2n) {
            background-color: #ccc;
        }
        .log-wrapper tr td {
            padding: 10px 20px;
        }
        .log-wrapper tr td:nth-child(2) {
            padding: 10px 30px;
        }
        .pagination_links {
            margin-top: 20px;
        }
        .pagination_links--previous {
            margin-right: 20px;
        }
    </style>
</div>
