<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="widget" id="widget-<?php echo create_widget_id(); ?>" data-name="<?php echo _l('jobreport_related'); ?>">
    <?php if(staff_can('view', 'jobreports') || staff_can('view_own', 'jobreports')) { ?>
    <div class="panel_s related_tasks-expiring">
        <div class="panel-body padding-10">
            <p class="padding-5"><?php echo _l('jobreport_related'); ?></p>
            <hr class="hr-panel-heading-dashboard">

            <?php if($jobreport->status == 2 ){ ?>

                <div class="text-center padding-5">
                    <i class="fa fa-check fa-5x" aria-hidden="true"></i>
                    <h4><?php echo _l('this_jobreport_already_send',["7"]) ; ?> </h4>
                </div>

            <?php }else {?>

                <?php if (!empty($related_tasks)) { ?>
                    <div class="table-vertical-scroll">
                        <table id="widget-<?php echo create_widget_id(); ?>" class="table dt-table" data-order-col="0" data-order-type="ASC">
                            <thead>
                                <tr>
                                    <th><?php echo _l('select'); ?></th>
                                    <th><?php echo _l('jobreport_task'); ?> #</th>
                                    <th><?php echo _l('jobreport_tags'); ?> #</th>
                                </tr>
                            </thead>
                            <tbody>
                               <?php $i = 0; ?>
                                <?php foreach ($related_tasks as $task) { ?>
                                    <tr>
                                        <td>
                                           <input type="checkbox" name="<?php echo 'tasks['.$i.'][task_id]'; ?>" value="<?php echo $task["id"]; ?>">
                                           <input type="hidden" name="<?php echo 'tasks['.$i.'][tag_id]'; ?>" value="<?php echo $task["tag_id"]; ?>">
                                        </td>
                                        <td>
                                            <?php echo $task["name"]; ?>
                                        </td>
                                        <td>
                                            <?php echo $task["tags_name"]; ?>
                                        </td>
                                    </tr>
                                <?php $i++; } ?>
                            </tbody>
                        </table>
                         <div class="col-md-12">
                            <button type="button" class="btn-tr btn btn-info jobreport-form-submit transaction-submit">Save</button>
                         </div>
                    </div>
                <?php } else { ?>
                    <div class="text-center padding-5">
                        <i class="fa fa-check fa-5x" aria-hidden="true"></i>
                        <h4><?php echo _l('no_equipment_available',["7"]) ; ?> </h4>
                    </div>
                <?php } ?>

            <?php } ?>


        </div>
    </div>
    <?php } ?>
</div>
