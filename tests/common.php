<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/

function sql_error()
{
	$error =& ADODB_Pear_Error();
	include_once ('George/dBug.php'); new dBug('Bad SQL statement', 'Bad SQL statement. Error returned was "' . $error->message . '".', __LINE__, __FILE__);
}


class m2fUnitTestCase extends UnitTestCase 
{
	function setUp()
	{
		$this->read_log_fresh = TRUE;

		$this->set_paths();
		$this->create_test_objects();
		$this->configure_test_objects();

		new m2f_database_helper;
		$this->db =& m2f_db::get_instance();
		$this->db_prefix = m2f_db::prefix();
	}
	
	function tearDown()
	{
		return TRUE;
	}

	function set_paths()
	{
		$this->fileWriter_path = FILES_DIR . '/fileWriter_output';
		$this->fileWriter2_path = FILES_DIR . '/fileWriter2_output';
		$this->fileWriter3_path = FILES_DIR . '/fileWriter3_output';

		$this->smtp_dir = FILES_DIR . '/smtp/';
		$this->mbox_file = FILES_DIR . '/mbox';

		@unlink($this->fileWriter_path);
		@unlink($this->fileWriter2_path);
		@unlink($this->fileWriter3_path);
		
		$this->mbox_file_contents = "From George Crawford 2006 04:12\nTo: m2f@localhost\nFrom: \"George\" <test@mail2forum.com>\nSubject: test subject\nMessage-ID: <0123456789@domain.com>\n\nhello!\n\n";
	}
	
	function create_test_objects()
	{		
		$this->generic_message =& new m2f_generic_message();

		// set up channels ....
			// internal m2f channels:
		$this->randomTextGenerator =& m2f_factory::make_object('channels_randomTextGenerator');
		$this->randomTextGenerator2 =& m2f_factory::make_object('channels_randomTextGenerator');
		$this->fileWriter =& m2f_factory::make_object('channels_fileWriter');
		$this->fileWriter2 =& m2f_factory::make_object('channels_fileWriter');
		$this->fileWriter3 =& m2f_factory::make_object('channels_fileWriter');

			// real channels:
		$this->phpbb =& m2f_factory::make_object('channels_forum_phpbb');
		$this->mbox =& m2f_factory::make_object('channels_email_mbox');

			// ... POP is a bit more complicated ...
		$this->pear_pop =& $this->get_configured_mocked_PEAR_POP();
		$this->pop =& $this->get_mocked_POP($this->pear_pop);

			// ... SMTP is a bit more complicated ...
		$this->pear_smtp =& $this->get_configured_mocked_PEAR_SMTP();
		$this->smtp =& $this->get_mocked_SMTP($this->pear_smtp);
		
		// set up filters
		$this->toUppercase_filter =& m2f_factory::make_object('filters_toUppercase');
		$this->bbcodeParser_filter =& m2f_factory::make_object('filters_bbcodeParser');

		// set up routers
		$this->hasAngleBracket_router =& m2f_factory::make_object('routers_hasAngleBracket');
		$this->isAllUppercase_router =& m2f_factory::make_object('routers_isAllUppercase');
		$this->countOCharacters_router =& m2f_factory::make_object('routers_countOCharacters');
		
		// set up a few of chains
		$this->chain =& m2f_factory::make_object('chain');
		$this->chain2 =& m2f_factory::make_object('chain');		
		$this->chain3 =& m2f_factory::make_object('chain');		
	}
	
	function configure_test_objects()
	{		
		$this->chain->name = 'tester';
		$this->chain->description = 'test description';
		$this->chain2->name = 'tester2';
		$this->chain2->description = 'test description 2';
		
		$this->generic_message->body = 'body text';
		$this->generic_message->html_body = 'html body text';
		$this->generic_message->subject = 'test subject';
		$this->generic_message->author = 'George';
		$this->generic_message->author_email = 'test@mail2forum.com';

		$this->fileWriter->config['filepath'] = $this->fileWriter_path;
		$this->fileWriter2->config['filepath'] = $this->fileWriter2_path;
		$this->fileWriter3->config['filepath'] = $this->fileWriter3_path;

		$this->randomTextGenerator->config['useless_param'] = 'useless';
		$this->randomTextGenerator2->config['useless_param'] = 'useless2';
		
		$this->smtp->config['host'] = 'mail2forum.com';
		$this->smtp->config['port'] = 25;
		$this->smtp->config['mail_to'] = 'm2f@mail2forum.com';
		
		$this->mbox->config['path'] = $this->mbox_file;
		$this->mbox->max_msgs = 0;
		$this->mbox->delete_msgs = TRUE;
		
		$this->pop->config['host'] = 'mail2forum.com';
		$this->pop->config['port'] = 110;
		$this->pop->config['user'] = 'abc';
		$this->pop->config['pass'] = 'xyz';
		$this->pop->delete_msgs = TRUE;

		$this->phpbb->config['phpbb_root'] = '../m2f_DEV';
		$this->phpbb->config['forum_id'] = 1;
		$this->phpbb->config['user_id'] = -1;
		$this->phpbb->config['user_name'] = 'george';
		$this->phpbb->config['user_attach_sig'] = 0;
		
		$this->html_forum_post = array (
			'html_on' => true,
			'body' => <<<EOL
Message Body
special chars line ''single'' &amp; &quot;double&quot;
(next line empty)

HTML line break here&lt;br&gt;done
HTML entity space here&amp;nbsp;done
<b>bold HTML tag</b>
(next 2 lines empty)


[b:32caf6c588]bold[/b:32caf6c588][i:32caf6c588]italic[/i:32caf6c588]
[i:32caf6c588][b:32caf6c588]bold and italic[/b:32caf6c588][/i:32caf6c588]
[u:32caf6c588]underline[/u:32caf6c588]
[quote:32caf6c588]quote[/quote:32caf6c588]
[quote=&quot;George&quot;]quote George[/quote]
[quote=&quot;George&quot;][quote:32caf6c588]quote in a quote[/quote:32caf6c588][/quote]
[code:1:32caf6c588]code[/code:1:32caf6c588]
[list:32caf6c588][*:32caf6c588]list1
[*:32caf6c588]list2[/list:u:32caf6c588]
[list=1:32caf6c588][*:32caf6c588]ordered list1
[*:32caf6c588]ordered list2[/list:o:32caf6c588]
[list=a:32caf6c588][*:32caf6c588]alphabetic list1
[*:32caf6c588]alphabetic list2[/list:o:32caf6c588]
[list:32caf6c588][*:32caf6c588]list1
[*:32caf6c588]list2[list:32caf6c588][*:32caf6c588]list in a list 1
[*:32caf6c588]list in a list 2[/list:u:32caf6c588][/list:u:32caf6c588]
http://www.google.com
[url=http://www.google.com]Google link[/url]
[img:32caf6c588]http://www.google.com/images/logo.gif[/img:32caf6c588]
[url=http://www.google.com][img:32caf6c588]http://www.google.com/images/logo.gif[/img:32caf6c588][/url]
EOL
			, 'post_subject' => 'Message Subject \'\'single\'\' &amp; \\&quot;double\\&quot;',
			'forum_id' => '1',
			'topic_id' => 1,
			'post_id' => 1,
			'userdata' => 
			array (
				'user_id' => '2', 'user_active' => '1', 'username' => 'george', 'user_password' => '30258a6356b19700faa46a4fbd1f5485', 
				'user_session_time' => '1148480557', 'user_session_page' => '-4', 'user_lastvisit' => '1147117093', 'user_regdate' => '1119384872', 
				'user_level' => '1', 'user_posts' => '26', 'user_timezone' => '0.00', 'user_style' => '1', 'user_lang' => 'english', 
				'user_dateformat' => 'd M Y h:i a', 'user_new_privmsg' => '0', 'user_unread_privmsg' => '0', 'user_last_privmsg' => '0', 'user_emailtime' => NULL, 
				'user_viewemail' => '1', 'user_attachsig' => '0', 'user_allowhtml' => '1', 'user_allowbbcode' => '1', 'user_allowsmile' => '1', 
				'user_allowavatar' => '1', 'user_allow_pm' => '1', 'user_allow_viewonline' => '1', 'user_notify' => '0', 'user_notify_pm' => '1', 'user_popup_pm' => '1', 'user_rank' => '1', 'user_avatar' => '', 
				'user_avatar_type' => '0', 'user_email' => 'g.o.crawford@gmail.com', 'user_icq' => '', 'user_website' => '', 'user_from' => '', 
				'user_sig_bbcode_uid' => '87e4cc6ec7', 'user_aim' => '', 'user_yim' => '', 'user_msnm' => '', 'user_occ' => '', 'user_interests' => '', 
				'user_actkey' => '', 'user_newpasswd' => '', 'session_id' => 'dfb4f9b79f171ea8bc16bda70870db30', 'session_user_id' => '2', 
				'session_start' => '1148480359', 'session_time' => '1148480557', 'session_ip' => 'c0a80101', 'session_page' => '-4', 'session_logged_in' => '1', 
				'session_admin' => '1',
				'user_sig' => "Signature\n[b:87e4cc6ec7]bold BBCode[/b:87e4cc6ec7]\n<b>bold HTML</b>"
				),
			'mode' => 'newtopic',
			'attach_sig' => true,
			'bbcode_uid' => '32caf6c588',
			'smilies_on' => true,
			'post_username' => '',
		);


		$this->plain_forum_post = array (
			'html_on' => false,
			'body' => <<<EOL
Message Body
special chars line ''single'' &amp; \&quot;double\&quot;
(next line empty)

HTML line break here&lt;br&gt;done
HTML entity space here&amp;nbsp;done
&lt;b&gt;bold HTML tag&lt;/b&gt;
(next 2 lines empty)


line with a : in it [b:32caf6c588]bold[/b:32caf6c588]
[b:32caf6c588]bold[/b:32caf6c588][i:32caf6c588]italic[/i:32caf6c588]
[i:32caf6c588][b:32caf6c588]bold and italic[/b:32caf6c588][/i:32caf6c588]
[u:32caf6c588]underline[/u:32caf6c588]
[quote:32caf6c588]quote[/quote:32caf6c588]
[quote:32caf6c588=\"George\"]quote George[/quote:32caf6c588]
[quote:32caf6c588=\"George\"][quote:32caf6c588]quote in a quote[/quote:32caf6c588][/quote:32caf6c588]
[code:1:32caf6c588]code[/code:1:32caf6c588]
[list:32caf6c588][*:32caf6c588]list1
[*:32caf6c588]list2[/list:u:32caf6c588]
[list=1:32caf6c588][*:32caf6c588]ordered list1
[*:32caf6c588]ordered list2[/list:o:32caf6c588]
[list=a:32caf6c588][*:32caf6c588]alphabetic list1
[*:32caf6c588]alphabetic list2[/list:o:32caf6c588]
[list:32caf6c588][*:32caf6c588]list1
[*:32caf6c588]list2[list:32caf6c588][*:32caf6c588]list in a list 1
[*:32caf6c588]list in a list 2[/list:u:32caf6c588][/list:u:32caf6c588]
http://www.google.com
[url=http://www.google.com]Google link[/url]
[img:32caf6c588]http://www.google.com/images/logo.gif[/img:32caf6c588]
[url=http://www.google.com][img:32caf6c588]http://www.google.com/images/logo.gif[/img:32caf6c588][/url]
EOL
			, 'post_subject' => 'Message Subject \'\'single\'\' &amp; \\&quot;double\\&quot;',
			'forum_id' => '1',
			'topic_id' => 1,
			'post_id' => 1,
			'userdata' => 
			array (
				'user_id' => '2', 'user_active' => '1', 'username' => 'george', 'user_password' => '30258a6356b19700faa46a4fbd1f5485', 
				'user_session_time' => '1148480557', 'user_session_page' => '-4', 'user_lastvisit' => '1147117093', 'user_regdate' => '1119384872', 
				'user_level' => '1', 'user_posts' => '26', 'user_timezone' => '0.00', 'user_style' => '1', 'user_lang' => 'english', 
				'user_dateformat' => 'd M Y h:i a', 'user_new_privmsg' => '0', 'user_unread_privmsg' => '0', 'user_last_privmsg' => '0', 'user_emailtime' => NULL, 
				'user_viewemail' => '1', 'user_attachsig' => '0', 'user_allowhtml' => '1', 'user_allowbbcode' => '1', 'user_allowsmile' => '1', 
				'user_allowavatar' => '1', 'user_allow_pm' => '1', 'user_allow_viewonline' => '1', 'user_notify' => '0', 'user_notify_pm' => '1', 'user_popup_pm' => '1', 'user_rank' => '1', 'user_avatar' => '', 
				'user_avatar_type' => '0', 'user_email' => 'g.o.crawford@gmail.com', 'user_icq' => '', 'user_website' => '', 'user_from' => '', 
				'user_sig_bbcode_uid' => '87e4cc6ec7', 'user_aim' => '', 'user_yim' => '', 'user_msnm' => '', 'user_occ' => '', 'user_interests' => '', 
				'user_actkey' => '', 'user_newpasswd' => '', 'session_id' => 'dfb4f9b79f171ea8bc16bda70870db30', 'session_user_id' => '2', 
				'session_start' => '1148480359', 'session_time' => '1148480557', 'session_ip' => 'c0a80101', 'session_page' => '-4', 'session_logged_in' => '1', 
				'session_admin' => '1',
				'user_sig' => "Signature\n[b:87e4cc6ec7]bold BBCode[/b:87e4cc6ec7]\n<b>bold HTML</b>"
				),
			'mode' => 'newtopic',
			'attach_sig' => true,
			'bbcode_uid' => '32caf6c588',
			'smilies_on' => true,
			'post_username' => '',
		);
		
		$this->board_config = array (
  		'config_id' => '1', 'board_disable' => '0', 'sitename' => 'yourdomain.com', 'site_desc' => 'A _little_ text to describe your forum', 
  		'cookie_name' => 'phpbb2mysql2', 'cookie_path' => '/', 'cookie_domain' => '', 'cookie_secure' => '0', 'session_length' => '3600', 
  		'allow_html' => '1', 'allow_html_tags' => 'b,i,u,pre', 'allow_bbcode' => '1', 'allow_smilies' => '1', 'allow_sig' => '1', 'allow_namechange' => '0', 
  		'allow_theme_create' => '0', 'allow_avatar_local' => '0', 'allow_avatar_remote' => '0', 'allow_avatar_upload' => '0', 'enable_confirm' => '0', 
  		'override_user_style' => '0', 'posts_per_page' => '15', 'topics_per_page' => '50', 'hot_threshold' => '25', 'max_poll_options' => '10', 
  		'max_sig_chars' => '255', 'max_inbox_privmsgs' => '50', 'max_sentbox_privmsgs' => '25', 'max_savebox_privmsgs' => '50', 
  		'board_email_sig' => 'Thanks, The Management', 'board_email' => 'g.o.crawford@gmail.com', 'smtp_delivery' => '0', 'smtp_host' => '', 
  		'smtp_username' => '', 'smtp_password' => '', 'sendmail_fix' => '0', 'require_activation' => '0', 'flood_interval' => '0', 'board_email_form' => '0', 
  		'avatar_filesize' => '6144', 'avatar_max_width' => '80', 'avatar_max_height' => '80', 'avatar_path' => 'images/avatars', 
  		'avatar_gallery_path' => 'images/avatars/gallery', 'smilies_path' => 'images/smiles', 'default_style' => '1', 'default_dateformat' => 'D M d, Y g:i a', 
  		'board_timezone' => '0', 'prune_enable' => '1', 'privmsg_disable' => '0', 'gzip_compress' => '0', 'coppa_fax' => '', 'coppa_mail' => '', 
  		'record_online_users' => '1', 'record_online_date' => '1119384912', 'server_name' => 'gc.homedns.org', 'server_port' => '', 
  		'script_path' => '/m2f_DEV/', 'version' => '.0.15', 'board_startdate' => '1119384872', 'default_lang' => 'english', 'vote_graphic_length' => 205, 
  		'privmsg_graphic_length' => 175,
		);
		
		$this->phpbb->board_config = $this->board_config;	
	}
	

	function get_SMTP_message($return_headers=FALSE)
	{
		$handle = opendir($this->smtp_dir);
		while (false !== ($file = readdir($handle))) 
		{
			$latest_file = $file;
		}
		$sent = file_get_contents($this->smtp_dir . $latest_file);
		list($headers, $content) = split("\r\n\r\n", $sent);
		return $return_headers ? trim($sent) : trim($content);
	}
	
	function get_log_message($num = 1)
	{
		static $lines = array();
		if (!$lines || $this->read_log_fresh) 
		{
			$lines = file($this->log_file);
			$this->read_log_fresh = FALSE;
		}
		while ($num)
		{
			$ret = array_pop($lines);
			$num--;
		}
		return $ret;
	}
	
	function &get_mocked_PEAR_POP()
	{	
		if (!class_exists('PEAR_POP_mock'))
		{
			require_once('Net/POP3.php');
			Mock::generate('Net_POP3','PEAR_POP_mock');
		}
		
		$pear_pop =& new PEAR_POP_mock($this);
		return $pear_pop;
	}

	function &get_configured_mocked_PEAR_POP()
	{	
		$pear_pop =& $this->get_mocked_PEAR_POP();

		$pear_pop->setReturnValue('connect', TRUE);
		$pear_pop->setReturnValue('login', TRUE);
		$pear_pop->setReturnValue('numMsg', 3);
		$pear_pop->setReturnValue('getMsg', FALSE);
		$pear_pop->setReturnValueAt(0, 'getMsg', $this->mbox_file_contents);
		$pear_pop->setReturnValueAt(1, 'getMsg', str_replace('0123456789', '0000000000', $this->mbox_file_contents));
		$pear_pop->setReturnValueAt(2, 'getMsg', str_replace('0123456789', '1111111111', $this->mbox_file_contents));
		$pear_pop->setReturnValue('deleteMsg', TRUE);
		$pear_pop->setReturnValue('disconnect', TRUE);

		return $pear_pop;
	}
	
	function &get_mocked_POP(&$pear_pop)
	{
		if (!class_exists('POP_mock'))
		{
			require_once('channels/email/pop/pop.php');
			Mock::generatePartial('m2f_channels_email_pop','POP_mock', array());
		}
		
		$pop =& new POP_mock($this);
		
		$pop->pop =& $pear_pop;
		$pop->class = 'channels_email_pop';
		$pop->type = 'channel';
		$pop->properties = 2;

		$pop->host = 'mail2forum.com';
		$pop->port = 110;
		$pop->user = 'abc';
		$pop->pass = 'xyz';
		$pop->delete_msgs = TRUE;

		return $pop;
	}

	function &get_mocked_PEAR_SMTP()
	{	
		if (!class_exists('PEAR_SMTP_mock'))
		{
			require_once('Mail.php');
			require_once('Mail/smtp.php');
			Mock::generate('Mail_smtp','PEAR_SMTP_mock');
		}
		
		$pear_smtp =& new PEAR_SMTP_mock($this);
		return $pear_smtp;
	}

	function &get_configured_mocked_PEAR_SMTP()
	{	
		$pear_smtp =& $this->get_mocked_PEAR_SMTP();

		$pear_smtp->setReturnValue('send', TRUE);

		return $pear_smtp;
	}
	
	function &get_mocked_SMTP(&$pear_smtp)
	{
		if (!class_exists('SMTP_mock'))
		{
			require_once('channels/email/smtp/smtp.php');
			Mock::generatePartial('m2f_channels_email_smtp','SMTP_mock', array('_connect'));
		}
		
		$smtp =& new SMTP_mock($this);

		$smtp->smtp =& $pear_smtp;
		$smtp->class = 'channels_email_smtp';
		$smtp->type = 'channel';
		$smtp->properties = 1;

		$smtp->host = 'mail2forum.com';
		$smtp->port = 25;
		$smtp->mail_to = 'm2f@mail2forum.com';

		return $smtp;
	}
}


class m2f_database_helper
{
	
	function m2f_database_helper($install_channels = TRUE)
	{
		$this->db =& m2f_db::get_instance();
		$this->db_prefix = m2f_db::prefix();
		
		$db = m2f_conf::get('db_database');
		$this->db->Execute('drop database ' . $db);
		$this->db->Execute('create database ' . $db);
		$this->db->Execute('use ' . $db);
		
		include_once('admin/admin.common.php');
		$this->admin =& new m2f_admin_common;
		$this->admin->db =& $this->db;
		$this->admin->db_prefix =& $this->db_prefix;

		$this->admin->_install_database_tables();
		$this->admin->_populate_db();

		if ($install_channels) $this->install_channels();
	}
	
	function install_channels()
	{
		$channels = $this->admin->_get_uninstalled_channels();
		foreach ($channels as $channel)
		{
			$channel_array[] = $channel['path'];
		}
		$this->admin->_install_channels($channel_array);
	}
}


require_once('channels/email/pop/pop.php');
class mocked_POP extends m2f_channels_email_pop
{
	function mocked_POP(&$mock)
	{
		$this->pop =& $mock;
	}
}

    
?>