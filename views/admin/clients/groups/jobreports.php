<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<?php if(isset($client)){ ?>
	<h4 class="customer-profile-group-heading"><?php echo _l('jobreports'); ?></h4>
	<?php if(has_permission('jobreports','','create')){ ?>
		<a href="<?php echo admin_url('jobreports/jobreport?customer_id='.$client->userid); ?>" class="btn btn-info mbot15<?php if($client->active == 0){echo ' disabled';} ?>"><?php echo _l('create_new_jobreport'); ?></a>
	<?php } ?>
	<?php if(has_permission('jobreports','','view') || has_permission('jobreports','','view_own') || get_option('allow_staff_view_jobreports_assigned') == '1'){ ?>
		<a href="#" class="btn btn-info mbot15" data-toggle="modal" data-target="#client_zip_jobreports"><?php echo _l('zip_jobreports'); ?></a>
	<?php } ?>
	<div id="jobreports_total"></div>
	<?php
	$this->load->view('admin/jobreports/table_html', array('class'=>'jobreports-single-client'));
	//$this->load->view('admin/clients/modals/zip_jobreports');
	?>
<?php } ?>
