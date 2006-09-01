<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



class phpBB extends m2fUnitTestCase 
{
/*
	function TestFactoryCreatesChannelObjects()
	{
		$this->assertIsA($this->phpbb, 'm2f_channels_forum_phpbb');
	}
	
	function TestChannelsCanImportOrExportOrBoth()
	{
		$this->assertEqual($this->phpbb->properties, M2F_CHANNEL_CAN_IMPORT + M2F_CHANNEL_CAN_EXPORT);
	}
	
	// Imports
	function TestGenericMessageProduced()
	{
		$this->phpbb->forum_post = $this->plain_forum_post;
		$this->assertEqual(1, count($messages =& $this->phpbb->import()));
		$this->assertIsA(($message =& $messages[0]), 'm2f_generic_message');
	}

	function TestUnescapedSubject()
	{
		$this->phpbb->forum_post = $this->plain_forum_post;
		$this->assertTrue($messages =& $this->phpbb->import());
		$this->assertEqual($messages[0]->subject, 'Message Subject \'single\' & "double"');
	}

	function TestUnescapedBody()
	{
		$this->phpbb->forum_post = $this->plain_forum_post;
		$this->assertTrue($messages =& $this->phpbb->import());
		
		$this->assertWantedPattern('#\'single\' &amp; &quot;double&quot;#', $messages[0]->html_body);
		$this->assertWantedPattern('#\'single\' & "double"#', $messages[0]->body);
	}

	function TestAllowedHTMLMakesItThrough()
	{
		$this->phpbb->forum_post = $this->html_forum_post;
		$this->assertTrue($messages =& $this->phpbb->import());
		
		$this->assertWantedPattern('#\<b\>bold HTML tag\</b\>#', $messages[0]->html_body);
		$this->assertWantedPattern('#&amp;nbsp;#', $messages[0]->html_body);
		$this->assertWantedPattern('#&lt;br&gt;#', $messages[0]->html_body);

		$this->assertWantedPattern('#[^>]bold HTML tag[^<]#', $messages[0]->body);
		$this->assertWantedPattern('#&nbsp;#', $messages[0]->body);
		$this->assertWantedPattern('#\<br\>#', $messages[0]->body);
	}

	function TestDisallowedHTMLIsConverted()
	{
		$this->phpbb->forum_post = $this->plain_forum_post;
		$this->assertTrue($messages =& $this->phpbb->import());
		
		$this->assertWantedPattern('#&lt;br&gt;#', $messages[0]->html_body);
		$this->assertWantedPattern('#&amp;nbsp;#', $messages[0]->html_body);
		$this->assertWantedPattern('#&lt;b&gt;bold HTML tag&lt;/b&gt;#', $messages[0]->html_body);
		
		$this->assertWantedPattern('#\<br\>#', $messages[0]->body);
		$this->assertWantedPattern('#&nbsp;#', $messages[0]->body);
		$this->assertWantedPattern('#\<b\>bold HTML tag\</b\>#', $messages[0]->body);
	}

	function TestBBCodeUIDRemoved()
	{
		$this->phpbb->forum_post = $this->plain_forum_post;
		$this->assertTrue($messages =& $this->phpbb->import());
		
		$this->assertNoUnwantedPattern('/'.$this->plain_forum_post['bbcode_uid'].'/', $messages[0]->html_body);
		$this->assertNoUnwantedPattern('/\[[^]]*?(?<!http):[^]]*?\]/', $messages[0]->html_body);
		$this->assertNoUnwantedPattern('/'.$this->plain_forum_post['bbcode_uid'].'/', $messages[0]->body);
		$this->assertNoUnwantedPattern('/\[[^]]*?(?<!http):[^]]*?\]/', $messages[0]->body);
	}
	
	function TestBBCodeRemovalRegexp()
	{
		$this->phpbb->forum_post = $this->plain_forum_post;
		$this->assertTrue($messages =& $this->phpbb->import());
		
		$this->assertWantedPattern('#line with a : in it \[b\]bold\[\/b\]#', $messages[0]->body);
		$this->assertWantedPattern('#line with a : in it \[b\]bold\[\/b\]#', $messages[0]->html_body);
	}

	function TestLineBreaksNotConverted()
	{
		$this->phpbb->forum_post = $this->plain_forum_post;
		$this->assertTrue($messages =& $this->phpbb->import());

		$this->assertNoUnwantedPattern('/\<br \/\>' . "\n" . '/', $messages[0]->html_body);
		$this->assertNoUnwantedPattern('/\<br \/\>' . "\n" . '/', $messages[0]->body);
	}
	
	function TestBBCodeParse()
	{
		$this->phpbb->forum_post = $this->plain_forum_post;

		$this->assertTrue($this->chain->add_element($this->phpbb, 'in'));
		$this->assertTrue($this->chain->add_element($this->bbcodeParser_filter));
		$this->assertTrue($this->chain->run());

		$message = $this->chain->_messages[0];

		//include_once ('George/dBug.php'); new dBug('$this->plain_forum_post', $this->plain_forum_post['body'], __LINE__, __FILE__); echo '<hr>';include_once ('George/dBug.php'); new dBug('body', $message->body, __LINE__, __FILE__);echo '<hr>';echo $message->html_body;echo '<hr>';include_once ('George/dBug.php'); new dBug('html_body', $message->html_body, __LINE__, __FILE__);
	}
//*/

	// Exports
	function TestNoRootGiven()
	{
		unset($this->phpbb->config['phpbb_root']);
		
		$error = $this->phpbb->export();
		$this->assertTrue(m2f::is_error($error));
		$this->assertWantedPattern('/root path/i', $error->GetMessage());
	}

	function TestNonExistantRootGiven()
	{
		$this->phpbb->config['phpbb_root'] = 'asd';
		
		$error = $this->phpbb->export();
		$this->assertTrue(m2f::is_error($error));
		$this->assertWantedPattern('/invalid.*root path/i', $error->GetMessage());
	}

	function TestBadRootGiven()
	{
		$this->phpbb->config['phpbb_root'] = './';
		
		$error = $this->phpbb->export();
		$this->assertTrue(m2f::is_error($error));
		$this->assertWantedPattern('/phpBB installation/i', $error->GetMessage());
	}

	function TestNoIncFile()
	{
		@unlink($this->inc_file);

		$this->phpbb->config['phpbb_root'] = realpath('tests/files') . '/';
		$error = $this->phpbb->export();
		$this->assertTrue(m2f::is_error($error));
		$this->assertWantedPattern('/installation/i', $error->GetMessage());
	}

	function TestNoConfigFile()
	{
		@unlink($this->config_file);

		$this->phpbb->config['phpbb_root'] = realpath('tests/files') . '/';
		$error = $this->phpbb->export();
		$this->assertTrue(m2f::is_error($error));
		$this->assertWantedPattern('/read phpBB config/i', $error->GetMessage());
	}

	function TestIncompleteConfFile()
	{
		$handle = fopen($this->config_file, 'w');
		fwrite($handle, "<?php\n\$dbms = 'xxx';\$dbhost = 'localhost';\$dbname = 'xxx';\$dbuser = 'xxx';");
		fclose($handle);

		$this->phpbb->config['phpbb_root'] = realpath('tests/files') . '/';
		$error = $this->phpbb->export();
		$this->assertTrue(m2f::is_error($error));
		$this->assertWantedPattern('/file.*invalid/i', $error->GetMessage());
	}

	function TestBadConfigFile()
	{
		$handle = fopen($this->config_file, 'w');
		fwrite($handle, "<?php\n\$dbhost = 'localhost';\$dbname = 'xxx';\$dbuser = 'xxx';\$dbpasswd = 'xxx';\$table_prefix = 'xxx';define('PHPBB_INSTALLED', true);");
		fclose($handle);

		$this->phpbb->config['phpbb_root'] = realpath('tests/files') . '/';
		$error = $this->phpbb->export();
		$this->assertTrue(m2f::is_error($error));
		$this->assertWantedPattern('/dbms/i', $error->GetMessage());
	}

	function TestBadDBType()
	{
		$handle = fopen($this->config_file, 'w');
		fwrite($handle, "<?php\n\$dbms = 'xxx';\$dbhost = 'localhost';\$dbname = 'xxx';\$dbuser = 'xxx';\$dbpasswd = 'xxx';\$table_prefix = 'xxx';define('PHPBB_INSTALLED', true);");
		fclose($handle);

		$this->phpbb->config['phpbb_root'] = realpath('tests/files') . '/';
		$error = $this->phpbb->export();
		$this->assertTrue(m2f::is_error($error));
		$this->assertWantedPattern('/type.*xxx/i', $error->GetMessage());
	}

	function TestBadConfigValues()
	{
		$handle = fopen($this->config_file, 'w');
		fwrite($handle, "<?php\n\$dbms = 'mysql';\$dbhost = 'localhost';\$dbname = 'xxx';\$dbuser = 'xxx';\$dbpasswd = 'xxx';\$table_prefix = 'xxx';define('PHPBB_INSTALLED', true);");
		fclose($handle);

		$this->phpbb->config['phpbb_root'] = realpath('tests/files') . '/';
		$error = $this->phpbb->export();
		$this->assertTrue(m2f::is_error($error));
		$this->assertWantedPattern('/xxx/i', $error->GetMessage());
	}

	function TestNewTopicEnteredCorrectly()
	{
		$this->phpbb->add_message($this->generic_message);
		$this->assertTrue($this->phpbb->export());
		
		$recordSet = &$this->phpbb_db->execute('SELECT * FROM phpbb_posts');
		$this->assertEqual($recordSet->RecordCount(), 1);
		$set = $recordSet->fetchRow();
		$this->assertEqual($set['post_id'], 1);
		$this->assertEqual($set['topic_id'], 1);
		$this->assertEqual($set['forum_id'], 1);
		$this->assertEqual($set['poster_id'], -1);
		$this->assertEqual($set['post_username'], 'george');
		$this->assertEqual($set['enable_bbcode'], 1);
		$this->assertEqual($set['enable_html'], 0);
		$this->assertEqual($set['post_edit_time'], NULL);
		$this->assertEqual($set['post_edit_count'], 0);

		$recordSet = &$this->phpbb_db->execute('SELECT * FROM phpbb_posts_text');
		$this->assertEqual($recordSet->RecordCount(), 1);
		
		$recordSet = &$this->phpbb_db->execute('SELECT * FROM phpbb_topics');
		$this->assertEqual($recordSet->RecordCount(), 1);
		$set = $recordSet->fetchRow();
		$this->assertEqual($set['topic_first_post_id'], 1);
		$this->assertEqual($set['topic_last_post_id'], 1);
		
		$recordSet = &$this->phpbb_db->execute('SELECT * FROM phpbb_forums');
		$this->assertEqual($recordSet->RecordCount(), 1);
		$set = $recordSet->fetchRow();
		$this->assertEqual($set['forum_posts'], 1);
		$this->assertEqual($set['forum_last_post_id'], 1);
		$this->assertEqual($set['forum_topics'], 1);

		$recordSet = &$this->phpbb_db->execute('SELECT user_posts FROM phpbb_users where user_id = -1');
		$this->assertEqual($recordSet->fields('user_posts'), 1);
	}
	
	function TestReplyEnteredCorrectly()
	{
		$this->phpbb->add_message($this->generic_message);
		$this->assertTrue($this->phpbb->export());

		$this->phpbb->topic_id = 1;
		$this->assertTrue($this->phpbb->export());

		$recordSet = &$this->phpbb_db->execute('SELECT * FROM phpbb_posts');
		$this->assertEqual($recordSet->RecordCount(), 2);

		$recordSet = &$this->phpbb_db->execute('SELECT * FROM phpbb_posts_text');
		$this->assertEqual($recordSet->RecordCount(), 2);
		
		$recordSet = &$this->phpbb_db->execute('SELECT * FROM phpbb_topics');
		$this->assertEqual($recordSet->RecordCount(), 1);
		$set = $recordSet->fetchRow();
		$this->assertEqual($set['topic_first_post_id'], 1);
		$this->assertEqual($set['topic_last_post_id'], 2);
		$this->assertEqual($set['topic_replies'], 1);
		
		$recordSet = &$this->phpbb_db->execute('SELECT * FROM phpbb_forums');
		$this->assertEqual($recordSet->RecordCount(), 1);
		$set = $recordSet->fetchRow();
		$this->assertEqual($set['forum_posts'], 2);
		$this->assertEqual($set['forum_last_post_id'], 2);
		$this->assertEqual($set['forum_topics'], 1);

		$recordSet = &$this->phpbb_db->execute('SELECT user_posts FROM phpbb_users where user_id = -1');
		$this->assertEqual($recordSet->fields('user_posts'), 2);
	}
	
/*
	function TestPostTextEnteredCorrectly()
	{
		$this->phpbb->add_message($this->generic_message);
		$this->assertTrue($this->phpbb->export());
		
		$recordSet = &$this->phpbb_db->execute('SELECT * FROM phpbb_posts_text');
		$set = $recordSet->fetchRow();
		$this->assertEqual($set['post_subject'], $this->generic_message->subject);
		$this->assertEqual($set['post_text'], $this->generic_message->body);
	}
	
	function TestSearchWordsUpdated()
	{
		$this->phpbb->add_message($this->generic_message);
		$this->assertTrue($this->phpbb->export());
		
		$recordSet = $this->phpbb_db->execute('SELECT * FROM phpbb_search_wordlist');
		$set = $recordSet->getArray();
		$this->assertEqual($set, array (0 => array ( 'word_text' => 'body', 'word_id' => '1', 'word_common' => '0', ), 
																		1 => array ( 'word_text' => 'subject', 'word_id' => '2', 'word_common' => '0', ), 
																		2 => array ( 'word_text' => 'test', 'word_id' => '3', 'word_common' => '0', ), 
																		3 => array ( 'word_text' => 'text', 'word_id' => '4', 'word_common' => '0', )));

		$recordSet = $this->phpbb_db->execute('SELECT * FROM phpbb_search_wordmatch');
		$set = $recordSet->getArray();
		$this->assertEqual($set, array (0 => array ( 'post_id' => '1', 'word_id' => '1', 'title_match' => '0', ), 
																		1 => array ( 'post_id' => '1', 'word_id' => '4', 'title_match' => '0', ), 
																		2 => array ( 'post_id' => '1', 'word_id' => '2', 'title_match' => '1', ), 
																		3 => array ( 'post_id' => '1', 'word_id' => '3', 'title_match' => '1', )));

	}
	
	function TestBBCodeUIDAdded()
	{
		$this->generic_message->body = '[b]bold text[/b]';
		$this->phpbb->add_message($this->generic_message);
		$this->assertTrue($this->phpbb->export());
		$recordSet = &$this->phpbb_db->execute('SELECT * FROM phpbb_posts_text');
		$set = $recordSet->fetchRow();
		$this->assertEqual('[b:' . $set['bbcode_uid'] . ']bold text[/b:' . $set['bbcode_uid'] . ']', $set['post_text']);
	}

	function TestSingleQuoteNotEscaped()
	{
		$this->generic_message->body = 'george\'s';
		$this->phpbb->add_message($this->generic_message);
		$this->assertTrue($this->phpbb->export());
		$recordSet = &$this->phpbb_db->execute('SELECT * FROM phpbb_posts_text');
		$set = $recordSet->fetchRow();
		$this->assertEqual('george\'s', $set['post_text']);
	}

	function TestSpecialEntitiesRetained()
	{
		$this->generic_message->body = '&gt;&lt;&quot;&amp;';
		$this->phpbb->add_message($this->generic_message);
		$this->assertTrue($this->phpbb->export());
		$recordSet = &$this->phpbb_db->execute('SELECT * FROM phpbb_posts_text');
		$set = $recordSet->fetchRow();
		$this->assertEqual('&gt;&lt;&quot;&amp;', $set['post_text']);
	}

	function TestMessageTrimmed()
	{
		$this->generic_message->body = '  spaced out   ';
		$this->phpbb->add_message($this->generic_message);
		$this->assertTrue($this->phpbb->export());
		$recordSet = &$this->phpbb_db->execute('SELECT * FROM phpbb_posts_text');
		$set = $recordSet->fetchRow();
		$this->assertEqual('spaced out', $set['post_text']);
	}

	function TestAmpersandConvertedInEntity()
	{
		$this->generic_message->body = '&nbsp;';
		$this->phpbb->add_message($this->generic_message);
		$this->assertTrue($this->phpbb->export());
		$recordSet = &$this->phpbb_db->execute('SELECT * FROM phpbb_posts_text');
		$set = $recordSet->fetchRow();
		$this->assertEqual('&amp;nbsp;', $set['post_text']);
	}

	function TestAmpersandNotConvertedInDecimalEntity()
	{
		$this->generic_message->body = '&#123;';
		$this->phpbb->add_message($this->generic_message);
		$this->assertTrue($this->phpbb->export());
		$recordSet = &$this->phpbb_db->execute('SELECT * FROM phpbb_posts_text');
		$set = $recordSet->fetchRow();
		$this->assertEqual('&#123;', $set['post_text']);
	}

	function TestHTMLConvertedToEntities()
	{
		$this->generic_message->body = '<b>bold</b>';
		$this->phpbb->add_message($this->generic_message);
		$this->assertTrue($this->phpbb->export());
		$recordSet = &$this->phpbb_db->execute('SELECT * FROM phpbb_posts_text');
		$set = $recordSet->fetchRow();
		$this->assertEqual('&lt;b&gt;bold&lt;/b&gt;', $set['post_text']);
	}
	
	function TestBodySubjectAndUsernameCorrectlyEscaped()
	{
		$this->generic_message->subject = 'asd\'\"asd';
		$this->generic_message->body = 'asd\'\"asd';
		$this->phpbb->add_message($this->generic_message);
		$this->phpbb->config['user_name'] = 'asd\'\"asd';
		
		$this->assertTrue($this->phpbb->export());
		$recordSet = &$this->phpbb_db->execute('SELECT * FROM phpbb_posts');
		$set = $recordSet->fetchRow();
		$this->assertEqual('asd\'\&quot;asd', $set['post_username']);
		
		$recordSet = &$this->phpbb_db->execute('SELECT * FROM phpbb_posts_text');
		$set = $recordSet->fetchRow();
		$this->assertEqual('asd\'\&quot;asd', $set['post_subject']);
		$this->assertEqual('asd\'\&quot;asd', $set['post_text']);
	}

	function TestCurrentTimeEntered()
	{
		$this->phpbb->add_message($this->generic_message);
		$this->assertTrue($this->phpbb->export());
		$recordSet = &$this->phpbb_db->execute('SELECT * FROM phpbb_posts');
		$set = $recordSet->fetchRow();
		$this->assertTrue($set['post_time'] == time() || $set['post_time'] == time() - 1);
	}

	function TestBadSQLReturnsError()
	{
		$this->phpbb->add_message($this->generic_message);
		$this->phpbb->config['user_id'] = NULL;
		$error = $this->phpbb->export();
		$this->assertTrue(m2f::is_error($error));
		$this->assertWantedPattern('/SQL/i', $error->GetMessage());
	}


//*
	// Database Mapper for Export
	function TestSavePhpBBChannel()
	{
		$this->assertNull($this->phpbb->id);
		
		$mapper =& m2f_factory::make_object('channels_forum_phpbb', TRUE);
		$mapper->insert($this->phpbb);
		
		$this->assertEqual(1, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channels_forum_phpbb'));

		$this->assertWantedPattern('/saving.*phpbb 1/i', $this->get_log_message());
	}

	function TestGetSavedPhpbbChannel()
	{
		$mapper =& m2f_factory::make_object('channels_forum_phpbb', TRUE);
		$mapper->insert($this->phpbb);
		
		$retrieved =& $mapper->get(1);
		$this->assertEqual(1, $retrieved->config['forum_id']);

		$this->assertWantedPattern('/Retrieving.*phpbb 1/i', $this->get_log_message());
	}
	
	function TestUpdatedPhpBBChannel()
	{
		$mapper =& m2f_factory::make_object('channels_forum_phpbb', TRUE);
		$mapper->insert($this->phpbb);

		$this->phpbb->config['forum_id'] = 2;
		$this->phpbb->config['phpbb_root'] = 'asd';
		$mapper->update($this->phpbb);
		
		$updated = $mapper->get(1);

		$this->assertEqual(1, $updated->id);
		$this->assertEqual('asd', $updated->config['phpbb_root']);
		$this->assertEqual(2, $updated->config['forum_id']);
		
		$this->assertWantedPattern('/Retrieving.*phpbb 1/i', $this->get_log_message());
		$this->assertWantedPattern('/updating.*phpbb 1/i', $this->get_log_message());
	}

	function TestDeletedPhpBBChannel()
	{
		$mapper =& m2f_factory::make_object('channels_forum_phpbb', TRUE);
		$mapper->insert($this->phpbb);

		$this->assertEqual(1, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channels_forum_phpbb'));
		
		$mapper->delete(1);
		$this->assertEqual(0, $this->db->getOne('SELECT id FROM ' . $this->db_prefix . '_channels_forum_phpbb'));
		
		$this->assertWantedPattern('/deleting.*phpbb 1/i', $this->get_log_message());
		$this->assertWantedPattern('/saving.*phpbb 1/i', $this->get_log_message());
	}
	
	function TestRunStoredRandomTextToPhpBBChain()
	{
		$this->chain->add_element($this->randomTextGenerator, 'in');
		$this->chain->add_element($this->phpbb, 'out');
		$this->chain->add_element($this->fileWriter, 'out');
		
		$mapper =& m2f_factory::make_object('chain', TRUE);
		$mapper->insert($this->chain);

		$retrieved_chain = $mapper->get(1);

		$retrieved_chain->elements[1]->config['phpbb_root'] = '../m2f_DEV';
		$retrieved_chain->elements[1]->config['forum_id'] = 1;
		$retrieved_chain->elements[1]->config['user_id'] = -1;
		$retrieved_chain->elements[1]->config['user_name'] = 'george';
		$retrieved_chain->elements[1]->config['user_attach_sig'] = 0;

		$retrieved_chain->run();
		
		$recordSet = &$this->phpbb_db->execute('SELECT * FROM phpbb_posts_text');
		$set = $recordSet->fetchRow();
		$this->assertWantedPattern('#^(\w+ ){19}\w+$#', $set['post_text']);
	}
	
//*/


	function setUp()
	{
		parent::setUp();
		
		$this->phpbb_db = adoNewConnection('mysql');
		if (!$this->phpbb_db->pconnect('localhost', 'm2f_DEV', 'm2f_DEV', 'm2f_DEV')) sql_error();
		$this->phpbb_db->setFetchMode(ADODB_FETCH_ASSOC);
		
		$this->wipe_db();

		$this->phpbb->config['phpbb_root'] = realpath('../m2f_DEV') . '/';
		
		$this->config_file = realpath('tests/files') . '/config.php';
		$this->inc_file = realpath('tests/files') . '/extension.inc';

		$handle = fopen($this->inc_file, 'w');
		fwrite($handle, "<?php\n\$phpEx = 'php';");
		fclose($handle);
	}
	
	function tearDown()
	{
		parent::tearDown();
		
		$this->phpbb_db->close();
	}
	
	function wipe_db()
	{
		$sql = 'TRUNCATE TABLE phpbb_posts_text;
						TRUNCATE TABLE phpbb_posts;
						TRUNCATE TABLE phpbb_search_wordlist;
						TRUNCATE TABLE phpbb_search_wordmatch;
						TRUNCATE TABLE phpbb_topics;
						TRUNCATE TABLE phpbb_topics_watch;
						UPDATE phpbb_forums SET forum_posts = 0, forum_topics = 0, forum_last_post_id = 0;
						UPDATE phpbb_users SET user_posts = 0;';
		
		foreach (explode("\n", $sql) as $line)
		{
			if (!$this->phpbb_db->execute(trim($line))) sql_error();
		}
	}

}