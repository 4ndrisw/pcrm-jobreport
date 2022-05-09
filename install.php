<?php

defined('BASEPATH') or exit('No direct script access allowed');

if (!$CI->db->table_exists(db_prefix() . 'jobreports')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "jobreports` (
      `id` int(11) NOT NULL,
      `staff_id` int(11) NOT NULL DEFAULT 0,
      `sent` tinyint(1) NOT NULL DEFAULT 0,
      `datesend` datetime DEFAULT NULL,
      `clientid` int(11) NOT NULL DEFAULT 0,
      `deleted_customer_name` varchar(100) DEFAULT NULL,
      `project_id` int(11) NOT NULL DEFAULT 0,
      `number` int(11) NOT NULL DEFAULT 0,
      `prefix` varchar(50) DEFAULT NULL,
      `number_format` int(11) NOT NULL DEFAULT 0,
      `hash` varchar(32) DEFAULT NULL,
      `datecreated` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
      `date` date DEFAULT NULL,
      `addedfrom` int(11) NOT NULL DEFAULT 0,
      `status` int(11) NOT NULL DEFAULT 1,
      `clientnote` text DEFAULT NULL,
      `adminnote` text DEFAULT NULL,
      `invoiceid` int(11) DEFAULT NULL,
      `invoiced_date` datetime DEFAULT NULL,
      `terms` text DEFAULT NULL,
      `reference_no` varchar(100) DEFAULT NULL,
      `assigned` int(11) NOT NULL DEFAULT 0,
      `billing_street` varchar(200) DEFAULT NULL,
      `billing_city` varchar(100) DEFAULT NULL,
      `billing_state` varchar(100) DEFAULT NULL,
      `billing_zip` varchar(100) DEFAULT NULL,
      `billing_country` int(11) DEFAULT NULL,
      `shipping_street` varchar(200) DEFAULT NULL,
      `shipping_city` varchar(100) DEFAULT NULL,
      `shipping_state` varchar(100) DEFAULT NULL,
      `shipping_zip` varchar(100) DEFAULT NULL,
      `shipping_country` int(11) DEFAULT NULL,
      `include_shipping` tinyint(1) NOT NULL DEFAULT 0,
      `show_shipping_on_jobreport` tinyint(1) NOT NULL DEFAULT 1,
      `show_quantity_as` int(11) NOT NULL DEFAULT 1,
      `pipeline_order` int(11) DEFAULT 1,
      `is_expiry_notified` int(11) NOT NULL DEFAULT 0,
      `signed` tinyint(1) NOT NULL DEFAULT 0,
      `acceptance_firstname` varchar(50) DEFAULT NULL,
      `acceptance_lastname` varchar(50) DEFAULT NULL,
      `acceptance_email` varchar(100) DEFAULT NULL,
      `acceptance_date` datetime DEFAULT NULL,
      `acceptance_ip` varchar(40) DEFAULT NULL,
      `signature` varchar(40) DEFAULT NULL,
      `short_link` varchar(100) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'jobreports`
      ADD PRIMARY KEY (`id`),
      ADD UNIQUE( `number`),
      ADD KEY `signed` (`signed`),
      ADD KEY `status` (`status`),
      ADD KEY `clientid` (`clientid`),
      ADD KEY `project_id` (`project_id`),
      ADD KEY `date` (`date`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'jobreports`
      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}


if (!$CI->db->table_exists(db_prefix() . 'jobreport_members')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "jobreport_members` (
      `id` int(11) NOT NULL,
      `jobreport_id` int(11) NOT NULL DEFAULT 0,
      `staff_id` int(11) NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'jobreport_members`
      ADD PRIMARY KEY (`id`),
      ADD KEY `staff_id` (`staff_id`),
      ADD KEY `jobreport_id` (`jobreport_id`) USING BTREE;');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'jobreport_members`
      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

if (!$CI->db->table_exists(db_prefix() . 'jobreport_activity')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "jobreport_activity` (
  `id` int(11) NOT NULL,
  `rel_type` varchar(20) DEFAULT NULL,
  `rel_id` int(11) NOT NULL,
  `description` text NOT NULL,
  `additional_data` text DEFAULT NULL,
  `staffid` varchar(11) DEFAULT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `date` datetime NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'jobreport_activity`
        ADD PRIMARY KEY (`id`),
        ADD KEY `date` (`date`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'jobreport_activity`
      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

if (!$CI->db->table_exists(db_prefix() . 'jobreport_items')) {
    $CI->db->query('CREATE TABLE `' . db_prefix() . "jobreport_items` (
      `id` int(11) NOT NULL,
      `rel_id` int(11) NOT NULL,
      `rel_type` varchar(15) NOT NULL,
      `description` mediumtext NOT NULL,
      `long_description` mediumtext DEFAULT NULL,
      `qty` decimal(15,2) NOT NULL,
      `unit` varchar(40) DEFAULT NULL,
      `item_order` int(11) DEFAULT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=" . $CI->db->char_set . ';');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'jobreport_items`
      ADD PRIMARY KEY (`id`),
      ADD KEY `rel_id` (`rel_id`);');

    $CI->db->query('ALTER TABLE `' . db_prefix() . 'jobreport_items`
      MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1');
}

$CI->db->query("
INSERT INTO `tblemailtemplates` (`type`, `slug`, `language`, `name`, `subject`, `message`, `fromname`, `fromemail`, `plaintext`, `active`, `order`) VALUES
('jobreport', 'jobreport-send-to-client', 'english', 'Send jobreport to Customer', 'jobreport # {jobreport_number} created', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">Please find the attached jobreport <strong># {jobreport_number}</strong></span><br /><br /><span style=\"font-size: 12pt;\"><strong>jobreport status:</strong> {jobreport_status}</span><br /><br /><span style=\"font-size: 12pt;\">You can view the jobreport on the following link: <a href=\"{jobreport_link}\">{jobreport_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">We look forward to your communication.</span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}<br /></span>', '{companyname} | CRM', '', 0, 1, 0),
('jobreport', 'jobreport-already-send', 'english', 'jobreport Already Sent to Customer', 'jobreport # {jobreport_number} ', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /> <br /><span style=\"font-size: 12pt;\">Thank you for your jobreport request.</span><br /> <br /><span style=\"font-size: 12pt;\">You can view the jobreport on the following link: <a href=\"{jobreport_link}\">{jobreport_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">Please contact us for more information.</span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('jobreport', 'jobreport-declined-to-staff', 'english', 'jobreport Declined (Sent to Staff)', 'Customer Declined jobreport', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) declined jobreport with number <strong># {jobreport_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the jobreport on the following link: <a href=\"{jobreport_link}\">{jobreport_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('jobreport', 'jobreport-accepted-to-staff', 'english', 'jobreport Accepted (Sent to Staff)', 'Customer Accepted jobreport', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) accepted jobreport with number <strong># {jobreport_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the jobreport on the following link: <a href=\"{jobreport_link}\">{jobreport_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('jobreport', 'jobreport-thank-you-to-customer', 'english', 'Thank You Email (Sent to Customer After Accept)', 'Thank for you accepting jobreport', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /> <br /><span style=\"font-size: 12pt;\">Thank for for accepting the jobreport.</span><br /> <br /><span style=\"font-size: 12pt;\">We look forward to doing business with you.</span><br /> <br /><span style=\"font-size: 12pt;\">We will contact you as soon as possible.</span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('jobreport', 'jobreport-expiry-reminder', 'english', 'jobreport Expiration Reminder', 'jobreport Expiration Reminder', '<p><span style=\"font-size: 12pt;\">Hello {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">The jobreport with <strong># {jobreport_number}</strong> will expire on <strong>{jobreport_expirydate}</strong></span><br /><br /><span style=\"font-size: 12pt;\">You can view the jobreport on the following link: <a href=\"{jobreport_link}\">{jobreport_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span></p>', '{companyname} | CRM', '', 0, 1, 0),
('jobreport', 'jobreport-send-to-client', 'english', 'Send jobreport to Customer', 'jobreport # {jobreport_number} created', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /><br /><span style=\"font-size: 12pt;\">Please find the attached jobreport <strong># {jobreport_number}</strong></span><br /><br /><span style=\"font-size: 12pt;\"><strong>jobreport status:</strong> {jobreport_status}</span><br /><br /><span style=\"font-size: 12pt;\">You can view the jobreport on the following link: <a href=\"{jobreport_link}\">{jobreport_number}</a></span><br /><br /><span style=\"font-size: 12pt;\">We look forward to your communication.</span><br /><br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}<br /></span>', '{companyname} | CRM', '', 0, 1, 0),
('jobreport', 'jobreport-already-send', 'english', 'jobreport Already Sent to Customer', 'jobreport # {jobreport_number} ', '<span style=\"font-size: 12pt;\">Dear {contact_firstname} {contact_lastname}</span><br /> <br /><span style=\"font-size: 12pt;\">Thank you for your jobreport request.</span><br /> <br /><span style=\"font-size: 12pt;\">You can view the jobreport on the following link: <a href=\"{jobreport_link}\">{jobreport_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">Please contact us for more information.</span><br /> <br /><span style=\"font-size: 12pt;\">Kind Regards,</span><br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('jobreport', 'jobreport-declined-to-staff', 'english', 'jobreport Declined (Sent to Staff)', 'Customer Declined jobreport', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) declined jobreport with number <strong># {jobreport_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the jobreport on the following link: <a href=\"{jobreport_link}\">{jobreport_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('jobreport', 'jobreport-accepted-to-staff', 'english', 'jobreport Accepted (Sent to Staff)', 'Customer Accepted jobreport', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) accepted jobreport with number <strong># {jobreport_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the jobreport on the following link: <a href=\"{jobreport_link}\">{jobreport_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0),
('jobreport', 'staff-added-as-project-member', 'english', 'Staff Added as Project Member', 'New project assigned to you', '<p>Hi <br /><br />New jobreport has been assigned to you.<br /><br />You can view the jobreport on the following link <a href=\"{jobreport_link}\">jobreport__number</a><br /><br />{email_signature}</p>', '{companyname} | CRM', '', 0, 1, 0),
('jobreport', 'jobreport-accepted-to-staff', 'english', 'jobreport Accepted (Sent to Staff)', 'Customer Accepted jobreport', '<span style=\"font-size: 12pt;\">Hi</span><br /> <br /><span style=\"font-size: 12pt;\">Customer ({client_company}) accepted jobreport with number <strong># {jobreport_number}</strong></span><br /> <br /><span style=\"font-size: 12pt;\">You can view the jobreport on the following link: <a href=\"{jobreport_link}\">{jobreport_number}</a></span><br /> <br /><span style=\"font-size: 12pt;\">{email_signature}</span>', '{companyname} | CRM', '', 0, 1, 0);
");
/*
 *
 */

// Add options for jobreports
add_option('delete_only_on_last_jobreport', 1);
add_option('jobreport_prefix', 'BAPP-');
add_option('next_jobreport_number', 1);
add_option('default_jobreport_assigned', 9);
add_option('jobreport_number_decrement_on_delete', 0);
add_option('jobreport_number_format', 4);
add_option('jobreport_year', date('Y'));
add_option('exclude_jobreport_from_client_area_with_draft_status', 1);
add_option('predefined_clientnote_jobreport', '- Staf diatas untuk melakukan riksa uji pada peralatan tersebut.<br />
- Staf diatas untuk membuat dokumentasi riksa uji sesuai kebutuhan.');
add_option('predefined_terms_jobreport', '- Dokumen ini diterbitkan melalui Aplikasi CRM, tidak memerlukan tanda tangan basah dari PT. Cipta Mas Jaya.');
add_option('jobreport_due_after', 1);
add_option('allow_staff_view_jobreports_assigned', 1);
add_option('show_assigned_on_jobreports', 1);
add_option('require_client_logged_in_to_view_jobreport', 0);

add_option('show_project_on_jobreport', 1);
add_option('jobreports_pipeline_limit', 1);
add_option('default_jobreports_pipeline_sort', 1);
add_option('jobreport_accept_identity_confirmation', 1);


/*

DROP TABLE `tbljobreports`;
DROP TABLE `tbljobreport_activity`, `tbljobreport_items`, `tbljobreport_members`;
delete FROM `tbloptions` WHERE `name` LIKE '%jobreport%';
DELETE FROM `tblemailtemplates` WHERE `type` LIKE 'jobreport';



*/