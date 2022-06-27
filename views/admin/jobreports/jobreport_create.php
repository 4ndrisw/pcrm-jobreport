<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php init_head(); ?>
<div id="wrapper">
	<div class="content jobreport-add">
		<div class="row">
			<?php
			echo form_open($this->uri->uri_string(),array('id'=>'jobreport-form','class'=>'_transaction_form'));
			if(isset($jobreport)){
				echo form_hidden('isedit');
			}
			?>
			<div class="col-md-12">
				<?php $this->load->view('admin/jobreports/jobreport_template'); ?>
			</div>
			<?php echo form_close(); ?>
		</div>
	</div>
</div>
</div>
<?php init_tail(); ?>
<script type="text/javascript" src="/modules/jobreports/assets/js/jobreports.js"></script>
<script type="text/javascript">
	$(function(){
		validate_jobreport_form();
		// Project ajax search
		init_ajax_project_search_by_customer_id();
		// Maybe items ajax search
//	    init_ajax_search('items','#item_select.ajax-search',undefined,admin_url+'items/search');
	});
</script>
</body>
</html>
