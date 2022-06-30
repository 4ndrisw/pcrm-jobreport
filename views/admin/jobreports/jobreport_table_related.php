<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>

    <div class="panel_s">
        <div class="panel-body">
         <div class="clearfix"></div>
         <hr class="hr-panel-heading" />
         <div class="table-responsive">
            <?php render_datatable(array(
                _l('jobreport_task'),
                _l('jobreport_tag'),
                _l('jobreport_flag'),
                ),'jobreport-related'); ?>
         </div>
        </div>
    </div>
