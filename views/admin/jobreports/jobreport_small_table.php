<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

<div class="panel_s">
    <div class="panel-body">
     <?php if(has_permission('jobreports','','create')){ ?>
     <div class="_buttons">
        <a href="<?php echo admin_url('jobreports/create'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('new_jobreport'); ?></a>
     </div>
     <?php } ?>
     <?php if(has_permission('jobreports','','create')){ ?>
     <div class="_buttons">
        <a href="<?php echo admin_url('jobreports'); ?>" class="btn btn-primary pull-right display-block"><?php echo _l('jobreports'); ?></a>
     </div>
     <?php } ?>
     <div class="clearfix"></div>
     <hr class="hr-panel-heading" />
     <div class="table-responsive">
        <?php render_datatable(array(
            _l('jobreport_number'),
            _l('jobreport_company'),
            _l('jobreport_start_date'),
            ),'jobreports'); ?>
     </div>
    </div>
</div>
