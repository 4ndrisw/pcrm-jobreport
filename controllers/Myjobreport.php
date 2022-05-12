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

        if ($this->input->post('jobreport_action')) {
            $action = $this->input->post('jobreport_action');

            // Only decline and accept allowed
            if ($action == 4 || $action == 3) {
                $success = $this->jobreports_model->mark_action_status($action, $id, true);

                $redURL   = $this->uri->uri_string();
                $accepted = false;

                if (is_array($success)) {
                    if ($action == 4) {
                        $accepted = true;
                        set_alert('success', _l('clients_jobreport_accepted_not_invoiced'));
                    } else {
                        set_alert('success', _l('clients_jobreport_declined'));
                    }
                } else {
                    set_alert('warning', _l('clients_jobreport_failed_action'));
                }
                if ($action == 4 && $accepted = true) {
                    process_digital_signature_image($this->input->post('signature', false), JOBREPORT_ATTACHMENTS_FOLDER . $id);

                    $this->db->where('id', $id);
                    $this->db->update(db_prefix() . 'jobreports', get_acceptance_info_array());
                }
            }
            redirect($redURL);
        }
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
        $this->load->library('app_number_to_word', [
            'clientid' => $jobreport->clientid,
        ], 'numberword');

        $this->app_scripts->theme('sticky-js', 'assets/plugins/sticky/sticky.js');


        $data['title'] = $jobreport_number;
        $this->disableNavigation();
        $this->disableSubMenu();

        $data['jobreport_number']              = $jobreport_number;
        $data['hash']                          = $hash;
        $data['can_be_accepted']               = false;
        $data['jobreport']                     = hooks()->apply_filters('jobreport_html_pdf_data', $jobreport);
        $data['bodyclass']                     = 'viewjobreport';
        $data['client_company']                = $this->clients_model->get($jobreport->clientid)->company;

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
        $params['setSize'] = 160;
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
        //$this->view('jobreporthtml');
        $this->view('themes/'. active_clients_theme() .'/views/jobreports/jobreporthtml');
        add_views_tracking('jobreport', $id);
        hooks()->do_action('jobreport_html_viewed', $id);
        no_index_customers_area();
        $this->layout();
    }


}
