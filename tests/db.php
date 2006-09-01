<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



class TestDB extends m2fUnitTestCase 
{
//*

	// File Writer 
	function TestSaveFileWriterChannel()
	{
		$this->assertTrue(empty($this->fileWriter->id));
		
		$mapper =& m2f_factory::make_object('channels_fileWriter', TRUE);
		$mapper->insert($this->fileWriter);
		
		$this->assertEqual(1, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channels_fileWriter'));

		$this->assertWantedPattern('/saving.*fileWriter 1/i', $this->get_log_message());
	}

	function TestGetSavedFileWriterChannel()
	{
		$mapper =& m2f_factory::make_object('channels_fileWriter', TRUE);
		$mapper->insert($this->fileWriter);
		
		$retrieved =& $mapper->get(1);
		$this->assertIdentical($this->fileWriter_path, $retrieved->config['filepath']);

		$this->assertWantedPattern('/Retrieving.*fileWriter 1/i', $this->get_log_message());
	}

	function TestUpdatedFileWriterChannel()
	{
		$mapper =& m2f_factory::make_object('channels_fileWriter', TRUE);
		$mapper->insert($this->fileWriter);

		$this->fileWriter->config['filepath'] = 'bullshit';
		$mapper->update($this->fileWriter);
		
		$updated = $mapper->get(1);

		$this->assertIdentical(1, $updated->id);
		$this->assertIdentical('bullshit', $updated->config['filepath']);
		
		$this->assertWantedPattern('/Retrieving.*fileWriter 1/i', $this->get_log_message());
		$this->assertWantedPattern('/updating.*fileWriter 1/i', $this->get_log_message());
	}

	function TestDeletedFileWriterChannel()
	{
		$mapper =& m2f_factory::make_object('channels_fileWriter', TRUE);
		$mapper->insert($this->fileWriter);

		$this->assertEqual(1, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channels_fileWriter'));
		
		$mapper->delete(1);
		$this->assertEqual(0, $this->db->getOne('SELECT id FROM ' . $this->db_prefix . '_channels_fileWriter'));
		
		$this->assertWantedPattern('/deleting.*fileWriter 1/i', $this->get_log_message());
		$this->assertWantedPattern('/saving.*fileWriter 1/i', $this->get_log_message());
	}

	function TestInsertTwiceResultsInUpdate()
	{
		$mapper =& m2f_factory::make_object('channels_fileWriter', TRUE);
		$mapper->insert($this->fileWriter);

		$this->fileWriter->config['filepath'] = 'bullshit';
		$mapper->insert($this->fileWriter);

		$this->assertEqual(1, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channels_fileWriter'));

		$updated = $mapper->get(1);
		$this->assertIdentical(1, $updated->id);
		$this->assertIdentical('bullshit', $updated->config['filepath']);
	}

//*


//*/


}

?>