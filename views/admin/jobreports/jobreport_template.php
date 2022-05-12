<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<div class="panel_s accounting-template jobreport">
   <div class="panel-body">
      <?php if(isset($jobreport)){ ?>
      <?php echo format_jobreport_status($jobreport->status); ?>
      <hr class="hr-panel-heading" />
      <?php } ?>
      <div class="row">
          <?php if (isset($jobreport_request_id) && $jobreport_request_id != '') {
              echo form_hidden('jobreport_request_id',$jobreport_request_id);
          }
          ?>
         <div class="col-md-6">
            <div class="f_client_id">
             <div class="form-group select-placeholder">
                <label for="clientid" class="control-label"><?php echo _l('jobreport_select_customer'); ?></label>
                <select id="clientid" name="clientid" data-live-search="true" data-width="100%" class="ajax-search<?php if(isset($jobreport) && empty($jobreport->clientid)){echo ' customer-removed';} ?>" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
               <?php $selected = (isset($jobreport) ? $jobreport->clientid : '');
                 if($selected == ''){
                   $selected = (isset($customer_id) ? $customer_id: '');
                 }
                 if($selected != ''){
                    $rel_data = get_relation_data('customer',$selected);
                    $rel_val = get_relation_values($rel_data,'customer');
                    echo '<option value="'.$rel_val['id'].'" selected>'.$rel_val['name'].'</option>';
                 } ?>
                </select>
              </div>
            </div>
            <div class="form-group select-placeholder projects-wrapper<?php if((!isset($jobreport)) || (isset($jobreport) && !customer_has_projects($jobreport->clientid))){ echo ' hide';} ?>">
             <label for="project_id"><?php echo _l('project'); ?></label>
             <div id="project_ajax_search_wrapper">
               <select name="project_id" id="project_id" class="projects ajax-search" data-live-search="true" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                <?php
                  if(isset($jobreport) && $jobreport->project_id != 0){
                    echo '<option value="'.$jobreport->project_id.'" selected>'.get_project_name_by_id($jobreport->project_id).'</option>';
                  }
                ?>
              </select>
            </div>
           </div>
            <div class="row">
               <div class="col-md-12">
                  <a href="#" class="edit_shipping_billing_info" data-toggle="modal" data-target="#billing_and_shipping_details"><i class="fa fa-pencil-square-o"></i></a>
                  <?php include_once(module_views_path('jobreports','admin/jobreports/billing_and_shipping_template.php')); ?>
               </div>
               <div class="col-md-6">
                  <p class="bold"><?php echo _l('bill_to'); ?></p>
                  <address>
                     <span class="billing_street">
                     <?php $billing_street = (isset($jobreport) ? $jobreport->billing_street : '--'); ?>
                     <?php $billing_street = ($billing_street == '' ? '--' :$billing_street); ?>
                     <?php echo $billing_street; ?></span><br>
                     <span class="billing_city">
                     <?php $billing_city = (isset($jobreport) ? $jobreport->billing_city : '--'); ?>
                     <?php $billing_city = ($billing_city == '' ? '--' :$billing_city); ?>
                     <?php echo $billing_city; ?></span>,
                     <span class="billing_state">
                     <?php $billing_state = (isset($jobreport) ? $jobreport->billing_state : '--'); ?>
                     <?php $billing_state = ($billing_state == '' ? '--' :$billing_state); ?>
                     <?php echo $billing_state; ?></span>
                     <br/>
                     <span class="billing_country">
                     <?php $billing_country = (isset($jobreport) ? get_country_short_name($jobreport->billing_country) : '--'); ?>
                     <?php $billing_country = ($billing_country == '' ? '--' :$billing_country); ?>
                     <?php echo $billing_country; ?></span>,
                     <span class="billing_zip">
                     <?php $billing_zip = (isset($jobreport) ? $jobreport->billing_zip : '--'); ?>
                     <?php $billing_zip = ($billing_zip == '' ? '--' :$billing_zip); ?>
                     <?php echo $billing_zip; ?></span>
                  </address>
               </div>
               <div class="col-md-6">
                  <p class="bold"><?php echo _l('ship_to'); ?></p>
                  <address>
                     <span class="shipping_street">
                     <?php $shipping_street = (isset($jobreport) ? $jobreport->shipping_street : '--'); ?>
                     <?php $shipping_street = ($shipping_street == '' ? '--' :$shipping_street); ?>
                     <?php echo $shipping_street; ?></span><br>
                     <span class="shipping_city">
                     <?php $shipping_city = (isset($jobreport) ? $jobreport->shipping_city : '--'); ?>
                     <?php $shipping_city = ($shipping_city == '' ? '--' :$shipping_city); ?>
                     <?php echo $shipping_city; ?></span>,
                     <span class="shipping_state">
                     <?php $shipping_state = (isset($jobreport) ? $jobreport->shipping_state : '--'); ?>
                     <?php $shipping_state = ($shipping_state == '' ? '--' :$shipping_state); ?>
                     <?php echo $shipping_state; ?></span>
                     <br/>
                     <span class="shipping_country">
                     <?php $shipping_country = (isset($jobreport) ? get_country_short_name($jobreport->shipping_country) : '--'); ?>
                     <?php $shipping_country = ($shipping_country == '' ? '--' :$shipping_country); ?>
                     <?php echo $shipping_country; ?></span>,
                     <span class="shipping_zip">
                     <?php $shipping_zip = (isset($jobreport) ? $jobreport->shipping_zip : '--'); ?>
                     <?php $shipping_zip = ($shipping_zip == '' ? '--' :$shipping_zip); ?>
                     <?php echo $shipping_zip; ?></span>
                  </address>
               </div>
            </div>
            <?php
               $next_jobreport_number = get_option('next_jobreport_number');
               $format = get_option('jobreport_number_format');
               
                if(isset($jobreport)){
                  $format = $jobreport->number_format;
                }

               $prefix = get_option('jobreport_prefix');

               if ($format == 1) {
                 $__number = $next_jobreport_number;
                 if(isset($jobreport)){
                   $__number = $jobreport->number;
                   $prefix = '<span id="prefix">' . $jobreport->prefix . '</span>';
                 }
               } else if($format == 2) {
                 if(isset($jobreport)){
                   $__number = $jobreport->number;
                   $prefix = $jobreport->prefix;
                   $prefix = '<span id="prefix">'. $prefix . '</span><span id="prefix_year">' . date('Y',strtotime($jobreport->date)).'</span>/';
                 } else {
                   $__number = $next_jobreport_number;
                   $prefix = $prefix.'<span id="prefix_year">'.date('Y').'</span>/';
                 }
               } else if($format == 3) {
                  if(isset($jobreport)){
                   $yy = date('y',strtotime($jobreport->date));
                   $__number = $jobreport->number;
                   $prefix = '<span id="prefix">'. $jobreport->prefix . '</span>';
                 } else {
                  $yy = date('y');
                  $__number = $next_jobreport_number;
                }
               } else if($format == 4) {
                  if(isset($jobreport)){
                   $yyyy = date('Y',strtotime($jobreport->date));
                   $mm = date('m',strtotime($jobreport->date));
                   $__number = $jobreport->number;
                   $prefix = '<span id="prefix">'. $jobreport->prefix . '</span>';
                 } else {
                  $yyyy = date('Y');
                  $mm = date('m');
                  $__number = $next_jobreport_number;
                }
               }
               
               $_jobreport_number = str_pad($__number, get_option('number_padding_prefixes'), '0', STR_PAD_LEFT);
               $isedit = isset($jobreport) ? 'true' : 'false';
               $data_original_number = isset($jobreport) ? $jobreport->number : 'false';
               ?>
            <div class="form-group">
               <label for="number"><?php echo _l('jobreport_add_edit_number'); ?></label>
               <div class="input-group">
                  <span class="input-group-addon">
                  <?php if(isset($jobreport)){ ?>
                  <a href="#" onclick="return false;" data-toggle="popover" data-container='._transaction_form' data-html="true" data-content="<label class='control-label'><?php echo _l('settings_sales_jobreport_prefix'); ?></label><div class='input-group'><input name='s_prefix' type='text' class='form-control' value='<?php echo $jobreport->prefix; ?>'></div><button type='button' onclick='save_sales_number_settings(this); return false;' data-url='<?php echo admin_url('jobreports/update_number_settings/'.$jobreport->id); ?>' class='btn btn-info btn-block mtop15'><?php echo _l('submit'); ?></button>"><i class="fa fa-cog"></i></a>
                   <?php }
                    echo $prefix;
                  ?>
                  </span>
                  <input type="text" name="number" class="form-control" value="<?php echo $_jobreport_number; ?>" data-isedit="<?php echo $isedit; ?>" data-original-number="<?php echo $data_original_number; ?>">
                  <?php if($format == 3) { ?>
                  <span class="input-group-addon">
                     <span id="prefix_year" class="format-n-yy"><?php echo $yy; ?></span>
                  </span>
                  <?php } else if($format == 4) { ?>
                   <span class="input-group-addon">
                     <span id="prefix_month" class="format-mm-yyyy"><?php echo $mm; ?></span>
                     .
                     <span id="prefix_year" class="format-mm-yyyy"><?php echo $yyyy; ?></span>
                  </span>
                  <?php } ?>
               </div>
            </div>

            <div class="row">
               <div class="col-md-6">
                  <?php $value = (isset($jobreport) ? _d($jobreport->date) : _d(date('Y-m-d'))); ?>
                  <?php echo render_date_input('date','jobreport_add_edit_date',$value); ?>
               </div>
               <div class="col-md-6">
                  
               </div>
            </div>
            <div class="clearfix mbot15"></div>
            <?php $rel_id = (isset($jobreport) ? $jobreport->id : false); ?>
            <?php
                  if(isset($custom_fields_rel_transfer)) {
                      $rel_id = $custom_fields_rel_transfer;
                  }
             ?>
            <?php echo render_custom_fields('jobreport',$rel_id); ?>
         </div>
         <div class="col-md-6">
            <div class="panel_s no-shadow">
               <div class="form-group">
                  <label for="tags" class="control-label"><i class="fa fa-tag" aria-hidden="true"></i> <?php echo _l('tags'); ?></label>
                  <input type="text" class="tagsinput" id="tags" name="tags" value="<?php echo (isset($jobreport) ? prep_tags_input(get_tags_in($jobreport->id,'jobreport')) : ''); ?>" data-role="tagsinput">
               </div>
               <div class="row">
                   <div class="col-md-6">
                     <div class="form-group select-placeholder">
                        <label class="control-label"><?php echo _l('jobreport_status'); ?></label>
                        <select class="selectpicker display-block mbot15" name="status" data-width="100%" data-none-selected-text="<?php echo _l('dropdown_non_selected_tex'); ?>">
                           <?php foreach($jobreport_statuses as $status){ ?>
                           <option value="<?php echo $status; ?>" <?php if(isset($jobreport) && $jobreport->status == $status){echo 'selected';} ?>><?php echo format_jobreport_status($status,'',false); ?></option>
                           <?php } ?>
                        </select>
                     </div>
                  </div>

                  <div class="col-md-6">
                    <?php $value = (isset($jobreport) ? $jobreport->reference_no : ''); ?>
                    <?php echo render_input('reference_no','reference_no',$value); ?>
                  </div>
                  <div class="col-md-6">
                         <?php
                        $selected = get_option('default_jobreport_assigned');
                        foreach($staff as $member){
                         if(isset($jobreport)){
                           if($jobreport->assigned == $member['staffid']) {
                             $selected = $member['staffid'];
                           }
                         }
                        }
                        echo render_select('assigned',$staff,array('staffid',array('firstname','lastname')),'jobreport_assigned_string',$selected);
                        ?>
                  </div>
               </div>
               <?php $value = (isset($jobreport) ? $jobreport->adminnote : ''); ?>
               <?php echo render_textarea('adminnote','jobreport_add_edit_admin_note',$value); ?>

            </div>
         </div>
      </div>
   </div>
   <?php $this->load->view('admin/jobreports/_add_edit_items'); ?>
   <div class="row">
      <div class="col-md-12 mtop15">
         <div class="panel-body bottom-transaction">
           <?php $value = (isset($jobreport) ? $jobreport->clientnote : get_option('predefined_clientnote_jobreport')); ?>
           <?php echo render_textarea('clientnote','jobreport_add_edit_client_note',$value,array(),array(),'mtop15'); ?>
           <?php $value = (isset($jobreport) ? $jobreport->terms : get_option('predefined_terms_jobreport')); ?>
           <?php echo render_textarea('terms','terms_and_conditions',$value,array(),array(),'mtop15'); ?>
         </div>
      </div>
      <div class='clearfix'></div>
      <div id="footer" class="col-md-12">
         <div class="col-md-8">
         </div>  
         <div class="col-md-2">
            <div class="bottom-tollbar">
               <div class="btn-group dropup">
                  <button type="button" class="btn-tr btn btn-info jobreport-form-submit transaction-submit">Save</button>
                  <button type="button" class="btn btn-info dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                     <span class="caret"></span>
                  </button>
                  <ul class="dropdown-menu dropdown-menu-right width200">
                     <li>
                        <a href="#" class="jobreport-form-submit save-and-send transaction-submit"><?php echo _l('submit'); ?></a>
                     </li>
                     <li>
                        <a href="#" class="jobreport-form-submit save-and-send-later transaction-submit"><?php echo _l('save_and_send_later'); ?></a>
                     </li>
                  </ul>
               </div>
            </div>
         </div>
      </div>
      
   </div>

 <div class="btn-bottom-pusher"></div>


</div>
