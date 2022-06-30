<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content">
		<div class="row">
			<div class="col-md-6 no-padding">
				<?php 
			        if ($this->input->is_ajax_request()) {
			            $this->app->get_table_data(module_views_path('jobreports', 'admin/tables/table'));
			        }
					$this->load->view('admin/jobreports/jobreport_small_table'); 
				?>
			</div>
			<div class="col-md-6 no-padding jobreport-preview">
				<?php $this->load->view('admin/jobreports/jobreport_preview_template'); ?>
			</div>
		</div>
		<div class="row">
			<div class="no-padding jobreport-table-related">
				<?php $this->load->view('admin/jobreports/jobreport_table_related'); ?>
			</div>
		</div>


	</div>
</div>
<?php init_tail(); ?>
<script type="text/javascript" id="jobreport-js" src="<?= base_url() ?>modules/jobreports/assets/js/jobreports.js?"></script>

<script>
   init_items_sortable(true);
   init_btn_with_tooltips();
   init_datepicker();
   init_selectpicker();
   init_form_reminder();
   init_tabs_scrollable();
   <?php if($send_later) { ?>
      jobreport_jobreport_send(<?php echo $jobreport->id; ?>);
   <?php } ?>
</script>

<script>
    $(function(){
        initDataTable('.table-jobreports', window.location.href, 'undefined', 'undefined','fnServerParams', [0, 'desc']);
    });
</script>

<script>
    $(function(){
        initDataTable('.table-jobreport-items', admin_url+'jobreports/table_items', 'undefined', 'undefined','fnServerParams', [0, 'desc']);
    });
</script>
<script>
    $(function(){
        initDataTable('.table-jobreport-related', admin_url+'jobreports/table_related', 'undefined', 'undefined','fnServerParams', [0, 'desc']);
    });
</script>
</body>
</html>
