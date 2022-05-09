<?php

use app\services\jobreports\JobreportsPipeline;

defined('BASEPATH') or exit('No direct script access allowed');

class Jobreports extends AdminController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('jobreports_model');
        $this->load->model('clients_model');
    }

    /* Get all jobreports in case user go on index page */
    public function index($id = '')
    {
        if (!has_permission('jobreports', '', 'view')) {
            access_denied('jobreports');
        }
        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('jobreports', 'admin/tables/table'));
        }
        $data['jobreportid']            = $id;
        $data['title']                 = _l('jobreports_tracking');
        $this->load->view('admin/jobreports/manage', $data);
    }


    /* Add new jobreport or update existing */
    public function jobreport($id)
    {

        $jobreport = $this->jobreports_model->get($id);

        if (!$jobreport || !user_can_view_jobreport($id)) {
            blank_page(_l('jobreport_not_found'));
        }

        $data['jobreport'] = $jobreport;
        $data['edit']     = false;
        $title            = _l('preview_jobreport');
    

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }

        $data['staff']             = $this->staff_model->get('', ['active' => 1]);
        $data['jobreport_statuses'] = $this->jobreports_model->get_statuses();
        $data['title']             = $title;

        $jobreport->date       = _d($jobreport->date);        
        
        if ($jobreport->project_id !== null) {
            $this->load->model('projects_model');
            $jobreport->project_data = $this->projects_model->get($jobreport->project_id);
        }

        //$data = jobreport_mail_preview_data($template_name, $jobreport->clientid);

        $data['jobreport_members'] = $this->jobreports_model->get_jobreport_members($id,true);

        //$data['jobreport_items']    = $this->jobreports_model->get_jobreport_item($id);

        $data['activity']          = $this->jobreports_model->get_jobreport_activity($id);
        $data['jobreport']          = $jobreport;
        $data['members']           = $this->staff_model->get('', ['active' => 1]);
        $data['jobreport_statuses'] = $this->jobreports_model->get_statuses();
        $data['totalNotes']        = total_rows(db_prefix() . 'notes', ['rel_id' => $id, 'rel_type' => 'jobreport']);

        $data['send_later'] = false;
        if ($this->session->has_userdata('send_later')) {
            $data['send_later'] = true;
            $this->session->unset_userdata('send_later');
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('jobreports', 'admin/tables/small_table'));
        }

        $this->load->view('admin/jobreports/jobreport_preview', $data);
    }


    /* Add new jobreport */
    public function create()
    {
        if ($this->input->post()) {

            $jobreport_data = $this->input->post();

            $save_and_send_later = false;
            if (isset($jobreport_data['save_and_send_later'])) {
                unset($jobreport_data['save_and_send_later']);
                $save_and_send_later = true;
            }

            if (!has_permission('jobreports', '', 'create')) {
                access_denied('jobreports');
            }

            $next_jobreport_number = get_option('next_jobreport_number');
            $_format = get_option('jobreport_number_format');
            $_prefix = get_option('jobreport_prefix');
            
            $prefix  = isset($jobreport->prefix) ? $jobreport->prefix : $_prefix;
            $format  = isset($jobreport->number_format) ? $jobreport->number_format : $_format;
            $number  = isset($jobreport->number) ? $jobreport->number : $next_jobreport_number;

            $date = date('Y-m-d');
            
            $jobreport_data['formatted_number'] = jobreport_number_format($number, $format, $prefix, $date);

            $id = $this->jobreports_model->add($jobreport_data);

            if ($id) {
                set_alert('success', _l('added_successfully', _l('jobreport')));

                $redUrl = admin_url('jobreports/jobreport/' . $id);

                if ($save_and_send_later) {
                    $this->session->set_userdata('send_later', true);
                    // die(redirect($redUrl));
                }

                redirect(
                    !$this->set_jobreport_pipeline_autoload($id) ? $redUrl : admin_url('jobreports/jobreport/')
                );
            }
        }
        $title = _l('create_new_jobreport');

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }
        /*
        $data['ajaxItems'] = false;
        if (total_rows(db_prefix() . 'items') <= ajax_on_total_items()) {
            $data['items'] = $this->items_model->get_grouped();
        } else {
            $data['items']     = [];
            $data['ajaxItems'] = true;
        }
        */

        $data['staff']             = $this->staff_model->get('', ['active' => 1]);
        $data['jobreport_statuses'] = $this->jobreports_model->get_statuses();
        $data['title']             = $title;

        $this->load->view('admin/jobreports/jobreport_create', $data);
    }

    /* update jobreport */
    public function update($id='')
    {
        if ($this->input->post()) {
            $jobreport_data = $this->input->post();

            $save_and_send_later = false;
            if (isset($jobreport_data['save_and_send_later'])) {
                unset($jobreport_data['save_and_send_later']);
                $save_and_send_later = true;
            }

            if (!has_permission('jobreports', '', 'edit')) {
                access_denied('jobreports');
            }
            $success = $this->jobreports_model->update($jobreport_data, $id);
            if ($success) {
                set_alert('success', _l('updated_successfully', _l('jobreport')));
            }
            
            if ($this->set_jobreport_pipeline_autoload($id)) {
                redirect(admin_url('jobreports/'));
            } else {
                redirect(admin_url('jobreports/jobreport/' . $id));
            }
        }

            $jobreport = $this->jobreports_model->get($id);

            if (!$jobreport || !user_can_view_jobreport($id)) {
                blank_page(_l('jobreport_not_found'));
            }

            $data['jobreport'] = $jobreport;
            $data['edit']     = true;
            $title            = _l('edit', _l('jobreport_lowercase'));
       

        if ($this->input->get('customer_id')) {
            $data['customer_id'] = $this->input->get('customer_id');
        }


        $data['jobreport_members']  = $this->jobreports_model->get_jobreport_members($id);
        //$data['jobreport_items']    = $this->jobreports_model->get_jobreport_item($id);


        $data['staff']             = $this->staff_model->get('', ['active' => 1]);
        $data['jobreport_statuses'] = $this->jobreports_model->get_statuses();
        $data['title']             = $title;
        $this->load->view('admin/jobreports/jobreport_update', $data);
    }

    public function update_number_settings($id)
    {
        $response = [
            'success' => false,
            'message' => '',
        ];
        if (has_permission('jobreports', '', 'edit')) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'jobreports', [
                'prefix' => $this->input->post('prefix'),
            ]);
            if ($this->db->affected_rows() > 0) {
                $response['success'] = true;
                $response['message'] = _l('updated_successfully', _l('jobreport'));
            }
        }

        echo json_encode($response);
        die;
    }

    public function validate_jobreport_number()
    {
        $isedit          = $this->input->post('isedit');
        $number          = $this->input->post('number');
        $date            = $this->input->post('date');
        $original_number = $this->input->post('original_number');
        $number          = trim($number);
        $number          = ltrim($number, '0');

        if ($isedit == 'true') {
            if ($number == $original_number) {
                echo json_encode(true);
                die;
            }
        }

        if (total_rows(db_prefix() . 'jobreports', [
            'YEAR(date)' => date('Y', strtotime(to_sql_date($date))),
            'number' => $number,
        ]) > 0) {
            echo 'false';
        } else {
            echo 'true';
        }
    }


    public function add_note($rel_id)
    {
        if ($this->input->post() && user_can_view_jobreport($rel_id)) {
            $this->misc_model->add_note($this->input->post(), 'jobreport', $rel_id);
            echo $rel_id;
        }
    }

    public function get_notes($id)
    {
        if (user_can_view_jobreport($id)) {
            $data['notes'] = $this->misc_model->get_notes($id, 'jobreport');
            $this->load->view('admin/includes/sales_notes_template', $data);
        }
    }

    public function mark_action_status($status, $id)
    {
        if (!has_permission('jobreports', '', 'edit')) {
            access_denied('jobreports');
        }
        $success = $this->jobreports_model->mark_action_status($status, $id);
        if ($success) {
            set_alert('success', _l('jobreport_status_changed_success'));
        } else {
            set_alert('danger', _l('jobreport_status_changed_fail'));
        }
        if ($this->set_jobreport_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('jobreports/jobreport/' . $id));
        }
    }


    public function set_jobreport_pipeline_autoload($id)
    {
        if ($id == '') {
            return false;
        }

        if ($this->session->has_userdata('jobreport_pipeline')
                && $this->session->userdata('jobreport_pipeline') == 'true') {
            $this->session->set_flashdata('jobreportid', $id);

            return true;
        }

        return false;
    }

    public function copy($id)
    {
        if (!has_permission('jobreports', '', 'create')) {
            access_denied('jobreports');
        }
        if (!$id) {
            die('No jobreport found');
        }
        $new_id = $this->jobreports_model->copy($id);
        if ($new_id) {
            set_alert('success', _l('jobreport_copied_successfully'));
            if ($this->set_jobreport_pipeline_autoload($new_id)) {
                redirect($_SERVER['HTTP_REFERER']);
            } else {
                redirect(admin_url('jobreports/jobreport/' . $new_id));
            }
        }
        set_alert('danger', _l('jobreport_copied_fail'));
        if ($this->set_jobreport_pipeline_autoload($id)) {
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            redirect(admin_url('jobreports/jobreport/' . $id));
        }
    }

    /* Delete jobreport */
    public function delete($id)
    {
        if (!has_permission('jobreports', '', 'delete')) {
            access_denied('jobreports');
        }
        if (!$id) {
            redirect(admin_url('jobreports'));
        }
        $success = $this->jobreports_model->delete($id);
        if (is_array($success)) {
            set_alert('warning', _l('is_invoiced_jobreport_delete_error'));
        } elseif ($success == true) {
            set_alert('success', _l('deleted', _l('jobreport')));
        } else {
            set_alert('warning', _l('problem_deleting', _l('jobreport_lowercase')));
        }
        redirect(admin_url('jobreports'));
    }
    
    /* Used in kanban when dragging and mark as */
    public function update_jobreport_status()
    {
        if ($this->input->post() && $this->input->is_ajax_request()) {
            $this->jobreports_model->update_jobreport_status($this->input->post());
        }
    }

    public function clear_signature($id)
    {
        if (has_permission('jobreports', '', 'delete')) {
            $this->jobreports_model->clear_signature($id);
        }

        redirect(admin_url('jobreports/jobreport/' . $id));
    }

    /* Generates jobreport PDF and senting to email  */
    public function pdf($id)
    {
        $canView = user_can_view_jobreport($id);
        if (!$canView) {
            access_denied('Schedules');
        } else {
            if (!has_permission('jobreports', '', 'view') && !has_permission('jobreports', '', 'view_own') && $canView == false) {
                access_denied('Schedules');
            }
        }
        if (!$id) {
            redirect(admin_url('jobreports'));
        }
        $jobreport        = $this->jobreports_model->get($id);
        $jobreport_number = format_jobreport_number($jobreport->id);
        
        $jobreport->assigned_path = FCPATH . get_jobreport_upload_path('jobreport').$jobreport->id.'/assigned-'.$jobreport_number.'.png';
        $jobreport->acceptance_path = FCPATH . get_jobreport_upload_path('jobreport').$jobreport->id.'/signature.png';
        $jobreport->client_company = $this->clients_model->get($jobreport->clientid)->company;
        $jobreport->acceptance_date_string = _dt($jobreport->acceptance_date);


        try {
            $pdf = jobreport_pdf($jobreport);
        } catch (Exception $e) {
            $message = $e->getMessage();
            echo $message;
            if (strpos($message, 'Unable to get the size of the image') !== false) {
                show_pdf_unable_to_get_image_size_error();
            }
            die;
        }

        $type = 'D';

        if ($this->input->get('output_type')) {
            $type = $this->input->get('output_type');
        }

        if ($this->input->get('print')) {
            $type = 'I';
        }

        $fileNameHookData = hooks()->apply_filters('jobreport_file_name_admin_area', [
                            'file_name' => mb_strtoupper(slug_it($jobreport_number)) . '.pdf',
                            'jobreport'  => $jobreport,
                        ]);

        $pdf->Output($fileNameHookData['file_name'], $type);
    }
}