<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Jobreport_send_to_customer extends App_mail_template
{
    protected $for = 'customer';

    protected $jobreport;

    protected $contact;

    public $slug = 'jobreport-send-to-client';

    public $rel_type = 'jobreport';

    public function __construct($jobreport, $contact, $cc = '')
    {
        parent::__construct();

        $this->jobreport = $jobreport;
        $this->contact = $contact;
        $this->cc      = $cc;
    }

    public function build()
    {
        if ($this->ci->input->post('email_attachments')) {
            $_other_attachments = $this->ci->input->post('email_attachments');
            foreach ($_other_attachments as $attachment) {
                $_attachment = $this->ci->jobreports_model->get_attachments($this->jobreport->id, $attachment);
                $this->add_attachment([
                                'attachment' => get_upload_path_by_type('jobreport') . $this->jobreport->id . '/' . $_attachment->file_name,
                                'filename'   => $_attachment->file_name,
                                'type'       => $_attachment->filetype,
                                'read'       => true,
                            ]);
            }
        }

        $this->to($this->contact->email)
        ->set_rel_id($this->jobreport->id)
        ->set_merge_fields('client_merge_fields', $this->jobreport->clientid, $this->contact->id)
        ->set_merge_fields('jobreport_merge_fields', $this->jobreport->id);
    }
}
