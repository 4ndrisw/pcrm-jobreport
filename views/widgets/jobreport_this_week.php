<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php
    $CI = &get_instance();
    $CI->load->model('jobreports/jobreports_model');
    $jobreports = $CI->jobreports_model->get_jobreports_this_week(get_staff_user_id());
?>

<div class="widget" id="widget-<?php echo create_widget_id(); ?>" data-name="<?php echo _l('jobreport_this_week'); ?>">
    <?php if(staff_can('view', 'jobreports') || staff_can('view_own', 'jobreports')) { ?>
    <div class="panel_s jobreports-expiring">
        <div class="panel-body padding-10">
            <p class="padding-5"><?php echo _l('jobreport_this_week'); ?></p>
            <hr class="hr-panel-heading-dashboard">
            <?php if (!empty($jobreports)) { ?>
                <div class="table-vertical-scroll">
                    <a href="<?php echo admin_url('jobreports'); ?>" class="mbot20 inline-block full-width"><?php echo _l('home_widget_view_all'); ?></a>
                    <table id="widget-<?php echo create_widget_id(); ?>" class="table dt-table" data-order-col="3" data-order-type="desc">
                        <thead>
                            <tr>
                                <th><?php echo _l('jobreport_number'); ?> #</th>
                                <th class="<?php echo (isset($client) ? 'not_visible' : ''); ?>"><?php echo _l('jobreport_list_client'); ?></th>
                                <th><?php echo _l('jobreport_list_project'); ?></th>
                                <th><?php echo _l('jobreport_list_date'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($jobreports as $jobreport) { ?>
                                <tr>
                                    <td>
                                        <?php echo '<a href="' . admin_url("jobreports/jobreport/" . $jobreport["id"]) . '">' . format_jobreport_number($jobreport["id"]) . '</a>'; ?>
                                    </td>
                                    <td>
                                        <?php echo '<a href="' . admin_url("clients/client/" . $jobreport["userid"]) . '">' . $jobreport["company"] . '</a>'; ?>
                                    </td>
                                    <td>
                                        <?php echo $jobreport['name']; ?>
                                    </td>
                                    <td>
                                        <?php echo _d($jobreport['date']); ?>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            <?php } else { ?>
                <div class="text-center padding-5">
                    <i class="fa fa-check fa-5x" aria-hidden="true"></i>
                    <h4><?php echo _l('no_jobreport_this_week',["7"]) ; ?> </h4>
                </div>
            <?php } ?>
        </div>
    </div>
    <?php } ?>
</div>
