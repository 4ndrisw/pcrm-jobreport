<?php

defined('BASEPATH') or exit('No direct script access allowed');

$dimensions = $pdf->getPageDimensions();

$info_right_column = '';
$info_left_column  = '';

$info_right_column .= '<span style="font-weight:bold;font-size:27px;">' . _l('jobreport_pdf_heading') . '</span><br />';
$info_right_column .= '<b style="color:#4e4e4e;"># ' . $jobreport_number . '</b>';

if (get_option('show_status_on_pdf_ei') == 1) {
    $info_right_column .= '<br /><span style="color:rgb(' . jobreport_status_color_pdf($status) . ');text-transform:uppercase;">' . format_jobreport_status($status, '', false) . '</span>';
}

// Add logo
$info_left_column .= pdf_logo_url();
// Write top left logo and right column info/text
pdf_multi_row($info_left_column, $info_right_column, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->ln(4);

$organization_info = '<div style="color:#424242;">';
    $organization_info .= format_organization_info();
$organization_info .= '</div>';

// Jobreport to
$jobreport_info = '<b>' . _l('jobreport_to') . '</b>';
$jobreport_info .= '<div style="color:#424242;">';
$jobreport_info .= format_customer_info($jobreport, 'jobreport', 'billing');
$jobreport_info .= '</div>';

$CI = &get_instance();
$CI->load->model('jobreports_model');
$jobreport_members = $CI->jobreports_model->get_jobreport_members($jobreport->id,true);

if (!empty($jobreport->reference_no)) {
    $jobreport_info .= _l('reference_no') . ': ' . $jobreport->reference_no . '<br />';
}

$left_info  = $swap == '1' ? $jobreport_info : $organization_info;
$right_info = $swap == '1' ? $organization_info : $jobreport_info;

pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);

$pdf->ln(4);
$project = get_project($jobreport->project_id);

$list = explode(' ',$project->name);
$project_name = $list[0];

$contract_date ='';
if(isset($jobreport->contract->datestart)){
    $contract_date_raw = isset($jobreport->contract->datestart) ? _d($jobreport->contract->datestart) : NULL;
    $tahun = getYear($contract_date_raw);
    $bulan = getMonth($contract_date_raw);
    $tanggal = getDay($contract_date_raw);
    $contract_date = $tanggal.' '.$bulan.' '.$tahun;
}


$date = $jobreport->date;
$today = _l('jobreport_today');
$jobreport_declare = _l('jobreport_declare');
$getDayName = getDayName($date);
$getDay = getDay($date);
$getMonth = getMonth($date);
$getYear = getYear($date);

$txt = <<<EOD
$today $getDayName $getDay $getMonth $getYear, $jobreport_declare \r\n
EOD;

// print a block of text using Write()
$pdf->write(0, $txt, '', 0, 'J', true, 0, false, false, 0);

$jobreport_date_text = _l('jobreport_date_text');
$tbl_po = <<<EOD
<table style="margin-left:10">
    <tbody>
        <tr>
            <td style="width:160">PO/SPK/WO/PH *)</td>
            <td style="width:20">:</td>
            <td>$project_name</td>
        </tr>
        <tr>
            <td style="width:160">Tanggal PO</td>
            <td style="width:20">:</td>
            <td>$contract_date</td>
        </tr>
    </tbody>    
</table>
EOD;

$jobreport_result = _l('jobreport_result');
$pdf->writeHTML($tbl_po, true, false, false, false, '');

$txt = <<<EOD
$jobreport_result \r\n
EOD;

// print a block of text using Write()
$pdf->write(0, $txt, '', 0, 'J', true, 0, false, false, 0);

$pdf->Ln(hooks()->apply_filters('pdf_info_and_table_separator', 2));
/*
// The items table
$items = get_jobreport_items_table_data($jobreport, 'jobreport', 'pdf');

$tblhtml = $items->table();

$pdf->writeHTML($tblhtml, true, false, false, false, '');
*/

$items  = '';
$items .= '<table style="padding:5,10,5,10" border="1" class="table table-bordered table-jobreport-items">';
$items .=    '<thead>';
$items .=        '<tr>';
$items .=            '<th width="50" align="center">No#</th>';
$items .=            '<th width="650" align="center">Items</th>';
$items .=            '<th width="100" align="center">Item/Lot</th>';
$items .=        '</tr>';
$items .=    '</thead>';
$items .=    '<tbody>';
        $i=1;
        foreach($jobreport->items as $item){        
            
$items .=            '<tr>';
$items .=                '<td width="50" align="right">' .$i.' </td>';
$items .=                '<td width="650">' .$item['task_name']. '</td>';
$items .=                '<td width="100" align="center">' .$item['count']. '</td>';
$items .=            '</tr>';
            
             $i++; 
         } 
$items .=    '</tbody>';
$items .= '</table>';

pdf_multi_row($items, '', $pdf, ($dimensions['wk'] / 1) - $dimensions['lm']);

$pdf->Ln(4);

$pdf->SetFont($font_name, '', $font_size);

$assigned_path = <<<EOF
        <img src="$jobreport->assigned_path">
    EOF;    
$assigned_info = '<div style="text-align:center;">';
    $assigned_info .= get_option('invoice_company_name') . '<br />';
    $assigned_info .= $assigned_path . '<br />';

if ($jobreport->assigned != 0 && get_option('show_assigned_on_jobreports') == 1) {
    $assigned_info .= get_staff_full_name($jobreport->assigned);
}
$assigned_info .= '</div>';

$acceptance_path = <<<EOF
    <img src="$jobreport->acceptance_path">
EOF;
$client_info = '<div style="text-align:center;">';
    $client_info .= $jobreport->client_company .'<br />';

if ($jobreport->signed != 0) {
    $client_info .= _l('jobreport_signed_by') . ": {$jobreport->acceptance_firstname} {$jobreport->acceptance_lastname}" . '<br />';
    $client_info .= _l('jobreport_signed_date') . ': ' . _dt($jobreport->acceptance_date_string) . '<br />';
    $client_info .= _l('jobreport_signed_ip') . ": {$jobreport->acceptance_ip}" . '<br />';

    $client_info .= $acceptance_path;
    $client_info .= '<br />';
}
$client_info .= '</div>';


$left_info  = $swap == '1' ? $client_info : $assigned_info;
$right_info = $swap == '1' ? $assigned_info : $client_info;
pdf_multi_row($left_info, $right_info, $pdf, ($dimensions['wk'] / 2) - $dimensions['lm']);
$jobreport_closing = _l('jobreport_closing');
$txt = <<<EOD
$jobreport_closing \r\n
EOD;

$pdf->ln(4);
// print a block of text using Write()
$pdf->Write(0, $txt, '', 0, 'J', true, 0, false, false, 0);

if (!empty($jobreport->clientnote)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('jobreport_order'), 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $jobreport->clientnote, 0, 1, false, true, 'L', true);
}

if (!empty($jobreport->terms)) {
    $pdf->Ln(4);
    $pdf->SetFont($font_name, 'B', $font_size);
    $pdf->Cell(0, 0, _l('jobreport_terms_and_conditions') . ":", 0, 1, 'L', 0, '', 0);
    $pdf->SetFont($font_name, '', $font_size);
    $pdf->Ln(2);
    $pdf->writeHTMLCell('', '', '', '', $jobreport->terms, 0, 1, false, true, 'L', true);
}

