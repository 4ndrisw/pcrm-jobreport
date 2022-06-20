<?php

use app\services\AbstractKanban;
use app\services\jobreports\JobreportsPipeline;

defined('BASEPATH') or exit('No direct script access allowed');

class Jobreports_model extends App_Model
{
    private $statuses;

    private $shipping_fields = ['shipping_street', 'shipping_city', 'shipping_city', 'shipping_state', 'shipping_zip', 'shipping_country'];

    public function __construct()
    {
        parent::__construct();

        $this->statuses = hooks()->apply_filters('before_set_jobreport_statuses', [
            1,
            2,
            5,
            3,
            4,
        ]);   
    }
    /**
     * Get unique sale agent for jobreports / Used for filters
     * @return array
     */
    public function get_assigneds()
    {
        return $this->db->query("SELECT DISTINCT(assigned) as assigned, CONCAT(firstname, ' ', lastname) as full_name FROM " . db_prefix() . 'jobreports JOIN ' . db_prefix() . 'staff on ' . db_prefix() . 'staff.staffid=' . db_prefix() . 'jobreports.assigned WHERE assigned != 0')->result_array();
    }

    /**
     * Get jobreport/s
     * @param mixed $id jobreport id
     * @param array $where perform where
     * @return mixed
     */
    public function get($id = '', $where = [])
    {
//        $this->db->select('*,' . db_prefix() . 'currencies.id as currencyid, ' . db_prefix() . 'jobreports.id as id, ' . db_prefix() . 'currencies.name as currency_name');
        $this->db->select('*,' . db_prefix() . 'jobreports.id as id');
        $this->db->from(db_prefix() . 'jobreports');
        //$this->db->join(db_prefix() . 'currencies', db_prefix() . 'currencies.id = ' . db_prefix() . 'jobreports.currency', 'left');
        $this->db->where($where);
        if (is_numeric($id)) {
            $this->db->where(db_prefix() . 'jobreports.id', $id);
            $jobreport = $this->db->get()->row();
            if ($jobreport) {
                $jobreport->attachments                           = $this->get_attachments($id);
                $jobreport->visible_attachments_to_customer_found = false;

                foreach ($jobreport->attachments as $attachment) {
                    if ($attachment['visible_to_customer'] == 1) {
                        $jobreport->visible_attachments_to_customer_found = true;

                        break;
                    }
                }

                $jobreport->items = get_items_by_type('jobreport', $id);
                if(isset($jobreport->jobreport_id)){

                    if ($jobreport->jobreport_id != 0) {
                        $this->load->model('jobreports_model');
                        $jobreport->jobreport_data = $this->jobreports_model->get($jobreport->jobreport_id);
                    }

                }
                $jobreport->client = $this->clients_model->get($jobreport->clientid);

                if (!$jobreport->client) {
                    $jobreport->client          = new stdClass();
                    $jobreport->client->company = $jobreport->deleted_customer_name;
                }

                $this->load->model('email_schedule_model');
                $jobreport->scheduled_email = $this->email_schedule_model->get($id, 'jobreport');
            }

            return $jobreport;
        }
        $this->db->order_by('number,YEAR(date)', 'desc');

        return $this->db->get()->result_array();
    }

    /**
     * Get jobreport statuses
     * @return array
     */
    public function get_statuses()
    {
        return $this->statuses;
    }


    /**
     * Get jobreport statuses
     * @return array
     */
    public function get_status($status,$id)
    {
        $this->db->where('status', $status);
        $this->db->where('id', $id);
        $jobreport = $this->db->get(db_prefix() . 'jobreports')->row();

        return $this->status;
    }

    public function clear_signature($id)
    {
        $this->db->select('signed','signature','status');
        $this->db->where('id', $id);
        $jobreport = $this->db->get(db_prefix() . 'jobreports')->row();

        if ($jobreport) {
            $this->db->where('id', $id);
            $this->db->update(db_prefix() . 'jobreports', ['signed'=>0,'signature' => null, 'status'=>2]);

            if (!empty($jobreport->signature)) {
                unlink(get_upload_path_by_type('jobreport') . $id . '/' . $jobreport->signature);
            }

            return true;
        }

        return false;
    }


    /**
     * Copy jobreport
     * @param mixed $id jobreport id to copy
     * @return mixed
     */
    public function copy($id)
    {
        $_jobreport                       = $this->get($id);
        $new_jobreport_data               = [];
        $new_jobreport_data['clientid']   = $_jobreport->clientid;
        $new_jobreport_data['project_id']   = $_jobreport->project_id;
        $new_jobreport_data['number']     = get_option('next_jobreport_number');
        $new_jobreport_data['date']       = _d(date('Y-m-d'));

    

        $number = get_option('next_jobreport_number');
        $format = get_option('jobreport_number_format');
        $prefix = get_option('jobreport_prefix');
        $date = date('Y-m-d');
        
        $new_jobreport_data['formatted_number'] = jobreport_number_format($number, $format, $prefix, $date);



        $new_jobreport_data['terms']            = $_jobreport->terms;
        $new_jobreport_data['assigned']       = $_jobreport->assigned;
        $new_jobreport_data['reference_no']     = $_jobreport->reference_no;
        // Since version 1.0.6
        $new_jobreport_data['billing_street']   = clear_textarea_breaks($_jobreport->billing_street);
        $new_jobreport_data['billing_city']     = $_jobreport->billing_city;
        $new_jobreport_data['billing_state']    = $_jobreport->billing_state;
        $new_jobreport_data['billing_zip']      = $_jobreport->billing_zip;
        $new_jobreport_data['billing_country']  = $_jobreport->billing_country;
        $new_jobreport_data['shipping_street']  = clear_textarea_breaks($_jobreport->shipping_street);
        $new_jobreport_data['shipping_city']    = $_jobreport->shipping_city;
        $new_jobreport_data['shipping_state']   = $_jobreport->shipping_state;
        $new_jobreport_data['shipping_zip']     = $_jobreport->shipping_zip;
        $new_jobreport_data['shipping_country'] = $_jobreport->shipping_country;
        if ($_jobreport->include_shipping == 1) {
            $new_jobreport_data['include_shipping'] = $_jobreport->include_shipping;
        }
        $new_jobreport_data['show_shipping_on_jobreport'] = $_jobreport->show_shipping_on_jobreport;
        // Set to unpaid status automatically
        $new_jobreport_data['status']     = 1;
        $new_jobreport_data['clientnote'] = $_jobreport->clientnote;
        $new_jobreport_data['adminnote']  = '';
        $new_jobreport_data['newitems']   = [];
        $custom_fields_items             = get_custom_fields('items');
        $key                             = 1;
        foreach ($_jobreport->items as $item) {
            $new_jobreport_data['newitems'][$key]['description']      = $item['description'];
            $new_jobreport_data['newitems'][$key]['long_description'] = clear_textarea_breaks($item['long_description']);
            $new_jobreport_data['newitems'][$key]['qty']              = $item['qty'];
            $new_jobreport_data['newitems'][$key]['unit']             = $item['unit'];
            
            $new_jobreport_data['newitems'][$key]['order'] = $item['item_order'];
            $key++;
        }
        $id = $this->add($new_jobreport_data);
        if ($id) {

            $tags = get_tags_in($_jobreport->id, 'jobreport');
            handle_tags_save($tags, $id, 'jobreport');

            $this->log_jobreport_activity('Copied Jobreport ' . format_jobreport_number($_jobreport->id));

            return $id;
        }

        return false;
    }

    /**
     * Performs jobreports totals status
     * @param array $data
     * @return array
     */
    public function get_jobreports_total($data)
    {
        $statuses            = $this->get_statuses();
        $has_permission_view = has_permission('jobreports', '', 'view');
        $this->load->model('currencies_model');
        if (isset($data['currency'])) {
            $currencyid = $data['currency'];
        } elseif (isset($data['customer_id']) && $data['customer_id'] != '') {
            $currencyid = $this->clients_model->get_customer_default_currency($data['customer_id']);
            if ($currencyid == 0) {
                $currencyid = $this->currencies_model->get_base_currency()->id;
            }
        } elseif (isset($data['jobreport_id']) && $data['jobreport_id'] != '') {
            $this->load->model('jobreports_model');
            $currencyid = $this->jobreports_model->get_currency($data['jobreport_id'])->id;
        } else {
            $currencyid = $this->currencies_model->get_base_currency()->id;
        }

        $currency = get_currency($currencyid);
        $where    = '';
        if (isset($data['customer_id']) && $data['customer_id'] != '') {
            $where = ' AND clientid=' . $data['customer_id'];
        }

        if (isset($data['jobreport_id']) && $data['jobreport_id'] != '') {
            $where .= ' AND jobreport_id=' . $data['jobreport_id'];
        }

        if (!$has_permission_view) {
            $where .= ' AND ' . get_jobreports_where_sql_for_staff(get_staff_user_id());
        }

        $sql = 'SELECT';
        foreach ($statuses as $jobreport_status) {
            $sql .= '(SELECT SUM(total) FROM ' . db_prefix() . 'jobreports WHERE status=' . $jobreport_status;
            $sql .= ' AND currency =' . $this->db->escape_str($currencyid);
            if (isset($data['years']) && count($data['years']) > 0) {
                $sql .= ' AND YEAR(date) IN (' . implode(', ', array_map(function ($year) {
                    return get_instance()->db->escape_str($year);
                }, $data['years'])) . ')';
            } else {
                $sql .= ' AND YEAR(date) = ' . date('Y');
            }
            $sql .= $where;
            $sql .= ') as "' . $jobreport_status . '",';
        }

        $sql     = substr($sql, 0, -1);
        $result  = $this->db->query($sql)->result_array();
        $_result = [];
        $i       = 1;
        foreach ($result as $key => $val) {
            foreach ($val as $status => $total) {
                $_result[$i]['total']         = $total;
                $_result[$i]['symbol']        = $currency->symbol;
                $_result[$i]['currency_name'] = $currency->name;
                $_result[$i]['status']        = $status;
                $i++;
            }
        }
        $_result['currencyid'] = $currencyid;

        return $_result;
    }

    /**
     * Insert new jobreport to database
     * @param array $data invoiec data
     * @return mixed - false if not insert, jobreport ID if succes
     */
    public function add($data)
    {
        $affectedRows = 0;
        
        $data['datecreated'] = date('Y-m-d H:i:s');

        $data['addedfrom'] = get_staff_user_id();

        $data['prefix'] = get_option('jobreport_prefix');

        $data['number_format'] = get_option('jobreport_number_format');

        $save_and_send = isset($data['save_and_send']);


        $data['hash'] = app_generate_hash();
        $tags         = isset($data['tags']) ? $data['tags'] : '';

        $items = [];
        if (isset($data['newitems'])) {
            $items = $data['newitems'];
            unset($data['newitems']);
        }

        $data = $this->map_shipping_columns($data);

        $data['billing_street'] = trim($data['billing_street']);
        $data['billing_street'] = nl2br($data['billing_street']);

        if (isset($data['shipping_street'])) {
            $data['shipping_street'] = trim($data['shipping_street']);
            $data['shipping_street'] = nl2br($data['shipping_street']);
        }

        $hook = hooks()->apply_filters('before_jobreport_added', [
            'data'  => $data,
            'items' => $items,
        ]);

        $data  = $hook['data'];
        $items = $hook['items'];

        unset($data['tags']);
        unset($data['allowed_payment_modes']);
        unset($data['save_as_draft']);
        unset($data['schedule_id']);
        unset($data['duedate']);

        try {
            $this->db->insert(db_prefix() . 'jobreports', $data);
        } catch (Exception $e) {
            $message = $e->getMessage();
            log_activity('Insert ERROR ' . $message);
        }

        $insert_id = $this->db->insert_id();

        if ($insert_id) {
            // Update next jobreport number in settings
            $this->db->where('name', 'next_jobreport_number');
            $this->db->set('value', 'value+1', false);
            $this->db->update(db_prefix() . 'options');

            handle_tags_save($tags, $insert_id, 'jobreport');

            foreach ($items as $key => $item) {
                if ($new_item_added = add_new_jobreport_item_post($item, $insert_id, 'jobreport')) {
                    $affectedRows++;
                }
            }

            hooks()->do_action('after_jobreport_added', $insert_id);

            if ($save_and_send === true) {
                $this->send_jobreport_to_client($insert_id, '', true, '', true);
            }

            return $insert_id;
        }

        return false;
    }

    /**
     * Get item by id
     * @param mixed $id item id
     * @return object
     */
    public function get_jobreport_item($id)
    {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'jobreport');

        return $this->db->get(db_prefix() . 'jobreport_items')->result();
    }

    /**
     * Update jobreport data
     * @param array $data jobreport data
     * @param mixed $id jobreportid
     * @return boolean
     */
    public function update($data, $id)
    {
        $affectedRows = 0;

        $data['number'] = trim($data['number']);

        $original_jobreport = $this->get($id);

        $original_status = $original_jobreport->status;

        $original_number = $original_jobreport->number;

        $original_number_formatted = format_jobreport_number($id);

        $save_and_send = isset($data['save_and_send']);

        $items = [];
        if (isset($data['items'])) {
            $items = $data['items'];
            unset($data['items']);
        }

        $newitems = [];
        if (isset($data['newitems'])) {
            $newitems = $data['newitems'];
            unset($data['newitems']);
        }

        if (isset($data['tags'])) {
            if (handle_tags_save($data['tags'], $id, 'jobreport')) {
                $affectedRows++;
            }
        }

        $data['billing_street'] = trim($data['billing_street']);
        $data['billing_street'] = nl2br($data['billing_street']);

        $data['shipping_street'] = trim($data['shipping_street']);
        $data['shipping_street'] = nl2br($data['shipping_street']);

        $data = $this->map_shipping_columns($data);

        $hook = hooks()->apply_filters('before_jobreport_updated', [
            'data'             => $data,
            'items'            => $items,
            'newitems'         => $newitems,
            'removed_items'    => isset($data['removed_items']) ? $data['removed_items'] : [],
        ], $id);

        $data                  = $hook['data'];
        $items                 = $hook['items'];
        $newitems              = $hook['newitems'];
        $data['removed_items'] = $hook['removed_items'];

        // Delete items checked to be removed from database
        foreach ($data['removed_items'] as $remove_item_id) {
            $original_item = $this->get_jobreport_item($remove_item_id);
            if (handle_removed_jobreport_item_post($remove_item_id, 'jobreport')) {
                $affectedRows++;
                $this->log_jobreport_activity($id, 'jobreport_activity_removed_item', false, serialize([
                    $original_item->description,
                ]));
            }
        }

        unset($data['removed_items']);

        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'jobreports', $data);

        if ($this->db->affected_rows() > 0) {
            // Check for status change
            if ($original_status != $data['status']) {
                $this->log_jobreport_activity($original_jobreport->id, 'not_jobreport_status_updated', false, serialize([
                    '<original_status>' . $original_status . '</original_status>',
                    '<new_status>' . $data['status'] . '</new_status>',
                ]));
                if ($data['status'] == 2) {
                    $this->db->where('id', $id);
                    $this->db->update(db_prefix() . 'jobreports', ['sent' => 1, 'datesend' => date('Y-m-d H:i:s')]);
                }
            }
            if ($original_number != $data['number']) {
                $this->log_jobreport_activity($original_jobreport->id, 'jobreport_activity_number_changed', false, serialize([
                    $original_number_formatted,
                    format_jobreport_number($original_jobreport->id),
                ]));
            }
            $affectedRows++;
        }

        foreach ($items as $key => $item) {
            $original_item = $this->get_jobreport_item($item['itemid']);

            if (update_jobreport_item_post($item['itemid'], $item, 'item_order')) {
                $affectedRows++;
            }

            if (update_jobreport_item_post($item['itemid'], $item, 'unit')) {
                $affectedRows++;
            }


            if (update_jobreport_item_post($item['itemid'], $item, 'qty')) {
                $this->log_jobreport_activity($id, 'jobreport_activity_updated_qty_item', false, serialize([
                    $item['description'],
                    $original_item->qty,
                    $item['qty'],
                ]));
                $affectedRows++;
            }

            if (update_jobreport_item_post($item['itemid'], $item, 'description')) {
                $this->log_jobreport_activity($id, 'jobreport_activity_updated_item_short_description', false, serialize([
                    $original_item->description,
                    $item['description'],
                ]));
                $affectedRows++;
            }

            if (update_jobreport_item_post($item['itemid'], $item, 'long_description')) {
                $this->log_jobreport_activity($id, 'jobreport_activity_updated_item_long_description', false, serialize([
                    $original_item->long_description,
                    $item['long_description'],
                ]));
                $affectedRows++;
            }

        }

        foreach ($newitems as $key => $item) {
            if ($new_item_added = add_new_jobreport_item_post($item, $id, 'jobreport')) {
                $affectedRows++;
            }
        }

        if ($save_and_send === true) {
            $this->send_jobreport_to_client($id, '', true, '', true);
        }

        if ($affectedRows > 0) {
            hooks()->do_action('after_jobreport_updated', $id);
            return true;
        }

        return false;
    }

    public function mark_action_status($action, $id, $client = false)
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'jobreports', [
            'status' => $action,
            'signed' => ($action == 4) ? 1 : 0,
        ]);

        $notifiedUsers = [];

        if ($this->db->affected_rows() > 0) {
            $jobreport = $this->get($id);
            if ($client == true) {
                $this->db->where('staffid', $jobreport->addedfrom);
                $this->db->or_where('staffid', $jobreport->assigned);
                $staff_jobreport = $this->db->get(db_prefix() . 'staff')->result_array();

                $invoiceid = false;
                $invoiced  = false;

                $contact_id = !is_client_logged_in()
                    ? get_primary_contact_user_id($jobreport->clientid)
                    : get_contact_user_id();

                if ($action == 4) {
                    $this->log_jobreport_activity($id, 'jobreport_activity_client_accepted', true);

                    // Send thank you email to all contacts with permission jobreports
                    $contacts = $this->clients_model->get_contacts($jobreport->clientid, ['active' => 1, 'project_emails' => 1]);

                    foreach ($contacts as $contact) {
                        // (To fix merge field) send_mail_template('jobreport_accepted_to_customer','jobreports', $jobreport, $contact);
                    }

                    foreach ($staff_jobreport as $member) {
                        $notified = add_notification([
                            'fromcompany'     => true,
                            'touserid'        => $member['staffid'],
                            'description'     => 'jobreport_customer_accepted',
                            'link'            => 'jobreports/jobreport/' . $id,
                            'additional_data' => serialize([
                                format_jobreport_number($jobreport->id),
                            ]),
                        ]);

                        if ($notified) {
                            array_push($notifiedUsers, $member['staffid']);
                        }

                        // (To fix merge field) send_mail_template('jobreport_accepted_to_staff','jobreports', $jobreport, $member['email'], $contact_id);
                    }

                    pusher_trigger_notification($notifiedUsers);
                    hooks()->do_action('jobreport_accepted', $jobreport);

                    return true;
                } elseif ($action == 3) {
                    foreach ($staff_jobreport as $member) {
                        $notified = add_notification([
                            'fromcompany'     => true,
                            'touserid'        => $member['staffid'],
                            'description'     => 'jobreport_customer_declined',
                            'link'            => 'jobreports/jobreport/' . $id,
                            'additional_data' => serialize([
                                format_jobreport_number($jobreport->id),
                            ]),
                        ]);

                        if ($notified) {
                            array_push($notifiedUsers, $member['staffid']);
                        }
                        // Send staff email notification that customer declined jobreport
                        // (To fix merge field) send_mail_template('jobreport_declined_to_staff', 'jobreports',$jobreport, $member['email'], $contact_id);
                    }
                    pusher_trigger_notification($notifiedUsers);
                    $this->log_jobreport_activity($id, 'jobreport_activity_client_declined', true);
                    hooks()->do_action('jobreport_declined', $jobreport);

                    return true;
                }
            } else {
                if ($action == 2) {
                    $this->db->where('id', $id);
                    $this->db->update(db_prefix() . 'jobreports', ['sent' => 1, 'datesend' => date('Y-m-d H:i:s')]);
                
                    $this->db->where('active', 1);
                    $staff_jobreport = $this->db->get(db_prefix() . 'staff')->result_array();
                    $contacts = $this->clients_model->get_contacts($jobreport->clientid, ['active' => 1, 'project_emails' => 1]);
                    
                        foreach ($staff_jobreport as $member) {
                            $notified = add_notification([
                                'fromcompany'     => true,
                                'touserid'        => $member['staffid'],
                                'description'     => 'jobreport_send_to_customer_already_sent',
                                'link'            => 'jobreports/jobreport/' . $id,
                                'additional_data' => serialize([
                                    format_jobreport_number($jobreport->id),
                                ]),
                            ]);
                    
                            if ($notified) {
                                array_push($notifiedUsers, $member['staffid']);
                            }
                            // Send staff email notification that customer declined jobreport
                            // (To fix merge field) send_mail_template('jobreport_declined_to_staff', 'jobreports',$jobreport, $member['email'], $contact_id);
                        }

                    // Admin marked jobreport
                    $this->log_jobreport_activity($id, 'jobreport_activity_marked', false, serialize([
                        '<status>' . $action . '</status>',
                    ]));
                    pusher_trigger_notification($notifiedUsers);
                    hooks()->do_action('jobreport_send_to_customer_already_sent', $jobreport);

                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get jobreport attachments
     * @param mixed $jobreport_id
     * @param string $id attachment id
     * @return mixed
     */
    public function get_attachments($jobreport_id, $id = '')
    {
        // If is passed id get return only 1 attachment
        if (is_numeric($id)) {
            $this->db->where('id', $id);
        } else {
            $this->db->where('rel_id', $jobreport_id);
        }
        $this->db->where('rel_type', 'jobreport');
        $result = $this->db->get(db_prefix() . 'files');
        if (is_numeric($id)) {
            return $result->row();
        }

        return $result->result_array();
    }

    /**
     *  Delete jobreport attachment
     * @param mixed $id attachmentid
     * @return  boolean
     */
    public function delete_attachment($id)
    {
        $attachment = $this->get_attachments('', $id);
        $deleted    = false;
        if ($attachment) {
            if (empty($attachment->external)) {
                unlink(get_upload_path_by_type('jobreport') . $attachment->rel_id . '/' . $attachment->file_name);
            }
            $this->db->where('id', $attachment->id);
            $this->db->delete(db_prefix() . 'files');
            if ($this->db->affected_rows() > 0) {
                $deleted = true;
                log_jobreport_activity('Jobreport Attachment Deleted [JobreportID: ' . $attachment->rel_id . ']');
            }

            if (is_dir(get_upload_path_by_type('jobreport') . $attachment->rel_id)) {
                // Check if no attachments left, so we can delete the folder also
                $other_attachments = list_files(get_upload_path_by_type('jobreport') . $attachment->rel_id);
                if (count($other_attachments) == 0) {
                    // okey only index.html so we can delete the folder also
                    delete_dir(get_upload_path_by_type('jobreport') . $attachment->rel_id);
                }
            }
        }

        return $deleted;
    }

    /**
     * Delete jobreport items and all connections
     * @param mixed $id jobreportid
     * @return boolean
     */
    public function delete($id, $simpleDelete = false)
    {
        if (get_option('delete_only_on_last_jobreport') == 1 && $simpleDelete == false) {
            if (!is_last_jobreport($id)) {
                return false;
            }
        }
        $jobreport = $this->get($id);
        /*
        if (!is_null($jobreport->invoiceid) && $simpleDelete == false) {
            return [
                'is_invoiced_jobreport_delete_error' => true,
            ];
        }
        */
        hooks()->do_action('before_jobreport_deleted', $id);

        $number = format_jobreport_number($id);

        $this->clear_signature($id);

        $this->db->where('id', $id);
        $this->db->delete(db_prefix() . 'jobreports');

        if ($this->db->affected_rows() > 0) {
            if (!is_null($jobreport->short_link)) {
                app_archive_short_link($jobreport->short_link);
            }

            if (get_option('jobreport_number_decrement_on_delete') == 1 && $simpleDelete == false) {
                $current_next_jobreport_number = get_option('next_jobreport_number');
                if ($current_next_jobreport_number > 1) {
                    // Decrement next jobreport number to
                    $this->db->where('name', 'next_jobreport_number');
                    $this->db->set('value', 'value-1', false);
                    $this->db->update(db_prefix() . 'options');
                }
            }

            delete_tracked_emails($id, 'jobreport');

            // Delete the items values
            $this->db->where('jobreportid', $id);
            $this->db->set('jobreportid', NULL, true);
            $this->db->set('jobreport_date', NULL, true);
            $this->db->update(db_prefix() . 'schedules');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'jobreport');
            $this->db->delete(db_prefix() . 'notes');

            $this->db->where('rel_type', 'jobreport');
            $this->db->where('rel_id', $id);
            $this->db->delete(db_prefix() . 'views_tracking');

            $this->db->where('rel_type', 'jobreport');
            $this->db->where('rel_id', $id);
            $this->db->delete(db_prefix() . 'taggables');

            $this->db->where('rel_type', 'jobreport');
            $this->db->where('rel_id', $id);
            $this->db->delete(db_prefix() . 'reminders');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'jobreport');
            $this->db->delete(db_prefix() . 'jobreport_items');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'jobreport');
            $this->db->delete(db_prefix() . 'item_tax');

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'jobreport');
            $this->db->delete(db_prefix() . 'jobreport_activity');

            // Delete the items values
            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'jobreport');
            $this->db->delete(db_prefix() . 'itemable');


            $attachments = $this->get_attachments($id);
            foreach ($attachments as $attachment) {
                $this->delete_attachment($attachment['id']);
            }

            $this->db->where('rel_id', $id);
            $this->db->where('rel_type', 'jobreport');
            $this->db->delete('scheduled_emails');

            // Get related tasks
            $this->db->where('rel_type', 'jobreport');
            $this->db->where('rel_id', $id);
            $tasks = $this->db->get(db_prefix() . 'tasks')->result_array();
            foreach ($tasks as $task) {
                $this->tasks_model->delete_task($task['id']);
            }
            if ($simpleDelete == false) {
                $this->log_jobreport_activity('Jobreports Deleted [Number: ' . $number . ']');
            }

            return true;
        }

        return false;
    }

    /**
     * Set jobreport to sent when email is successfuly sended to client
     * @param mixed $id jobreportid
     */
    public function set_jobreport_sent($id, $emails_sent = [])
    {
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'jobreports', [
            'sent'     => 1,
            'datesend' => date('Y-m-d H:i:s'),
        ]);

        $this->log_jobreport_activity($id, 'jobreport_activity_sent_to_client', false, serialize([
            '<custom_data>' . implode(', ', $emails_sent) . '</custom_data>',
        ]));

        // Update jobreport status to sent
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'jobreports', [
            'status' => 2,
        ]);

        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'jobreport');
        $this->db->delete('jobreportd_emails');
    }

    /**
     * Send expiration reminder to customer
     * @param mixed $id jobreport id
     * @return boolean
     */
    public function send_expiry_reminder($id)
    {
        $jobreport        = $this->get($id);
        $jobreport_number = format_jobreport_number($jobreport->id);
        set_mailing_constant();
        $pdf              = jobreport_pdf($jobreport);
        $attach           = $pdf->Output($jobreport_number . '.pdf', 'S');
        $emails_sent      = [];
        $sms_sent         = false;
        $sms_reminder_log = [];

        // For all cases update this to prevent sending multiple reminders eq on fail
        $this->db->where('id', $id);
        $this->db->update(db_prefix() . 'jobreports', [
            'is_expiry_notified' => 1,
        ]);

        $contacts = $this->clients_model->get_contacts($jobreport->clientid, ['active' => 1, 'project_emails' => 1]);

        foreach ($contacts as $contact) {
            $template = mail_template('jobreport_expiration_reminder', $jobreport, $contact);

            $merge_fields = $template->get_merge_fields();

            $template->add_attachment([
                'attachment' => $attach,
                'filename'   => str_replace('/', '-', $jobreport_number . '.pdf'),
                'type'       => 'application/pdf',
            ]);

            if ($template->send()) {
                array_push($emails_sent, $contact['email']);
            }

            if (can_send_sms_based_on_creation_date($jobreport->datecreated)
                && $this->app_sms->trigger(SMS_TRIGGER_JOBREPORT_EXP_REMINDER, $contact['phonenumber'], $merge_fields)) {
                $sms_sent = true;
                array_push($sms_reminder_log, $contact['firstname'] . ' (' . $contact['phonenumber'] . ')');
            }
        }

        if (count($emails_sent) > 0 || $sms_sent) {
            if (count($emails_sent) > 0) {
                $this->log_jobreport_activity($id, 'not_expiry_reminder_sent', false, serialize([
                    '<custom_data>' . implode(', ', $emails_sent) . '</custom_data>',
                ]));
            }

            if ($sms_sent) {
                $this->log_jobreport_activity($id, 'sms_reminder_sent_to', false, serialize([
                    implode(', ', $sms_reminder_log),
                ]));
            }

            return true;
        }

        return false;
    }

    /**
     * Send jobreport to client
     * @param mixed $id jobreportid
     * @param string $template email template to sent
     * @param boolean $attachpdf attach jobreport pdf or not
     * @return boolean
     */
    public function send_jobreport_to_client($id, $template_name = '', $attachpdf = true, $cc = '', $manually = false)
    {
        $jobreport = $this->get($id);

        if ($template_name == '') {
            $template_name = $jobreport->sent == 0 ?
                'jobreport_send_to_customer' :
                'jobreport_send_to_customer_already_sent';
        }

        $jobreport_number = format_jobreport_number($jobreport->id);

        $emails_sent = [];
        $send_to     = [];

        // Manually is used when sending the jobreport via add/edit area button Save & Send
        if (!DEFINED('CRON') && $manually === false) {
            $send_to = $this->input->post('sent_to');
        } elseif (isset($GLOBALS['jobreportd_email_contacts'])) {
            $send_to = $GLOBALS['jobreportd_email_contacts'];
        } else {
            $contacts = $this->clients_model->get_contacts(
                $jobreport->clientid,
                ['active' => 1, 'project_emails' => 1]
            );

            foreach ($contacts as $contact) {
                array_push($send_to, $contact['id']);
            }
        }

        $status_auto_updated = false;
        $status_now          = $jobreport->status;

        if (is_array($send_to) && count($send_to) > 0) {
            $i = 0;

            // Auto update status to sent in case when user sends the jobreport is with status draft
            if ($status_now == 1) {
                $this->db->where('id', $jobreport->id);
                $this->db->update(db_prefix() . 'jobreports', [
                    'status' => 2,
                ]);
                $status_auto_updated = true;
            }

            if ($attachpdf) {
                $_pdf_jobreport = $this->get($jobreport->id);
                set_mailing_constant();
                $pdf = jobreport_pdf($_pdf_jobreport);

                $attach = $pdf->Output($jobreport_number . '.pdf', 'S');
            }

            foreach ($send_to as $contact_id) {
                if ($contact_id != '') {
                    // Send cc only for the first contact
                    if (!empty($cc) && $i > 0) {
                        $cc = '';
                    }

                    $contact = $this->clients_model->get_contact($contact_id);

                    if (!$contact) {
                        continue;
                    }

                    $template = mail_template($template_name, $jobreport, $contact, $cc);

                    if ($attachpdf) {
                        $hook = hooks()->apply_filters('send_jobreport_to_customer_file_name', [
                            'file_name' => str_replace('/', '-', $jobreport_number . '.pdf'),
                            'jobreport'  => $_pdf_jobreport,
                        ]);

                        $template->add_attachment([
                            'attachment' => $attach,
                            'filename'   => $hook['file_name'],
                            'type'       => 'application/pdf',
                        ]);
                    }

                    if ($template->send()) {
                        array_push($emails_sent, $contact->email);
                    }
                }
                $i++;
            }
        } else {
            return false;
        }

        if (count($emails_sent) > 0) {
            $this->set_jobreport_sent($id, $emails_sent);
            hooks()->do_action('jobreport_sent', $id);

            return true;
        }

        if ($status_auto_updated) {
            // Jobreport not send to customer but the status was previously updated to sent now we need to revert back to draft
            $this->db->where('id', $jobreport->id);
            $this->db->update(db_prefix() . 'jobreports', [
                'status' => 1,
            ]);
        }

        return false;
    }

    /**
     * All jobreport activity
     * @param mixed $id jobreportid
     * @return array
     */
    public function get_jobreport_activity($id)
    {
        $this->db->where('rel_id', $id);
        $this->db->where('rel_type', 'jobreport');
        $this->db->order_by('date', 'desc');

        return $this->db->get(db_prefix() . 'jobreport_activity')->result_array();
    }

    /**
     * Log jobreport activity to database
     * @param mixed $id jobreportid
     * @param string $description activity description
     */
    public function log_jobreport_activity($id, $description = '', $client = false, $additional_data = '')
    {
        $staffid   = get_staff_user_id();
        $full_name = get_staff_full_name(get_staff_user_id());
        if (DEFINED('CRON')) {
            $staffid   = '[CRON]';
            $full_name = '[CRON]';
        } elseif ($client == true) {
            $staffid   = null;
            $full_name = '';
        }

        $this->db->insert(db_prefix() . 'jobreport_activity', [
            'description'     => $description,
            'date'            => date('Y-m-d H:i:s'),
            'rel_id'          => $id,
            'rel_type'        => 'jobreport',
            'staffid'         => $staffid,
            'full_name'       => $full_name,
            'additional_data' => $additional_data,
        ]);
    }

    /**
     * Updates pipeline order when drag and drop
     * @param mixe $data $_POST data
     * @return void
     */
    public function update_pipeline($data)
    {
        $this->mark_action_status($data['status'], $data['jobreportid']);
        AbstractKanban::updateOrder($data['order'], 'pipeline_order', 'jobreports', $data['status']);
    }

    /**
     * Get jobreport unique year for filtering
     * @return array
     */
    public function get_jobreports_years()
    {
        return $this->db->query('SELECT DISTINCT(YEAR(date)) as year FROM ' . db_prefix() . 'jobreports ORDER BY year DESC')->result_array();
    }

    private function map_shipping_columns($data)
    {
        if (!isset($data['include_shipping'])) {
            foreach ($this->shipping_fields as $_s_field) {
                if (isset($data[$_s_field])) {
                    $data[$_s_field] = null;
                }
            }
            $data['show_shipping_on_jobreport'] = 1;
            $data['include_shipping']          = 0;
        } else {
            $data['include_shipping'] = 1;
            // set by default for the next time to be checked
            if (isset($data['show_shipping_on_jobreport']) && ($data['show_shipping_on_jobreport'] == 1 || $data['show_shipping_on_jobreport'] == 'on')) {
                $data['show_shipping_on_jobreport'] = 1;
            } else {
                $data['show_shipping_on_jobreport'] = 0;
            }
        }

        return $data;
    }

    public function do_kanban_query($status, $search = '', $page = 1, $sort = [], $count = false)
    {
        _deprecated_function('Jobreports_model::do_kanban_query', '2.9.2', 'JobreportsPipeline class');

        $kanBan = (new JobreportsPipeline($status))
            ->search($search)
            ->page($page)
            ->sortBy($sort['sort'] ?? null, $sort['sort_by'] ?? null);

        if ($count) {
            return $kanBan->countAll();
        }

        return $kanBan->get();
    }


    public function get_jobreport_members($id, $with_name = false)
    {
        if ($with_name) {
            $this->db->select('firstname,lastname,email,jobreport_id,staff_id');
        } else {
            $this->db->select('email,jobreport_id,staff_id');
        }
        $this->db->join(db_prefix() . 'staff', db_prefix() . 'staff.staffid=' . db_prefix() . 'jobreport_members.staff_id');
        $this->db->where('jobreport_id', $id);

        return $this->db->get(db_prefix() . 'jobreport_members')->result_array();
    }


    /**
     * Update canban jobreport status when drag and drop
     * @param  array $data jobreport data
     * @return boolean
     */
    public function update_jobreport_status($data)
    {
        $this->db->select('status');
        $this->db->where('id', $data['jobreportid']);
        $_old = $this->db->get(db_prefix() . 'jobreports')->row();

        $old_status = '';

        if ($_old) {
            $old_status = format_jobreport_status($_old->status);
        }

        $affectedRows   = 0;
        $current_status = format_jobreport_status($data['status']);


        $this->db->where('id', $data['jobreportid']);
        $this->db->update(db_prefix() . 'jobreports', [
            'status' => $data['status'],
        ]);

        $_log_message = '';

        if ($this->db->affected_rows() > 0) {
            $affectedRows++;
            if ($current_status != $old_status && $old_status != '') {
                $_log_message    = 'not_jobreport_activity_status_updated';
                $additional_data = serialize([
                    get_staff_full_name(),
                    $old_status,
                    $current_status,
                ]);

                hooks()->do_action('jobreport_status_changed', [
                    'jobreport_id'    => $data['jobreportid'],
                    'old_status' => $old_status,
                    'new_status' => $current_status,
                ]);
            }
            $this->db->where('id', $data['jobreportid']);
            $this->db->update(db_prefix() . 'jobreports', [
                'last_status_change' => date('Y-m-d H:i:s'),
            ]);
        }

        if ($affectedRows > 0) {
            if ($_log_message == '') {
                return true;
            }
            $this->log_jobreport_activity($data['jobreportid'], $_log_message, false, $additional_data);

            return true;
        }

        return false;
    }


    /**
     * Get the jobreports about to expired in the given days
     *
     * @param  integer|null $staffId
     * @param  integer $days
     *
     * @return array
     */
    public function get_jobreports_this_week($staffId = null, $days = 7)
    {
        $diff1 = date('Y-m-d', strtotime('-' . $days . ' days'));
        $diff2 = date('Y-m-d', strtotime('+' . $days . ' days'));

        if ($staffId && ! staff_can('view', 'jobreports', $staffId)) {
            $this->db->where(db_prefix() . 'jobreports.addedfrom', $staffId);
        }

        $this->db->select(db_prefix() . 'jobreports.id,' . db_prefix() . 'jobreports.number,' . db_prefix() . 'clients.userid,' . db_prefix() . 'clients.company,' . db_prefix() . 'projects.id AS project_id,' . db_prefix() . 'projects.name,' . db_prefix() . 'jobreports.date');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'jobreports.clientid', 'left');
        $this->db->join(db_prefix() . 'projects', db_prefix() . 'projects.id = ' . db_prefix() . 'jobreports.project_id', 'left');
        $this->db->where('date IS NOT NULL');
        $this->db->where('date >=', $diff1);
        $this->db->where('date <=', $diff2);

        return $this->db->get(db_prefix() . 'jobreports')->result_array();
    }

    /**
     * Get the jobreports for the client given
     *
     * @param  integer|null $staffId
     * @param  integer $days
     *
     * @return array
     */
    public function get_client_jobreports($client = null)
    {
        /*
        if ($staffId && ! staff_can('view', 'jobreports', $staffId)) {
            $this->db->where('addedfrom', $staffId);
        }
        */

        $this->db->select(db_prefix() . 'jobreports.id,' . db_prefix() . 'jobreports.number,' . db_prefix() . 'jobreports.status,' . db_prefix() . 'clients.userid,' . db_prefix() . 'jobreports.hash,' . db_prefix() . 'projects.name,' . db_prefix() . 'jobreports.date');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'jobreports.clientid', 'left');
        $this->db->join(db_prefix() . 'projects', db_prefix() . 'projects.id = ' . db_prefix() . 'jobreports.project_id', 'left');
        $this->db->where('date IS NOT NULL');
        $this->db->where(db_prefix() . 'jobreports.status > ',1);
        $this->db->where(db_prefix() . 'jobreports.clientid =', $client->userid);

        return $this->db->get(db_prefix() . 'jobreports')->result_array();
    }


    /**
     * Get the jobreports about to expired in the given days
     *
     * @param  integer|null $staffId
     * @param  integer $days
     *
     * @return array
     */
    public function get_jobreports_between($staffId = null, $days = 7)
    {
        $diff1 = date('Y-m-d', strtotime('-' . $days . ' days'));
        $diff2 = date('Y-m-d', strtotime('+' . $days . ' days'));

        if ($staffId && ! staff_can('view', 'jobreports', $staffId)) {
            $this->db->where('addedfrom', $staffId);
        }

        $this->db->select(db_prefix() . 'jobreports.id,' . db_prefix() . 'jobreports.number,' . db_prefix() . 'clients.userid,' . db_prefix() . 'clients.company,' . db_prefix() . 'projects.name,' . db_prefix() . 'jobreports.date');
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'jobreports.clientid', 'left');
        $this->db->join(db_prefix() . 'projects', db_prefix() . 'projects.id = ' . db_prefix() . 'jobreports.project_id', 'left');
        $this->db->where('expirydate IS NOT NULL');
        $this->db->where('expirydate >=', $diff1);
        $this->db->where('expirydate <=', $diff2);

        return $this->db->get_compiled_select(db_prefix() . 'jobreports');
//        return $this->db->get(db_prefix() . 'jobreports')->get_compiled_select();
//        return $this->db->get(db_prefix() . 'jobreports')->result_array();
    }

    public function get_jobreport_unfinished_project(){
        $this->db->select([db_prefix() . 'jobreports.id', db_prefix() . 'jobreports.number', db_prefix() . 'clients.userid', db_prefix() . 'clients.company', db_prefix() . 'projects.id AS project_id', db_prefix() . 'projects.name']);
        $this->db->join(db_prefix() . 'clients', db_prefix() . 'clients.userid = ' . db_prefix() . 'jobreports.clientid', 'left');
        $this->db->join(db_prefix() . 'projects', db_prefix() . 'projects.id = ' . db_prefix() . 'jobreports.project_id', 'left');
        $this->db->group_by([db_prefix() . 'jobreports.id', db_prefix() . 'clients.userid',db_prefix() . 'projects.id']);

        $this->db->where(db_prefix() . 'projects.status != ' . '4');

        //return $this->db->get_compiled_select(db_prefix() . 'jobreports');
        return $this->db->get(db_prefix() . 'jobreports')->result_array();

    }


}
