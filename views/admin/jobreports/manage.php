<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
    <div class="content">
        <div class="row">
            <div class="col-md-12">
                <div class="panel_s">
                    <div class="panel-body">
                     <?php if(has_permission('jobreports','','create')){ ?>

                     <div class="_buttons">
                        <a href="<?php echo admin_url('jobreports/create'); ?>" class="btn btn-info pull-left display-block"><?php echo _l('new_jobreport'); ?></a>
                    </div>
                    <div class="clearfix"></div>
                    <hr class="hr-panel-heading" />
                    <?php } ?>
                    <?php render_datatable(array(
                        _l('jobreport_number'),
                        _l('jobreport_company'),
                        _l('jobreport_list_project'),
                        //_l('jobreport_projects_name'),
                        _l('jobreport_status'),
                        _l('jobreport_start_date'),
                        //_l('jobreport_acceptance_name'),
                        _l('jobreport_acceptance_date'),
                        //_l('jobreport_end_date'),
                        ),'jobreports'); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php init_tail(); ?>
<script type="text/javascript" id="jobreport-js" src="<?= base_url() ?>modules/jobreports/assets/js/jobreports.js?"></script>
<script>
    $(function(){
        initDataTable('.table-jobreports', window.location.href, 'undefined', 'undefined','fnServerParams', [0, 'desc']);
    });
</script>
</body>
</html>
