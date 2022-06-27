<?php

defined('BASEPATH') or exit('No direct script access allowed');

include_once(LIBSPATH . 'pdf/App_pdf.php');

class Jobreport_tags_pdf extends App_pdf
{
    protected $jobreport;

    private $jobreport_number;

    public function __construct($jobreport, $tag = '')
    {
        $this->load_language($jobreport->clientid);

        $jobreport                = hooks()->apply_filters('jobreport_html_pdf_data', $jobreport);
        $GLOBALS['jobreport_pdf'] = $jobreport;

        parent::__construct();

        $this->tag             = $tag;
        $this->jobreport        = $jobreport;
        $this->jobreport_number = format_jobreport_number($this->jobreport->id);

        $this->SetTitle($this->jobreport_number);
    }

    public function prepare()
    {

        $this->set_view_vars([
            'status'          => $this->jobreport->status,
            'jobreport_number' => $this->jobreport_number,
            'jobreport'        => $this->jobreport,
        ]);

        return $this->build();
    }

    protected function type()
    {
        return 'jobreport';
    }

    protected function file_path()
    {
        $customPath = APPPATH . 'views/themes/' . active_clients_theme() . '/views/my_jobreport_tags_pdf.php';
        $actualPath = module_views_path('jobreports','themes/' . active_clients_theme() . '/views/jobreports/jobreport_tags_pdf.php');

        if (file_exists($customPath)) {
            $actualPath = $customPath;
        }

        return $actualPath;
    }
}
