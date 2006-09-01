<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



class TestChains extends m2fUnitTestCase 
{

	function TestRandomTextToFileWriterChain()
	{
		$this->assertTrue($this->chain->add_element($this->randomTextGenerator, 'in'));
		$this->assertTrue($this->chain->add_element($this->fileWriter, 'out'));
		$this->assertTrue($this->chain->run());
		$this->assertIdentical(file_get_contents($this->fileWriter_path), $this->randomTextGenerator->_messages[0]->body);
	}

	function TestUppercaseFilterInChain()
	{		
		$this->assertTrue($this->chain->add_element($this->randomTextGenerator, 'in'));
		$this->assertTrue($this->chain->add_element($this->toUppercase_filter));
		$this->assertTrue($this->chain->add_element($this->fileWriter, 'out'));
		$this->assertTrue($this->chain->run());
		
		$this->assertIdentical(file_get_contents($this->fileWriter_path), strtoupper($this->randomTextGenerator->_messages[0]->body));
	}

	function TestComplexChainWithDifferentOutputs()
	{
		$this->assertTrue($this->chain->add_element($this->randomTextGenerator, 'in'));
		$this->assertTrue($this->chain->add_element($this->fileWriter, 'out'));
		$this->assertTrue($this->chain->add_element($this->toUppercase_filter));
		$this->assertTrue($this->chain->add_element($this->fileWriter2, 'out'));
		$this->assertTrue($this->chain->run());
		
		$this->assertIdentical(file_get_contents($this->fileWriter_path), $this->randomTextGenerator->_messages[0]->body);
		$this->assertIdentical(file_get_contents($this->fileWriter2_path), strtoupper($this->randomTextGenerator->_messages[0]->body));
	}

	function TestComplexChainWithMultipleInputs()
	{
		$this->assertTrue($this->chain->add_element($this->randomTextGenerator, 'in'));
		$this->assertTrue($this->chain->add_element($this->toUppercase_filter));
		$this->assertTrue($this->chain->add_element($this->randomTextGenerator2, 'in'));
		$this->assertTrue($this->chain->add_element($this->fileWriter, 'out'));
		$this->assertTrue($this->chain->run());

		$this->assertIdentical(file_get_contents($this->fileWriter_path), strtoupper($this->randomTextGenerator->_messages[0]->body) . $this->randomTextGenerator2->_messages[0]->body);
	}

	function TestChainWithNoOutputs()
	{
		$this->assertTrue($this->chain->add_element($this->fileWriter, 'out'));
		$this->assertTrue($this->chain->run());
		$this->assertIdentical(array(), $this->fileWriter->_messages);
	}

	function TestComplexChainWithSomeOutputsbeforeInputs()
	{
		$this->assertTrue($this->chain->add_element($this->randomTextGenerator, 'in'));
		$this->assertTrue($this->chain->add_element($this->fileWriter, 'out'));
		$this->assertTrue($this->chain->add_element($this->randomTextGenerator2, 'in'));
		$this->assertTrue($this->chain->add_element($this->fileWriter2, 'out'));
		$this->assertTrue($this->chain->run());
		
		$this->assertIdentical(file_get_contents($this->fileWriter_path), $this->randomTextGenerator->_messages[0]->body);
		$this->assertIdentical(file_get_contents($this->fileWriter2_path), $this->randomTextGenerator->_messages[0]->body . $this->randomTextGenerator2->_messages[0]->body);
	}






	// DB
	function TestDBConnection()
	{
		$rs = $this->db->execute('SELECT * FROM ' . $this->db_prefix . '_chain');
		$this->assertIsA($rs, 'ADORecordSet');
	}

	function TestSaveChainInDB()
	{
		$this->assertTrue(empty($this->chain->id));
		
		$mapper =& m2f_factory::make_object('chain', TRUE);
		$mapper->insert($this->chain);
		
		$this->assertEqual(1, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_chain'));
		$this->assertWantedPattern('/saving.*chain 1/i', $this->get_log_message());
	}
	
	function TestGetSavedChain()
	{
		$mapper =& m2f_factory::make_object('chain', TRUE);
		$mapper->insert($this->chain);

		$mapper2 =& m2f_factory::make_object('chain', TRUE);
		$mapper2->insert($this->chain2);
		
		$this->assertIdentical(1, $this->chain->id);
		$this->assertIdentical(2, $this->chain2->id);

		$this->assertWantedPattern('/saving.*chain 2/i', $this->get_log_message());
		$this->assertWantedPattern('/saving.*chain 1/i', $this->get_log_message());
	}


	function TestUpdatedChain()
	{
		$mapper =& m2f_factory::make_object('chain', TRUE);
		$mapper->insert($this->chain);
		
		$this->chain->name = 'new tester';
		$mapper->update($this->chain);

		$this->assertIdentical(1, $this->chain->id);
		$this->assertIdentical('new tester', $this->chain->name);
		
		$this->assertWantedPattern('/updating.*chain 1/i', $this->get_log_message());
		$this->assertWantedPattern('/saving.*chain 1/i', $this->get_log_message());
	}

	function TestDeletedChain()
	{
		$mapper =& m2f_factory::make_object('chain', TRUE);
		$mapper->insert($this->chain);

		$mapper->insert($this->chain2);

		$this->assertEqual(2, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_chain'));
		
		$mapper->delete(1);
		$this->assertEqual(1, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_chain'));
		
		$this->assertNull($mapper->get(1));
		
		$retrieved = $mapper->get(2);
		$this->assertIdentical('tester2', $retrieved->name);

		$this->assertWantedPattern('/Retrieving.*chain 2/i', $this->get_log_message());
		$this->assertWantedPattern('/not.*found/i', $this->get_log_message());
		$this->assertWantedPattern('/deleting.*chain 1/i', $this->get_log_message());
		$this->assertWantedPattern('/saving.*chain 2/i', $this->get_log_message());
		$this->assertWantedPattern('/saving.*chain 1/i', $this->get_log_message());
	}

	function TestCannotInsertSameChainTwice()
	{
		$mapper =& m2f_factory::make_object('chain', TRUE);
		$mapper->insert($this->chain);
		$mapper->insert($this->chain);

		$this->assertEqual(1, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_chain'));

		$this->assertWantedPattern('/updating.*chain 1/i', $this->get_log_message());
		$this->assertWantedPattern('/saving.*chain 1/i', $this->get_log_message());
	}

	// Chain with Elements 
	function TestAddChainWithTwoSimpleElements()
	{
		$this->chain->add_element($this->mbox, 'in');
		$this->chain->add_element($this->fileWriter, 'out');
		
		$mapper =& m2f_factory::make_object('chain', TRUE);
		$mapper->insert($this->chain);
		
		$this->assertIdentical(1, $this->chain->id);

		$this->assertEqual(1, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_chain'));
		$this->assertEqual(1, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channels_fileWriter'));
		$this->assertEqual(1, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channels_email_mbox'));
		$this->assertEqual(2, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_chain_elements'));
		
		$this->assertWantedPattern('/saving.*fileWriter 1/i', $this->get_log_message());
		$this->assertWantedPattern('/saving.*mbox 1/i', $this->get_log_message());
		$this->assertWantedPattern('/saving 2.*elements/i', $this->get_log_message());
		$this->assertWantedPattern('/saving.*chain 1/i', $this->get_log_message());
	}

	function TestStoredChainRunsProperly()
	{
		$handle = fopen($this->mbox_file, 'w');
		fwrite($handle, $this->mbox_file_contents);
		fclose($handle);

		$this->mbox->delete_msgs = FALSE;

		$this->chain->add_element($this->mbox, 'in');
		$this->chain->add_element($this->fileWriter, 'out');
		
		$mapper =& m2f_factory::make_object('chain', TRUE);
		$mapper->insert($this->chain);

		$retrieved_chain =& $mapper->get(1);
		$retrieved_chain->run();
		
		$this->assertIdentical(file_get_contents($this->fileWriter_path), 'hello!');
	}

	function TestUpdatedChainWithElements()
	{
		// add our test chain
		$this->chain->add_element($this->randomTextGenerator, 'in');
		$this->chain->add_element($this->fileWriter, 'out');
		$mapper =& m2f_factory::make_object('chain', TRUE);
		$mapper->insert($this->chain);

		// this is just to add another chain as a filler - to ensure non-consecutive ID numbers
		$this->chain2->add_element($this->randomTextGenerator2, 'in');
		$this->chain2->add_element($this->fileWriter2, 'out');
		$mapper =& m2f_factory::make_object('chain', TRUE);
		$mapper->insert($this->chain2);

		// alter details of chain1
		$this->randomTextGenerator->config['useless_param'] = 'changed';

		// add another channel to chain1
		$this->fileWriter3->config['filepath'] = 'new channel';
		$this->chain->add_element($this->fileWriter3, 'out');
		
		$mapper->update($this->chain);

		$this->assertEqual(2, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_chain'));
		$this->assertEqual(3, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channels_fileWriter'));
		$this->assertEqual(2, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channels_randomTextGenerator'));
		$this->assertEqual(5, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_chain_elements'));
		
		$retrieved =& $mapper->get(1);
		
		$mbox = $retrieved->elements[0];
		$fileWriter1 = $retrieved->elements[1];
		$fileWriter2 = $retrieved->elements[2];
		
		$this->assertIdentical('changed', $mbox->config['useless_param']);
		$this->assertIdentical($this->fileWriter_path, $fileWriter1->config['filepath']);
		$this->assertIdentical('new channel', $fileWriter2->config['filepath']);
	}

	function TestDeletedChainWithElements()
	{
		// add our test chain
		$this->chain->add_element($this->randomTextGenerator, 'in');
		$this->chain->add_element($this->fileWriter, 'out');
		$mapper =& m2f_factory::make_object('chain', TRUE);
		$mapper->insert($this->chain);

		// this is just to add another chain as a filler - to ensure non-consecutive ID numbers
		$this->chain2->add_element($this->randomTextGenerator2, 'in');
		$this->chain2->add_element($this->fileWriter2, 'out');
		$mapper =& m2f_factory::make_object('chain', TRUE);
		$mapper->insert($this->chain2);

		$this->assertEqual(2, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_chain'));
		$this->assertEqual(2, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channels_fileWriter'));
		$this->assertEqual(2, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channels_randomTextGenerator'));
		$this->assertEqual(4, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_chain_elements'));
		
		$mapper->delete(1);

		$this->assertEqual(1, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_chain'));
		$this->assertEqual(1, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channels_fileWriter'));
		$this->assertEqual(1, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channels_randomTextGenerator'));
		$this->assertEqual(2, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_chain_elements'));
	}
	
	function TestUpdatingNonExistantElementAddsInstead()
	{
		$mapper =& m2f_factory::make_object('chain', TRUE);
		$mapper->update($this->chain);
		
		$this->assertIdentical(1, $this->chain->id);
		$this->assertWantedPattern('/saving.*chain 1/i', $this->get_log_message());
	}
	
	function TestDeletingNonExistantElementReturnsNoError()
	{
		$mapper =& m2f_factory::make_object('chain', TRUE);
		$mapper->delete(1);
		$this->assertWantedPattern('/deleting.*chain 1/i', $this->get_log_message());
	}

	function TestAddingElementToChainWithNoDirectionGivesError()
	{
		$error = $this->chain->add_element($this->fileWriter);
		$this->assertTrue(m2f::is_error($error));
		$this->assertWantedPattern('/direction/i', $this->get_log_message());
	}

	function TestAddingElementToChainWithIncorrectDirectionGivesError()
	{
		$error = $this->chain->add_element($this->fileWriter, 'in');
		$this->assertTrue(m2f::is_error($error));

		$error = $this->chain->add_element($this->randomTextGenerator, 'out');
		$this->assertTrue(m2f::is_error($error));

		$this->assertWantedPattern('/randomTextGenerator.*export/i', $this->get_log_message());
		$this->assertWantedPattern('/filewriter.*import/i', $this->get_log_message());
	}

//*/
}


?>