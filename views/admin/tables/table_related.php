<?php

defined('BASEPATH') or exit('No direct script access allowed');

$CI = &get_instance();
$path = $CI->uri->segment(3);
$jobreport_id = $CI->session->userdata('jobreport_id');
$project_id = $CI->session->userdata('project_id');

$aColumns = [
    db_prefix() . 'tasks.name',
    db_prefix() . 'tags.name',
    'flag',
];

$sIndexColumn = 'id';
$sTable       = db_prefix() . 'jobreport_items';


$join = [
    'RIGHT JOIN ' . db_prefix() . 'tasks ON ' . db_prefix() . 'jobreport_items.task_id = ' . db_prefix() . 'tasks.id',
    'LEFT JOIN ' . db_prefix() . 'projects ON ' . db_prefix() . 'projects.id = ' . db_prefix() . 'tasks.rel_id',
    'LEFT JOIN ' . db_prefix() . 'taggables ON ' . db_prefix() . 'taggables.rel_id = ' . db_prefix() . 'tasks.id',
    'LEFT JOIN ' . db_prefix() . 'tags ON ' . db_prefix() . 'taggables.tag_id = ' . db_prefix() . 'tags.id',
];

$additionalSelect = [db_prefix() . 'jobreport_items.id','jobreport_id',db_prefix() . 'tasks.id as task_id'];


$where  = [];
array_push($where, 'AND ' . db_prefix() . 'projects.id = "'.$project_id.'"');
array_push($where, 'AND ' . db_prefix() . 'tasks.rel_type = "project"');
array_push($where, 'AND ' . db_prefix() . 'jobreport_items.id IS NULL');


$result = data_tables_init($aColumns, $sIndexColumn, $sTable, $join, $where, $additionalSelect);

$output  = $result['output'];
$rResult = $result['rResult'];

foreach ($rResult as $aRow) {
    $row = [];
    for ($i = 0; $i < count($aColumns); $i++) {
        $_data = $aRow[$aColumns[$i]];

        if ($aColumns[$i] == db_prefix() . 'tasks.name') {
            $_data = '<a href="' . admin_url('tasks/view/' . $aRow['task_id']) . '" target = "_blank">' . $_data . '</a>';
        } elseif ($aColumns[$i] == 'flag') {
            $_data = '<a class="btn btn-success" title = "'._l('propose_this_item').'" href="#" onclick="jobreport_add_item(' . $jobreport_id . ','. $project_id . ',' . $aRow['task_id'] . '); return false;">+</a>';
        } 
        $row[] = $_data;

    }
    $row['DT_RowClass'] = 'has-row-options';
    $output['aaData'][] = $row;
}
