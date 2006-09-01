<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/


class TestAdminChannels extends m2fUnitTestCase 
{
	function setUp()
	{
		new m2f_database_helper(FALSE);
		$this->db =& m2f_db::get_instance();
		$this->db_prefix = m2f_db::prefix();
		
		$this->admin =& new m2f_admin_common;
		$this->admin->_connect_db();
	}
	
	function TestNoChannelsInstalled()
	{
		$this->assertEqual(0, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channel'));
		$this->assertTrue(count($this->admin->_get_uninstalled_channels()) >= 8);
	}
	
	function TestInstallChannel()
	{
		$randomTextGen = array('name' => 'randomTextGenerator', 'path' => 'channels/randomTextGenerator/randomTextGenerator.php');
		$this->assertTrue(in_array($randomTextGen, $this->admin->_get_uninstalled_channels()));
		$this->assertTrue($this->admin->_install_channels(array($randomTextGen['path'])));
		$installed = current($this->db->getAll('SELECT * FROM ' . $this->db_prefix . '_channel'));
		$this->assertEqual($installed['path'], 'channels/randomTextGenerator/randomTextGenerator.php');
		$this->assertEqual($installed['class'], 'channels_randomTextGenerator');
		$this->assertEqual($installed['id'], '1');
		$this->assertEqual($installed['properties'], M2F_CHANNEL_CAN_IMPORT);
		$this->assertFalse(in_array($randomTextGen, $this->admin->_get_uninstalled_channels()));
		$this->assertTrue($channels = $this->admin->_get_channels());
		$this->assertEqual(count($channels), 1);
		$this->assertEqual($channels[0]->class, 'channels_randomTextGenerator');
	}

	function TestInstallTwoChannels()
	{
		$this->assertTrue($this->admin->_install_channels(array('channels/randomTextGenerator/randomTextGenerator.php', 'channels/fileWriter/fileWriter.php')));
		$this->assertEqual(2, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channel'));
	}
}



class TestAdminUIChannels extends WebTestCase 
{

	function setUp()
	{
		require_once('tests/common.php');
		new m2f_database_helper(FALSE);
		$this->db =& m2f_db::get_instance();
		$this->db_prefix = m2f_db::prefix();
		
		$this->path = 'http://' . $_SERVER['SERVER_NAME'] . ($_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT']) . str_replace('tests/run.php', '', $_SERVER['PHP_SELF']) . 'admin/channels.php';
		$this->get($this->path);
	}

	// Installing Channels
	function TestAdminIndex() 
	{
		$this->assertWantedPattern('/Mail2Forum.*admin/i');
	}

	function TestUninstalledChannelsListDisplayed()
	{
		$this->assertWantedPattern('/phpBB/i');
		$this->assertWantedPattern('/random/i');
		$this->assertWantedPattern('/mbox/i');
	}
	
	function TestInstallChannel()
	{
		$this->assertEqual(0, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channel'));
		
		$this->assertTrue($this->assertField('channel_path[]', FALSE));
		$this->assertTrue($this->setField('channel_path[]', 'channels/forum/phpbb/phpbb.php'));
		$this->assertTrue($this->clickSubmit('Install Selected Channels'));
		
		$recordSet = $this->db->getAll('SELECT * FROM ' . $this->db_prefix . '_channel');
		$this->assertEqual(count($recordSet), 1);
		$set = $recordSet[0];
		$this->assertEqual($set['id'], 1);
		$this->assertEqual($set['path'], 'channels/forum/phpbb/phpbb.php');
		$this->assertWantedPattern('/phpBB.*installed/i');
		
		$this->assertTrue($this->clickLink('Install Channels'));
		$this->assertNoUnwantedPattern('#phpbb/phpbb#i');
		$this->assertWantedPattern('/mbox/i');
	}
	
	function TestInstallNothingDoesNothing()
	{
		$this->assertEqual(0, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channel'));
		
		$this->assertTrue($this->assertField('channel_path[]', FALSE));
		$this->assertTrue($this->clickSubmit('Install Selected Channels'));
		$this->assertEqual(0, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channel'));
		$this->assertWantedPattern('/you must select some channels/i');
	}

	function TestAddingChannelCreatesChannelTableInDB()
	{
		$this->assertTrue($this->assertField('channel_path[]', FALSE));
		$this->assertTrue($this->setField('channel_path[]', 'channels/forum/phpbb/phpbb.php'));
		$this->assertTrue($this->clickSubmit('Install Selected Channels'));
		
		$this->assertIdentical('0', $res = $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channels_forum_phpbb'));
	}
	
	function TestAddingChannelCreatesCorrectFieldsArray()
	{
		$this->assertTrue($this->assertField('channel_path[]', FALSE));
		$this->assertTrue($this->setField('channel_path[]', 'channels/fileWriter/fileWriter.php'));
		$this->assertTrue($this->clickSubmit('Install Selected Channels'));

		$fields = $this->db->getOne('SELECT channel_fields FROM ' . $this->db_prefix . '_channel');
		$this->assertEqual(array( array('name' => 'id', 'type' => 'integer', 'size' => 0),
															array('name' => 'direction', 'type' => 'string', 'size' => 3),
															array('name' => 'filepath', 'type' => 'string', 'size' => 100),
														), unserialize($fields));
	}
	
	function TestCannotInstallSameChannelTwice()
	{
		$this->assertTrue($this->assertField('channel_path[]', FALSE));
		$this->assertTrue($this->setField('channel_path[]', 'channels/email/mbox/mbox.php'));
		$this->assertTrue($this->clickSubmit('Install Selected Channels'));
		$this->assertIdentical('1', $res = $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channel'));

		$this->post($this->path, array('channel_path[]' => 'channels/email/mbox/mbox.php', 'action' => 'Install Selected Channels'));
		$this->assertWantedPattern('/mbox.*already installed/i');
		$this->assertIdentical('1', $res = $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channel'));
	}

//*/
	
}
?>