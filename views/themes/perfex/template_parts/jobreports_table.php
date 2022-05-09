<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<table class="table dt-table table-jobreports" data-order-col="1" data-order-type="desc">
    <thead>
        <tr>
            <th><?php echo _l('jobreport_number'); ?> #</th>
            <th><?php echo _l('jobreport_list_project'); ?></th>
            <th><?php echo _l('jobreport_list_date'); ?></th>
            <th><?php echo _l('jobreport_list_status'); ?></th>

        </tr>
    </thead>
    <tbody>
        <?php foreach($jobreports as $jobreport){ ?>
            <tr>
                <td><?php echo '<a href="' . admin_url("jobreports/jobreport/" . $jobreport["id"]) . '">' . format_jobreport_number($jobreport["id"]) . '</a>'; ?></td>
                <td><?php echo $jobreport['name']; ?></td>
                <td><?php echo _d($jobreport['date']); ?></td>
                <td><?php echo format_jobreport_status($jobreport['status']); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table>
