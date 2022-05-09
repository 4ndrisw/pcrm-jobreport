<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!-- Zip Estimates -->
<div class="modal fade" id="client_zip_jobreports" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <?php echo form_open('admin/clients/zip_jobreports/'.$client->userid); ?>
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel"><?php echo _l('client_zip_jobreports'); ?></h4>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="jobreport_zip_status"><?php echo _l('client_zip_status'); ?></label>
                            <div class="radio radio-primary">
                                <input type="radio" value="all" id="all" checked name="jobreport_zip_status">
                                <label for="all"><?php echo _l('client_zip_status_all'); ?></label>
                            </div>
                            <?php foreach($jobreport_statuses as $status){ ?>
                            <div class="radio radio-primary">
                                <input type="radio" value="<?php echo $status; ?>" id="est_<?php echo $status; ?>" name="jobreport_zip_status">
                                <label for="est_<?php echo $status; ?>"><?php echo format_jobreport_status($status,'',false); ?></label>
                            </div>
                            <?php } ?>
                        </div>
                        <?php $this->load->view('admin/clients/modals/modal_zip_date_picker'); ?>
                        <?php echo form_hidden('file_name', $zip_in_folder); ?>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal"><?php echo _l('close'); ?></button>
                <button type="submit" class="btn btn-info"><?php echo _l('submit'); ?></button>
            </div>
            <?php echo form_close(); ?>
        </div>
    </div>
</div>
