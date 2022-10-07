<?php defined('BASEPATH') or exit('No direct script access allowed');

class Myjobreport extends ClientsController
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('jobreports_model');
        $this->load->model('clients_model');
    }

    /* Get all jobreports in case user go on index page */
    public function list($id = '')
    {
        if (!is_client_logged_in() && !is_staff_logged_in()) {
            if (get_option('view_jobreport_only_logged_in') == 1) {
                redirect_after_login_to_current_url();
                redirect(site_url('authentication/login'));
            }
        }

        if ($this->input->is_ajax_request()) {
            $this->app->get_table_data(module_views_path('jobreports', 'admin/tables/table'));
        }
        $contact_id = get_contact_user_id();
        $user_id = get_user_id_by_contact_id($contact_id);
        $client = $this->clients_model->get($user_id);
        $data['jobreports'] = $this->jobreports_model->get_client_jobreports($client);
        $data['jobreportid']            = $id;
        $data['title']                 = _l('jobreports_tracking');

        $data['bodyclass'] = 'jobreports';
        $this->data($data);
        $this->view('themes/'. active_clients_theme() .'/views/jobreports/jobreports');
        $this->layout();
    }

    public function show($id, $hash)
    {
        check_jobreport_restrictions($id, $hash);
        $jobreport = $this->jobreports_model->get($id);

        if (!is_client_logged_in()) {
            load_client_language($jobreport->clientid);
        }

        $identity_confirmation_enabled = get_option('jobreport_accept_identity_confirmation');

        // Handle Jobreport PDF generator

        $jobreport_number = format_jobreport_number($jobreport->id);
        if ($this->input->post('jobreportpdf')) {
            try {
                $pdf = jobreport_pdf($jobreport);
            } catch (Exception $e) {
                echo $e->getMessage();
                die;
            }

            //$jobreport_number = format_jobreport_number($jobreport->id);
            $companyname     = get_option('company_name');
            if ($companyname != '') {
                $jobreport_number .= '-' . mb_strtoupper(slug_it($companyname), 'UTF-8');
            }

            $filename = hooks()->apply_filters('customers_area_download_jobreport_filename', mb_strtoupper(slug_it($jobreport_number), 'UTF-8') . '.pdf', $jobreport);

            $pdf->Output($filename, 'D');
            die();
        }

        $data['title'] = $jobreport_number;
        $this->disableNavigation();
        $this->disableSubMenu();

        $data['jobreport_items'] = $this->jobreports_model->get_jobreport_items($jobreport->id, $jobreport->project_id);
        //$data['jobreport_taggable_items'] = $this->jobreports_model->get_jobreport_taggable_items($jobreport->id, $jobreport->project_id);
        $project = get_project($jobreport->project_id);

        $contract = $this->jobreports_model->get_contract_by_project($project);

        if(count($contract)>0){
            $jobreport->contract = $contract[0];
        }
        $data['jobreport_number']              = $jobreport_number;
        $data['hash']                          = $hash;
        $data['can_be_accepted']               = false;
        $data['jobreport']                     = hooks()->apply_filters('jobreport_html_pdf_data', $jobreport);
        $data['bodyclass']                     = 'viewjobreport';
        $data['client_company']                = $this->clients_model->get($jobreport->clientid)->company;
        $setSize = get_option('jobreport_qrcode_size');
        $data['identity_confirmation_enabled'] = $identity_confirmation_enabled;
        if ($identity_confirmation_enabled == '1') {
            $data['bodyclass'] .= ' identity-confirmation';
        }

        $qrcode_data  = '';
        $qrcode_data .= _l('jobreport_number') . ' : ' . $jobreport_number ."\r\n";
        $qrcode_data .= _l('jobreport_date') . ' : ' . $jobreport->date ."\r\n";
        $qrcode_data .= _l('jobreport_datesend') . ' : ' . $jobreport->datesend ."\r\n";
        $qrcode_data .= _l('jobreport_assigned_string') . ' : ' . get_staff_full_name($jobreport->assigned) ."\r\n";
        $qrcode_data .= _l('jobreport_url') . ' : ' . site_url('jobreports/show/'. $jobreport->id .'/'.$jobreport->hash) ."\r\n";

        $jobreport_path = get_upload_path_by_type('jobreports') . $jobreport->id . '/';
        _maybe_create_upload_path('uploads/jobreports');
        _maybe_create_upload_path('uploads/jobreports/'.$jobreport_path);

        $params['data'] = $qrcode_data;
        $params['writer'] = 'png';
        $params['setSize'] = isset($setSize) ? $setSize : 160;
        $params['encoding'] = 'UTF-8';
        $params['setMargin'] = 0;
        $params['setForegroundColor'] = ['r'=>0,'g'=>0,'b'=>0];
        $params['setBackgroundColor'] = ['r'=>255,'g'=>255,'b'=>255];

        $params['crateLogo'] = true;
        $params['logo'] = './uploads/company/favicon.png';
        $params['setResizeToWidth'] = 60;

        $params['crateLabel'] = false;
        $params['label'] = $jobreport_number;
        $params['setTextColor'] = ['r'=>255,'g'=>0,'b'=>0];
        $params['ErrorCorrectionLevel'] = 'hight';

        $params['saveToFile'] = FCPATH.'uploads/jobreports/'.$jobreport_path .'assigned-'.$jobreport_number.'.'.$params['writer'];

        $this->load->library('endroid_qrcode');
        $this->endroid_qrcode->generate($params);

        $this->data($data);
        $this->app_scripts->theme('sticky-js', 'assets/plugins/sticky/sticky.js');
        $this->view('themes/'. active_clients_theme() .'/views/jobreports/jobreporthtml');
        add_views_tracking('jobreport', $id);
        hooks()->do_action('jobreport_html_viewed', $id);
        no_index_customers_area();
        $this->layout();
    }


    /* Generates jobreport PDF and senting to email  */
    public function pdf($id)
    {
        $canView = user_can_view_jobreport($id);
        if (!$canView) {
            access_denied('Jobreports');
        } else {
            if (!has_permission('jobreports', '', 'view') && !has_permission('jobreports', '', 'view_own') && $canView == false) {
                access_denied('Jobreports');
            }
        }
        if (!$id) {
            redirect(admin_url('jobreports'));
        }

        $jobreport        = $this->jobreports_model->get($id);
        $project = get_project($jobreport->project_id);
        $contract = $this->jobreports_model->get_contract_by_project($project);
        
        if(count($contract)>0){
            $jobreport->contract = $contract[0];
        }

        $jobreport_number = format_jobreport_number($jobreport->id);
        $jobreport->items = $this->jobreports_model->get_jobreport_items($jobreport->id, $jobreport->project_id);

        $jobreport->assigned_path = FCPATH . get_jobreport_upload_path('jobreport').$jobreport->id.'/assigned-'.$jobreport_number.'.png';
        $jobreport->acceptance_path = FCPATH . get_jobreport_upload_path('jobreport').$jobreport->id .'/'.$jobreport->signature;
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


    /* Generates jobreport PDF and senting to email  */
    public function taggable_pdf($id)
    {
        $canView = user_can_view_jobreport($id);
        if (!$canView) {
            access_denied('Jobreports');
        } else {
            if (!has_permission('jobreports', '', 'view') && !has_permission('jobreports', '', 'view_own') && $canView == false) {
                access_denied('Jobreports');
            }
        }
        if (!$id) {
            redirect(admin_url('jobreports'));
        }

        $jobreport        = $this->jobreports_model->get($id);
        $project = get_project($jobreport->project_id);
        $contract = $this->jobreports_model->get_contract_by_project($project);
        $jobreport->contract = $contract[0];
        $jobreport_number = format_jobreport_number($jobreport->id);
        $jobreport->items = $this->jobreports_model->get_jobreport_taggable_items($jobreport->id, $jobreport->project_id);

        $jobreport->assigned_path = FCPATH . get_jobreport_upload_path('jobreport').$jobreport->id.'/assigned-'.$jobreport_number.'.png';
        $jobreport->acceptance_path = FCPATH . get_jobreport_upload_path('jobreport').$jobreport->id .'/'.$jobreport->signature;
        $jobreport->client_company = $this->clients_model->get($jobreport->clientid)->company;
        $jobreport->acceptance_date_string = _dt($jobreport->acceptance_date);


        try {
            $pdf = jobreport_tags_pdf($jobreport);
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
