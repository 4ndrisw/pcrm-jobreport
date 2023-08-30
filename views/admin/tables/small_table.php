<?php

defined('BASEPATH') or exit('No direct script access allowed');

$aColumns = [
    'formatted_number',
    'company',
    db_prefix() . 'projects.name',
    'date',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'jobreports';


$join = [
    'LEFT JOIN ' . db_prefix() . 'clients ON ' . db_prefix() . 'clients.userid = ' . db_prefix() . 'jobreports.clientid',
    'LEFT JOIN ' . db_prefix() . 'projects ON ' . db_prefix() . 'projects.id = ' . db_prefix() . 'jobreports.project_id',
];


$additionalColumns = hooks()->apply_filters('jobreports_table_additional_columns_sql', [
    db_prefix() . 'jobreports.id',
    'acceptance_lastname',
]);
$where = [];
$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalColumns);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];
        if ($aColumns[$i] == 'formatted_number') {
            $_data = '<a href="' . admin_url('jobreports/jobreport/' . $aRow['id']) . '">' . $_data . '</a>';
            /*
            $_data = '<a href="' . admin_url('jobreports/jobreport/' . $aRow['id']. '#' . $aRow['id']) . '" onclick="init_jobreport(' . $aRow['id'] . '); return false;">' . format_jobreport_number($aRow['id']) . '</a>';
            */

            $_data .= '<div class="row-options">';
            $_data .= '<a href="' . admin_url('jobreports/update/' . $aRow['id']) . '">' . _l('edit') . '</a>';

            if (has_permission('jobreports', '', 'delete')) {
                $_data .= ' | <a href="' . admin_url('jobreports/delete/' . $aRow['id']) . '" class="text-danger _delete">' . _l('delete') . '</a>';
            }
            $_data .= '</div>';
        } elseif ($aColumns[$i] == 'date') {
            $_data = _d($_data);
        } 
        $row[] = $_data;

    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
