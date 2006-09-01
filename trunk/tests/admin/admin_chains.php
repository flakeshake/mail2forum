<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/


class TestAdminChains extends m2fUnitTestCase 
{
	function setUp()
	{
		new m2f_database_helper();
		$this->db =& m2f_db::get_instance();
		$this->db_prefix = m2f_db::prefix();
		
		$this->admin =& new m2f_admin_common;
		$this->admin->_connect_db();
	}
	
	function TestNoChainsYet()
	{
		$chains = $this->admin->_get_chains();
		$this->assertTrue(empty($chains));
		$this->assertEqual(0, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_chain'));
	}
	
	function TestCreateChain()
	{
		$this->assertTrue($this->admin->_create_chain('name', 'desc'));
		$chain = current($this->db->getAll('SELECT * FROM ' . $this->db_prefix . '_chain'));
		$this->assertEqual($chain['name'], 'name');
		$this->assertEqual($chain['description'], 'desc');
	}

	function TestRetrieveChain()
	{
		$this->assertTrue($this->admin->_create_chain('name', 'desc'));
		$this->assertTrue($chain = $this->admin->_get_chains(1));
		$this->assertEqual($chain->name, 'name');
		$this->assertEqual($chain->description, 'desc');
	}
	
	function TestAddChannelToChain()
	{
	}
}

class TestAdminUIChains extends WebTestCase 
{
/*
	function setUp()
	{
		require_once('tests/common.php');
		$this->db_helper = new m2f_database_helper;
		$this->db =& m2f_db::get_instance();
		$this->db_prefix = m2f_db::prefix();
		
		$path = 'http://' . $_SERVER['SERVER_NAME'] . ($_SERVER['SERVER_PORT'] == '80' ? '' : ':' . $_SERVER['SERVER_PORT']) . str_replace('tests/run.php', '', $_SERVER['PHP_SELF']) . 'admin/chains.php';
		$this->get($path);
	}
	
	// Chains
	function CreateNewChain()
	{
		$this->assertWantedPattern('/Chains/i');
		$this->assertTrue($this->setField('chain_name', 'test chain'));
		$this->assertTrue($this->setField('chain_description', 'test chain description'));

		$this->assertTrue($this->clickSubmit('Create chain'));
	}

	function TestCreateNewChain()
	{
		$this->assertEqual(0, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_chain'));
		$this->CreateNewChain();
		$this->assertEqual(1, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_chain'));

		$recordSet = $this->db->getAssoc('SELECT * FROM ' . $this->db_prefix . '_chain WHERE id=1');
		$this->assertEqual($recordSet[1]['name'], 'test chain');
		$this->assertEqual($recordSet[1]['description'], 'test chain description');
	}
	
	function TestDisplayExistingChains()
	{
		$this->CreateNewChain();
		$this->assertWantedPattern('/test chain/i');
		$this->assertWantedPattern('/test chain description/i');
	}

	function TestAddChannelToChain()
	{
		$this->CreateNewChain();
		$this->assertTrue($this->clickLink('Add channel to chain'));
		
		$this->assertTrue($this->setField('channel_id', 'File Writer')); 
		$this->assertTrue($this->clickSubmit('Add channel to chain'));
		
		$this->assertField('direction', 'out');
		$filepath = 'tests/log';
		$this->assertTrue($this->setField('filepath', $filepath)); 
		$this->assertTrue($this->clickSubmit('Save channel in chain'));
		
		$this->assertWantedPattern('/File Writer/i');
		
		$recordSet = $this->db->getAssoc('SELECT * FROM ' . $this->db_prefix . '_channels_filewriter WHERE id=1');
		$this->assertEqual($recordSet[1]['direction'], 'out');
		$this->assertEqual($recordSet[1]['filepath'], $filepath);
	}
	
//*/
}





?>