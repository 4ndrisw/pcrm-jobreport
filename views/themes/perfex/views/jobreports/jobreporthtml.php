<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="mtop15 preview-top-wrapper">
   <div class="row">
      <div class="col-md-3">
         <div class="mbot30">
            <div class="jobreport-html-logo">
               <?php echo get_dark_company_logo(); ?>
            </div>
         </div>
      </div>
      <div class="clearfix"></div>
   </div>
   <div class="top" data-sticky data-sticky-class="preview-sticky-header">
      <div class="container preview-sticky-container">
         <div class="row">
            <div class="col-md-12">
               <div class="pull-left">
                  <h3 class="bold no-mtop jobreport-html-number no-mbot">
                     <span class="sticky-visible hide">
                     <?php echo format_jobreport_number($jobreport->id); ?>
                     </span>
                  </h3>
                  <h4 class="jobreport-html-status mtop7">
                     <?php echo format_jobreport_status($jobreport->status,'',true); ?>
                  </h4>
               </div>
               <div class="visible-xs">
                  <div class="clearfix"></div>
               </div>
               <?php
                  // Is not accepted, declined and expired
                  if ($jobreport->status != 4 && $jobreport->status != 3 && $jobreport->status != 5) {
                    $can_be_accepted = true;
                    if($identity_confirmation_enabled == '0'){
                      echo form_open($this->uri->uri_string(), array('class'=>'pull-right mtop7 action-button'));
                      echo form_hidden('jobreport_action', 4);
                      echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-success action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_jobreport').'</button>';
                      echo form_close();
                    } else {
                      echo '<button type="button" id="accept_action" class="btn btn-success mright5 mtop7 pull-right action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_jobreport').'</button>';
                    }
                  } else if($jobreport->status == 3){
                      $can_be_accepted = true;
                      if($identity_confirmation_enabled == '0'){
                        echo form_open($this->uri->uri_string(),array('class'=>'pull-right mtop7 action-button'));
                        echo form_hidden('jobreport_action', 4);
                        echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-success action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_jobreport').'</button>';
                        echo form_close();
                      } else {
                        echo '<button type="button" id="accept_action" class="btn btn-success mright5 mtop7 pull-right action-button accept"><i class="fa fa-check"></i> '._l('clients_accept_jobreport').'</button>';
                      }
                  }
                  // Is not accepted, declined and expired
                  if ($jobreport->status != 4 && $jobreport->status != 3 && $jobreport->status != 5) {
                    echo form_open($this->uri->uri_string(), array('class'=>'pull-right action-button mright5 mtop7'));
                    echo form_hidden('jobreport_action', 3);
                    echo '<button type="submit" data-loading-text="'._l('wait_text').'" autocomplete="off" class="btn btn-default action-button accept"><i class="fa fa-remove"></i> '._l('clients_decline_jobreport').'</button>';
                    echo form_close();
                  }
                  ?>
               <?php echo form_open(site_url('jobreports/pdf/'.$jobreport->id), array('class'=>'pull-right action-button')); ?>
               <button type="submit" name="jobreportpdf" class="btn btn-default action-button download mright5 mtop7" value="jobreportpdf">
               <i class="fa fa-file-pdf-o"></i>
               <?php echo _l('clients_html_btn_download'); ?>
               </button>
               <?php echo form_close(); ?>
               <?php if(is_client_logged_in() && has_contact_permission('jobreports')){ ?>
               <a href="<?php echo site_url('jobreports/list'); ?>" class="btn btn-default pull-right mright5 mtop7 action-button go-to-portal">
               <?php echo _l('client_go_to_dashboard'); ?>
               </a>
               <?php } ?>
               <div class="clearfix"></div>
            </div>
         </div>
      </div>
   </div>
</div>
<div class="clearfix"></div>
<div class="panel_s mtop20">
   <div class="panel-body">
      <div class="col-md-10 col-md-offset-1">
         <div class="row mtop20">
            <div class="col-md-6 col-sm-6 transaction-html-info-col-left">
               <h4 class="bold jobreport-html-number"><?php echo format_jobreport_number($jobreport->id); ?></h4>
               <address class="jobreport-html-company-info">
                  <?php echo format_organization_info(); ?>
               </address>
            </div>
            <div class="col-sm-6 text-right transaction-html-info-col-right">
               <span class="bold jobreport_to"><?php echo _l('jobreport_to'); ?>:</span>
               <address class="jobreport-html-customer-billing-info">
                  <?php echo format_customer_info($jobreport, 'jobreport', 'billing'); ?>
               </address>
               <!-- shipping details -->
               <?php if($jobreport->include_shipping == 1 && $jobreport->show_shipping_on_jobreport == 1){ ?>
               <span class="bold jobreport_ship_to"><?php echo _l('ship_to'); ?>:</span>
               <address class="jobreport-html-customer-shipping-info">
                  <?php echo format_customer_info($jobreport, 'jobreport', 'shipping'); ?>
               </address>
               <?php } ?>
            </div>
         </div>
         <div class="row">
            <div class="col-md-6">
               <div class="container-fluid">

               </div>
            </div>
            <div class="col-md-6 text-right">
               <p class="no-mbot jobreport-html-date">
                  <span class="bold">
                  <?php echo _l('jobreport_data_date'); ?>:
                  </span>
                  <?php echo _d($jobreport->date); ?>
               </p>
               <?php if(!empty($jobreport->reference_no)){ ?>
               <p class="no-mbot jobreport-html-reference-no">
                  <span class="bold"><?php echo _l('reference_no'); ?>:</span>
                  <?php echo $jobreport->reference_no; ?>
               </p>
               <?php } ?>
               <?php if($jobreport->project_id != 0 && get_option('show_project_on_jobreport') == 1){ ?>
               <p class="no-mbot jobreport-html-project">
                  <span class="bold"><?php echo _l('project'); ?>:</span>
                  <?php echo get_project_name_by_id($jobreport->project_id); ?>
               </p>
               <?php } ?>
               <?php $pdf_custom_fields = get_custom_fields('jobreport',array('show_on_pdf'=>1,'show_on_client_portal'=>1));
                  foreach($pdf_custom_fields as $field){
                    $value = get_custom_field_value($jobreport->id,$field['id'],'jobreport');
                    if($value == ''){continue;} ?>
               <p class="no-mbot">
                  <span class="bold"><?php echo $field['name']; ?>: </span>
                  <?php echo $value; ?>
               </p>
               <?php } ?>
            </div>
         </div>
         <div class="row">
         
            <?php 
               $status = format_jobreport_status($jobreport->status,'',true);
               echo _l('task_mark_as',$status, '. and .'); 
            ?>
         </div>
         

         <div class="row">
            <div class="col-md-12">
               <div class="table-responsive">
                  <?php
                     $this->view('themes/'. active_clients_theme() .'/template_parts/jobreport_items_table');

                     $items = get_jobreport_items_table_data($jobreport, 'jobreport');
                     echo $items->table();
                  ?>
               </div>
            </div>


            <div class="row mtop25">
               <div class="col-md-12">
                  <div class="col-md-6 text-center">
                     <div class="bold"><?php echo get_option('invoice_company_name'); ?></div>
                     <div class="qrcode text-center">
                        <img src="<?php echo site_url('download/preview_image?path='.protected_file_url_by_path(get_jobreport_upload_path('jobreport').$jobreport->id.'/assigned-'.$jobreport_number.'.png')); ?>" class="img-responsive center-block jobreport-assigned" alt="jobreport-<?= $jobreport->id ?>">
                     </div>
                     <div class="assigned">
                     <?php if($jobreport->assigned != 0 && get_option('show_assigned_on_jobreports') == 1){ ?>
                        <?php echo get_staff_full_name($jobreport->assigned); ?>
                     <?php } ?>

                     </div>
                  </div>
                     <div class="col-md-6 text-center">
                       <div class="bold"><?php echo $client_company; ?></div>
                       <?php if(!empty($jobreport->signature)) { ?>
                           <div class="bold">
                              <p class="no-mbot"><?php echo _l('jobreport_signed_by') . ": {$jobreport->acceptance_firstname} {$jobreport->acceptance_lastname}"?></p>
                              <p class="no-mbot"><?php echo _l('jobreport_signed_date') . ': ' . _dt($jobreport->acceptance_date) ?></p>
                              <p class="no-mbot"><?php echo _l('jobreport_signed_ip') . ": {$jobreport->acceptance_ip}"?></p>
                           </div>
                           <p class="bold"><?php echo _l('document_customer_signature_text'); ?>
                           <?php if($jobreport->signed == 1 && has_permission('jobreports','','delete')){ ?>
                              <a href="<?php echo admin_url('jobreports/clear_signature/'.$jobreport->id); ?>" data-toggle="tooltip" title="<?php echo _l('clear_signature'); ?>" class="_delete text-danger">
                                 <i class="fa fa-remove"></i>
                              </a>
                           <?php } ?>
                           </p>
                           <div class="customer_signature text-center">
                              <img src="<?php echo site_url('download/preview_image?path='.protected_file_url_by_path(get_jobreport_upload_path('jobreport').$jobreport->id.'/'.$jobreport->signature)); ?>" class="img-responsive center-block jobreport-signature" alt="jobreport-<?= $jobreport->id ?>">
                           </div>
                       <?php } ?>
                     </div>
               </div>
            </div>


            <?php if(!empty($jobreport->clientnote)){ ?>
            <div class="col-md-12 jobreport-html-note">
            <hr />
               <b><?php echo _l('jobreport_order'); ?></b><br /><?php echo $jobreport->clientnote; ?>
            </div>
            <?php } ?>
            <?php if(!empty($jobreport->terms)){ ?>
            <div class="col-md-12 jobreport-html-terms-and-conditions">
               <b><?php echo _l('terms_and_conditions'); ?>:</b><br /><?php echo $jobreport->terms; ?>
            </div>
            <?php } ?>

         </div>
      </div>
   </div>
</div>
<?php
   if($identity_confirmation_enabled == '1' && $can_be_accepted){
    get_template_part('identity_confirmation_form',array('formData'=>form_hidden('jobreport_action',4)));
   }
   ?>
<script>
   $(function(){
     new Sticky('[data-sticky]');
   })
</script>
