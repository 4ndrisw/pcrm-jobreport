<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
Module Name: jobreports
Description: Default module for defining jobreports
Version: 1.0.1
Requires at least: 2.3.*
*/

define('JOBREPORTS_MODULE_NAME', 'jobreports');
define('JOBREPORT_ATTACHMENTS_FOLDER', 'uploads/jobreports/');

hooks()->add_filter('before_jobreport_updated', '_format_data_jobreport_feature');
hooks()->add_filter('before_jobreport_added', '_format_data_jobreport_feature');

//hooks()->add_action('after_cron_run', 'jobreports_notification');
hooks()->add_action('admin_init', 'jobreports_module_init_menu_items');
hooks()->add_action('admin_init', 'jobreports_permissions');
hooks()->add_action('clients_init', 'jobreports_clients_area_menu_items');

hooks()->add_action('staff_member_deleted', 'jobreports_staff_member_deleted');

hooks()->add_action('after_jobreport_updated', 'jobreport_create_assigned_qrcode');

hooks()->add_filter('migration_tables_to_replace_old_links', 'jobreports_migration_tables_to_replace_old_links');
hooks()->add_filter('global_search_result_query', 'jobreports_global_search_result_query', 10, 3);
hooks()->add_filter('global_search_result_output', 'jobreports_global_search_result_output', 10, 2);
hooks()->add_filter('get_dashboard_widgets', 'jobreports_add_dashboard_widget');
hooks()->add_filter('module_jobreports_action_links', 'module_jobreports_action_links');

function jobreports_add_dashboard_widget($widgets)
{
    $widgets[] = [
        'path'      => 'jobreports/widgets/jobreport_this_week',
        'container' => 'left-8',
    ];
    $widgets[] = [
        'path'      => 'jobreports/widgets/jobreport_unfinished_project',
        'container' => 'left-8',
    ];

    return $widgets;
}


function jobreports_staff_member_deleted($data)
{
    $CI = &get_instance();
    $CI->db->where('staff_id', $data['id']);
    $CI->db->update(db_prefix() . 'jobreports', [
            'staff_id' => $data['transfer_data_to'],
        ]);
}

function jobreports_global_search_result_output($output, $data)
{
    if ($data['type'] == 'jobreports') {
        $output = '<a href="' . admin_url('jobreports/jobreport/' . $data['result']['id']) . '">' . format_jobreport_number($data['result']['id']) . '</a>';
    }

    return $output;
}

function jobreports_global_search_result_query($result, $q, $limit)
{
    $CI = &get_instance();
    if (has_permission('jobreports', '', 'view')) {

        // jobreports
        $CI->db->select()
           ->from(db_prefix() . 'jobreports')
           ->like(db_prefix() . 'jobreports.formatted_number', $q)->limit($limit);
        
        $result[] = [
                'result'         => $CI->db->get()->result_array(),
                'type'           => 'jobreports',
                'search_heading' => _l('jobreports'),
            ];
        
        if(isset($result[0]['result'][0]['id'])){
            return $result;
        }

        // jobreports
        $CI->db->select()->from(db_prefix() . 'jobreports')->like(db_prefix() . 'clients.company', $q)->or_like(db_prefix() . 'jobreports.formatted_number', $q)->limit($limit);
        $CI->db->join(db_prefix() . 'clients',db_prefix() . 'jobreports.clientid='.db_prefix() .'clients.userid', 'left');
        $CI->db->order_by(db_prefix() . 'clients.company', 'ASC');

        $result[] = [
                'result'         => $CI->db->get()->result_array(),
                'type'           => 'jobreports',
                'search_heading' => _l('jobreports'),
            ];
    }

    return $result;
}

function jobreports_migration_tables_to_replace_old_links($tables)
{
    $tables[] = [
                'table' => db_prefix() . 'jobreports',
                'field' => 'description',
            ];

    return $tables;
}

function jobreports_permissions()
{
    $capabilities = [];

    $capabilities['capabilities'] = [
            'view'   => _l('permission_view') . '(' . _l('permission_global') . ')',
            'create' => _l('permission_create'),
            'edit'   => _l('permission_edit'),
            'delete' => _l('permission_delete'),
    ];

    register_staff_capabilities('jobreports', $capabilities, _l('jobreports'));
}


/**
* Register activation module hook
*/
register_activation_hook(JOBREPORTS_MODULE_NAME, 'jobreports_module_activation_hook');

function jobreports_module_activation_hook()
{
    $CI = &get_instance();
    require_once(__DIR__ . '/install.php');
}

/**
* Register deactivation module hook
*/
register_deactivation_hook(JOBREPORTS_MODULE_NAME, 'jobreports_module_deactivation_hook');

function jobreports_module_deactivation_hook()
{

     log_activity( 'Hello, world! . jobreports_module_deactivation_hook ' );
}

//hooks()->add_action('deactivate_' . $module . '_module', $function);

/**
* Register language files, must be registered if the module is using languages
*/
register_language_files(JOBREPORTS_MODULE_NAME, [JOBREPORTS_MODULE_NAME]);

/**
 * Init jobreports module menu items in setup in admin_init hook
 * @return null
 */
function jobreports_module_init_menu_items()
{
    $CI = &get_instance();

    $CI->app->add_quick_actions_link([
            'name'       => _l('jobreport'),
            'url'        => 'jobreports',
            'permission' => 'jobreports',
            'position'   => 57,
            ]);

    if (has_permission('jobreports', '', 'view')) {
        $CI->app_menu->add_sidebar_menu_item('jobreports', [
                'slug'     => 'jobreports-tracking',
                'name'     => _l('jobreports'),
                'icon'     => 'fa fa-briefcase',
                'href'     => admin_url('jobreports'),
                'position' => 13,
        ]);
    }
}

function module_jobreports_action_links($actions)
{
    $actions[] = '<a href="' . admin_url('settings?group=jobreports') . '">' . _l('settings') . '</a>';

    return $actions;
}

function jobreports_clients_area_menu_items()
{   
    // Show menu item only if client is logged in
    if (is_client_logged_in()) {
        add_theme_menu_item('jobreports', [
                    'name'     => _l('jobreports'),
                    'href'     => site_url('jobreports/list'),
                    'position' => 15,
        ]);
    }
}

/**
 * [perfex_dark_theme_settings_tab net menu item in setup->settings]
 * @return void
 */
function jobreports_settings_tab()
{
    $CI = &get_instance();
    $CI->app_tabs->add_settings_tab('jobreports', [
        'name'     => _l('settings_group_jobreports'),
        //'view'     => module_views_path(JOBREPORTS_MODULE_NAME, 'admin/settings/includes/jobreports'),
        'view'     => 'jobreports/jobreports_settings',
        'position' => 51,
    ]);
}

$CI = &get_instance();
$CI->load->helper(JOBREPORTS_MODULE_NAME . '/jobreports');

if(($CI->uri->segment(0)=='admin' && $CI->uri->segment(1)=='jobreports') || $CI->uri->segment(1)=='jobreports'){
    $CI->app_css->add(JOBREPORTS_MODULE_NAME.'-css', base_url('modules/'.JOBREPORTS_MODULE_NAME.'/assets/css/'.JOBREPORTS_MODULE_NAME.'.css'));
    $CI->app_scripts->add(JOBREPORTS_MODULE_NAME.'-js', base_url('modules/'.JOBREPORTS_MODULE_NAME.'/assets/js/'.JOBREPORTS_MODULE_NAME.'.js'));
}

