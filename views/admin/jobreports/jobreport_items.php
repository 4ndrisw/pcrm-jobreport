<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>


            <div class="widget" id="widget-related" data-name="<?php echo _l('jobreport_related'); ?>">
                <?php if(staff_can('view', 'jobreports') || staff_can('view_own', 'jobreports')) { ?>
                <div class="panel_s related_tasks-expiring">
                    <div class="panel-body padding-10">
                        <p class="padding-5"><?php echo _l('jobreport_related'); ?></p>
                        <hr class="hr-panel-heading-dashboard">
                        <?php if (!empty($related_tasks)) { ?>
                            <div class="table-vertical-scroll">
                                <table id="widget-<?php echo create_widget_id(); ?>" class="table dt-table" data-order-col="0" data-order-type="DESC">
                                    <thead>
                                        <tr>
                                            <th><?php echo _l('select'); ?></th>
                                            <th><?php echo _l('jobreport_number'); ?> #</th>
                                            <th><?php echo _l('jobreport_list_date'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                       <?php $i = 1; ?>
                                        <?php foreach ($related_tasks as $task) { ?>
                                            <tr>
                                                <td>
                                                   <?= $i ?> 
                                                </td>
                                                <td>
                                                    <?php echo '<a href="' . admin_url("tasks/jobreport/" . $task["id"] . '">' . $task["name"]) . '</a>'; ?>
                                                </td>
                                                <td>
                                                    <?php echo _d($task['dateadded']); ?>
                                                </td>
                                            </tr>
                                        <?php $i++; } ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php } else { ?>
                            <div class="text-center padding-5">
                                <i class="fa fa-check fa-5x" aria-hidden="true"></i>
                                <h4><?php echo _l('no_jobreport_related',["7"]) ; ?> </h4>
                            </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>

