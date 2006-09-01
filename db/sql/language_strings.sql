<?php

/************************************************************
 *
 *	$Date: 2006-06-23 00:57:26 +0100 (Fri, 23 Jun 2006) $
 *	$Revision: 78 $
 *	$Author: georgeocrawford $
 *	$HeadURL: https://svn.sourceforge.net/svnroot/m2f/tests/admin.php $
 *
 /***********************************************************/

$command = 'INSERT INTO ' . $this->db_prefix . '_lang_strings (section,id,en) VALUES (?, ?, ?)';

$queries = array(
			// Core Language
			array('core', 'and', 'and'),
			array('core', '**M2F_PLURAL_TAG**', 's'),

			// m2f program language
			array('m2f', 'm2f_starting', 'm2f is starting'),
			array('m2f', 'running_chain', 'Running chain'),
			array('m2f', 'running_chain_num', 'Running chain %s'),
			array('m2f', 'adding_messages_channel', 'Adding %s message**M2F_PLURAL_TAG** to %s channel'),
			array('m2f', 'clean_up', 'Cleaning up: deleting messages and closing connections'),
			array('m2f', 'importing_channel', 'Import starting'),
			array('m2f', 'exporting_channel', 'Export starting'),
			array('m2f', 'cleaning_up', 'Cleaning up'),
			array('m2f', 'running_filter', 'Running filter'),
			array('m2f', 'running_router', 'Running router'),
			array('m2f', 'router_passed', 'Router matched condition "%s"'),
			array('m2f', 'routing_msg', 'Routing message %s'),
			array('m2f', 'saving_in_db', 'Saving %s %s in database'),
			array('m2f', 'updating_in_db', 'Updating %s %s in database'),
			array('m2f', 'searching_db', 'Searching database'),
			array('m2f', 'getting_from_db', 'Retrieving %s %s from database'),
			array('m2f', 'record_not_found', 'Record not found in database'),
			array('m2f', 'deleting_from_db', 'Deleting %s %s from database'),
			array('m2f', 'saving_chain_elements', 'Saving %s chain element**M2F_PLURAL_TAG** in database'),
			array('m2f', 'updating_chain_elements', 'Updating %s chain element**M2F_PLURAL_TAG** in database'),
			array('m2f', 'deleting_chain_elements', 'Deleting %s chain element**M2F_PLURAL_TAG** from database'),
			
			// Channels
			array('channels_randomtextgenerator', 'channel_name', 'Random Text Generator'),
			array('channels_randomtextgenerator', 'generated_text', 'Generated random text'),
			array('channels_randomtextgenerator', 'importing_text', 'Importing on Random Text Generator channel'),
			array('channels_randomtextgenerator', 'field_useless_param', 'Useless param'),
			array('channels_randomtextgenerator', 'field_descr_useless_param', 'Useless param description.'),
			
			array('channels_forum_phpbb', 'channel_name', 'phpBB 2'),
			array('channels_forum_phpbb', 'importing', 'Importing on phpBB channel'),
			array('channels_forum_phpbb', 'imported_post', 'Imported phpBB forum post'),
			array('channels_forum_phpbb', 'setting_subject', 'Setting message subject'),
			array('channels_forum_phpbb', 'set_subject', 'Message subject set to: "%s"'),
			array('channels_forum_phpbb', 'setting_html_body', 'Setting HTML body'),
			array('channels_forum_phpbb', 'set_html_body', 'HTML body set to: "%s"'),
			array('channels_forum_phpbb', 'setting_text_body', 'Setting plain text body'),
			array('channels_forum_phpbb', 'set_text_body', 'Plain text body set to: "%s"'),
			array('channels_forum_phpbb', 'exporting', 'Exporting on phpBB channel'),
			array('channels_forum_phpbb', 'posted_messages', 'Posted %s message**M2F_PLURAL_TAG** to the phpBB database'),
			array('channels_forum_phpbb', 'field_phpbb_root', 'phpBB root path'),
			array('channels_forum_phpbb', 'field_descr_phpbb_root', 'Path to your phpBB installation directory (full system path, or path relative to the m2f directory).'),
			array('channels_forum_phpbb', 'field_forum_id', 'Forum id'),
			array('channels_forum_phpbb', 'field_descr_forum_id', 'ID number of the phpBB forum you want to use.'),
			
			array('channels_forum_phpbb3', 'channel_name', 'phpBB 3'),

			array('channels_filewriter', 'channel_name', 'File Writer'),
			array('channels_filewriter', 'exporting_fileWriter', 'Exporting on File Writer channel'),
			array('channels_filewriter', 'wrote_file', 'Wrote %s message**M2F_PLURAL_TAG** to file'),
			array('channels_filewriter', 'field_filepath', 'Path to file'),
			array('channels_filewriter', 'field_descr_filepath', 'Enter the path for the file to write to (full system path, or path relative to the m2f directory).'),
			
			array('channels_email_phpMail', 'channel_name', 'PHP mail'),

			array('channels_email.common', 'email_transform', 'Transforming email message'),
			
			array('channels_email_smtp', 'channel_name', 'SMTP'),
			array('channels_email_smtp', 'exporting', 'Exporting on SMTP channel'),
			array('channels_email_smtp', 'sent_mail_smtp', 'Sent %s email**M2F_PLURAL_TAG** via SMTP'),
			
			array('channels_email_pop', 'channel_name', 'POP'),
			array('channels_email_pop', 'importing_pop', 'Importing on POP channel'),
			array('channels_email_pop', 'pop_login', 'Logging in to POP3 server'),
			array('channels_email_pop', 'pop_disconnect', 'Disconnecting from POP3 server'),
			array('channels_email_pop', 'pop_get_num_msgs', 'Getting number of messages'),
			array('channels_email_pop', 'pop_got_num_msgs', 'Found %s message**M2F_PLURAL_TAG**'),
			array('channels_email_pop', 'pop_delete_msg', 'Deleting message %s'),
			array('channels_email_pop', 'pop_get_message', 'Getting message %s'),
			array('channels_email_pop', 'field_delete_msgs', 'Delete messages?'),
			array('channels_email_pop', 'field_descr_delete_msgs', 'Should imported messages be deleted from the mail box?'),
			array('channels_email_pop', 'field_max_msgs', 'Number of messages to import'),
			array('channels_email_pop', 'field_descr_max_msgs', 'Number of messages to import'),
			
			array('channels_email_mbox', 'channel_name', 'Mbox'),
			array('channels_email_mbox', 'importing_mbox', 'Importing on Mbox channel'),
			array('channels_email_mbox', 'mbox_check_file', 'Checking mbox file permissions'),
			array('channels_email_mbox', 'mbox_opening', 'Opening mbox file'),
			array('channels_email_mbox', 'mbox_retrieving', 'Retrieving mbox messages'),
			array('channels_email_mbox', 'mbox_got_num_msgs', 'Found %s message**M2F_PLURAL_TAG**'),
			array('channels_email_mbox', 'mbox_deleting', 'Deleting retrieved messages'),
			array('channels_email_mbox', 'mbox_moving_temp_file', 'Moving temp mbox to new location'),
			array('channels_email_mbox', 'field_delete_msgs', 'Delete messages?'),
			array('channels_email_mbox', 'field_descr_delete_msgs', 'Should imported messages be deleted from the mail box?'),
			array('channels_email_mbox', 'field_path', 'Mbox file path'),
			array('channels_email_mbox', 'field_descr_path', 'Path to the mbox file  (full system path, or path relative to the m2f directory).'),
			array('channels_email_mbox', 'field_max_msgs', 'Number of messages to import'),
			array('channels_email_mbox', 'field_descr_max_msgs', 'Enter the maximum number of messages to import on each cycle of the script.'),
			
			// Filters
			array('filters_touppercase', 'toUppercase_filter', 'Transforming message body to uppercase'),
			
			array('filters_bbcodeparser', 'parsing', 'Starting BBCode parse'),
			array('filters_bbcodeparser', 'quote', 'Quote:'),
			array('filters_bbcodeparser', 'authored_quote', '%s wrote:'),
			array('filters_bbcodeparser', 'ascii_quote_before', "~~~~~~~~~~~~~~~~~~~~~~~~\n  %s\n"),
			array('filters_bbcodeparser', 'ascii_quote_after', "\n~~~~~~~~~~~~~~~~~~~~~~~~"),
			
			array('filters_bbcodeparser', 'html_quote_class', 'quote'),
			array('filters_bbcodeparser', 'html_quote_lable_class', 'quotelable'),
			
			// Routers
			array('routers_isAllUppercase', 'is_isAllUppercase_router', 'Checking if message body is all uppercase'),
			
			array('routers_hasAngleBracket', 'has_hasAngleBracket_router', 'Checking message body for ">" symbol'),
			
			array('routers_countocharacters', 'countOCharacters_router', 'Counting "o" characters in message body'),
			
			//Admin
			array('admin', 'title', 'Mail2Forum Admin'),
			array('admin', 'error', 'Error'),
			array('admin', 'message', 'Message'),
			array('admin', 'home', 'Home'),
			array('admin', 'missing_var', 'Form field [%s] must be completed.'),
			array('admin', 'missing_vars', 'Form fields [%s] must be completed.'),
			array('admin', 'field', 'Field'),
			array('admin', 'value', 'Value'),
			array('admin', 'no_fields', 'There are no configurable fields.'),
			array('admin', 'yes', 'yes'),
			array('admin', 'no', 'no'),
			
			array('admin', 'install_channels', 'Install Channels'),
			array('admin', 'install_selected_channels', 'Install Selected Channels'),
			array('admin', 'channel_already_installed', 'The channel at path [%s] was already installed.'),
			array('admin', 'invalid_channel_path', 'Invalid channel path: [%s]'),
			array('admin', 'channel_installed', 'Channel [%s] was successfully installed.'),
			array('admin', 'channels_installed', 'Channels [%s] were successfully installed.'),
			array('admin', 'no_uninstalled_channels', 'There are no uninstalled channels.'),
			array('admin', 'channels', 'Channels'),
			array('admin', 'channel', 'Channel'),
			array('admin', 'channel_in', 'Import'),
			array('admin', 'channel_out', 'Export'),
			array('admin', 'channel_path', 'Channel Path'),
			array('admin', 'no_channels_selected', 'You must select some channels to install!'),
			array('admin', 'channel_type_desc', 'Choose whether this channel will import or export messages'),
			array('admin', 'no_installed_channels', 'Please select channels to install.'),
			array('admin', 'unrecongnised_channel', 'Unrecongnised channel ID: [%s]'),
			array('admin', 'unspecified_channel', 'Unspecified channel ID'),
			array('admin', 'bad_permissions_for_channel_XML', 'The database schema file [%s] cannot be read. Please check permissions.'),
			array('admin', 'channel_field_too_long', 'The value for field [%s] was too long. Maximum length is [%s] characters.'),
			array('admin', 'channel_field_incorrect_type', 'Field [%s] should be of type [%s].'),
			array('admin', 'configure_channel_fields', 'Configure channel fields'),
			array('admin', 'configure_channel_direction', 'Configure channel direction'),
			array('admin', 'channel_direction', 'Channel direction'),
			array('admin', 'save_chain_channel_direction', 'Save channel direction'),
			
			array('admin', 'chains', 'Chains'),
			array('admin', 'chain', 'Chain'),
			array('admin', 'view_chain', 'View Chain'),
			array('admin', 'no_elements', 'This chain currently has no channels.'),
			array('admin', 'element_class', 'Channel type'),
			array('admin', 'element_direction', 'Channel direction'),
			array('admin', 'no_chains', 'There are no chains in the database.'),
			array('admin', 'create_new_chain', 'Create a new chain'),
			array('admin', 'chain_created', 'Chain successfully created.'),
			array('admin', 'chain_deleted', 'Chain successfully deleted.'),
			array('admin', 'create_chain', 'Create chain'),
			array('admin', 'delete_chain', 'Delete chain'),
			array('admin', 'chain_description', 'Description'),
			array('admin', 'chain_name', 'Name'),
			array('admin', 'add_chain_channel', 'Add channel to chain'),
			array('admin', 'save_chain_channel', 'Save channel in chain'),
			array('admin', 'chain_channel_created', 'Channel successfully added to chain.'),
			array('admin', 'unrecongnised_chain', 'Unrecongnised chain ID: [%s]'),
			array('admin', 'unspecified_chain', 'Unspecified chain ID'),
			array('admin', 'run_all_chains', 'Run all chains'),
			
			array('admin', 'schedule', 'Schedule'),

			array('admin', 'initialise', 'Install&nbsp;(again!)'),
		);
