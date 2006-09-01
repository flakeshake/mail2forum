<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



class mbox extends m2fUnitTestCase 
{

	function TestFactoryCreatesChannelObjects()
	{
		$this->assertIsA($this->mbox, 'm2f_channels_email_mbox');
	}
	
	function TestChannelsCanImportOrExportOrBoth()
	{
		$this->assertEqual($this->mbox->properties, M2F_CHANNEL_CAN_IMPORT);
	}
	
	function TestMboxProducesGenericMessage()
	{
		$handle = fopen($this->mbox_file, 'w');
		fwrite($handle, $this->mbox_file_contents);
		fclose($handle);

		$this->assertTrue($messages =& $this->mbox->import());
		$this->assertIsA($messages[0], 'm2f_generic_message');
	}

	function TestMboxProducesCorrectGenericMessage()
	{
		$handle = fopen($this->mbox_file, 'w');
		fwrite($handle, $this->mbox_file_contents);
		fclose($handle);

		$this->assertTrue($messages =& $this->mbox->import());
		$this->assertIsA($messages[0], 'm2f_generic_message');
		$this->assertIdentical($messages[0]->id, '0123456789@domain.com');
		$this->assertIdentical($messages[0]->subject, 'test subject');
		$this->assertIdentical($messages[0]->author, 'George');
		$this->assertIdentical($messages[0]->author_email, 'test@mail2forum.com');
		$this->assertIdentical($messages[0]->body, 'hello!');
	}

	function TestMboxCanImportTwoMessages()
	{
		$handle = fopen($this->mbox_file, 'w');
		fwrite($handle, $this->mbox_file_contents);
		fwrite($handle, str_replace('0123456789', '0000000000', $this->mbox_file_contents));
		fclose($handle);

		$this->assertTrue($messages =& $this->mbox->import());
		$this->assertIdentical(count($messages), 2);
		$this->assertIsA($messages[0], 'm2f_generic_message');
		$this->assertIsA($messages[1], 'm2f_generic_message');

		$this->assertIdentical($messages[0]->id, '0123456789@domain.com');
		$this->assertIdentical($messages[1]->id, '0000000000@domain.com');
	}

	function TestMboxDeletesMessages()
	{
		$handle = fopen($this->mbox_file, 'w');
		fwrite($handle, $this->mbox_file_contents);
		fwrite($handle, str_replace('0123456789', '0000000000', $this->mbox_file_contents));
		fclose($handle);

		$this->assertTrue($messages =& $this->mbox->import());
		$this->assertIdentical(count($messages), 2);
		
		$this->mbox->_delete(2);
		$this->assertIdentical('', file_get_contents($this->mbox_file));
	}

	function TestMboxOnlyImportsAndDeletesSpecifiedNumberOfMessages()
	{
		$handle = fopen($this->mbox_file, 'w');
		fwrite($handle, str_replace('0123456789', '0000000000', $this->mbox_file_contents));
		fwrite($handle, str_replace('0123456789', '1111111111', $this->mbox_file_contents));
		fwrite($handle, $this->mbox_file_contents);
		fclose($handle);
		
		$this->mbox->max_msgs = 2;

		$this->assertTrue($messages =& $this->mbox->import());
		$this->assertIdentical(count($messages), 2);

		$this->assertIdentical($messages[0]->id, '0000000000@domain.com');
		$this->assertIdentical($messages[1]->id, '1111111111@domain.com');
		
		$this->mbox->_delete(2);
		
		$this->assertIdentical($this->mbox_file_contents, file_get_contents($this->mbox_file));
	}

	function TestMboxMessagesOnlyDeletedAfterExportFinished()
	{
		$handle = fopen($this->mbox_file, 'w');
		fwrite($handle, $this->mbox_file_contents);
		fclose($handle);

		$this->assertTrue($this->chain->add_element($this->mbox, 'in'));
		$this->assertTrue($this->chain->add_element($this->fileWriter, 'out'));
		
		$this->chain->_run_channel($this->chain->elements[0]);
		$this->chain->_run_channel($this->chain->elements[1]);

		$this->assertIdentical($this->mbox_file_contents, file_get_contents($this->mbox_file));

		$this->chain->do_clean_up();

		$this->assertIdentical(file_get_contents($this->mbox_file), '');
	}


	//Errors
	function TestMboxPathError()
	{
		$this->mbox->config['path'] = '/asd';
		$error = $this->mbox->import();
		$this->assertTrue(m2f::is_error($error));
		$this->assertWantedPattern('/asd/i', $this->get_log_message());
	}

	function TestMboxBadPermissions()
	{
		touch($this->mbox_file);
		chmod($this->mbox_file, 0333);
		
		$error = $this->mbox->import();
		$this->assertTrue(m2f::is_error($error));
		
		$this->assertWantedPattern('/' . preg_quote($this->mbox_file, '/') . '/i', $this->get_log_message());
		
		unlink($this->mbox_file);
	}
	

	// DB
	function TestSaveMboxChannel()
	{
		$this->assertTrue(empty($this->mbox->id));
		
		$mapper =& m2f_factory::make_object('channels_email_mbox', TRUE);
		$mapper->insert($this->mbox);
		$this->assertEqual(1, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channels_email_mbox'));

		$this->assertWantedPattern('/saving.*mbox 1/i', $this->get_log_message());
	}

	function TestGetSavedMboxChannel()
	{
		$mapper =& m2f_factory::make_object('channels_email_mbox', TRUE);
		$mapper->insert($this->mbox);
		
		$retrieved =& $mapper->get(1);
		
		$this->assertIdentical($this->mbox_file, $retrieved->config['path']);
		$this->assertIdentical(TRUE, $retrieved->delete_msgs);
		$this->assertIdentical(0, $retrieved->max_msgs);
		
		$this->assertWantedPattern('/Retrieving.*mbox 1/i', $this->get_log_message());
	}

	function TestUpdatedMboxChannel()
	{
		$mapper =& m2f_factory::make_object('channels_email_mbox', TRUE);
		$mapper->insert($this->mbox);

		$this->mbox->config['path'] = 'bullshit';
		$this->mbox->delete_msgs = FALSE;
		$this->mbox->max_msgs = 7;
		$mapper->update($this->mbox);
		
		$updated = $mapper->get(1);
		
		$this->assertIdentical(1, $updated->id);
		$this->assertIdentical('bullshit', $updated->config['path']);
		$this->assertIdentical(FALSE, $updated->delete_msgs);
		$this->assertIdentical(7, $updated->max_msgs);
		
		$this->assertWantedPattern('/Retrieving.*mbox 1/i', $this->get_log_message());
		$this->assertWantedPattern('/updating.*mbox 1/i', $this->get_log_message());
	}

	function TestDeletedMboxChannel()
	{
		$mapper =& m2f_factory::make_object('channels_email_mbox', TRUE);
		$mapper->insert($this->mbox);

		$this->assertEqual(1, $this->db->getOne('SELECT COUNT(1) FROM ' . $this->db_prefix . '_channels_email_mbox'));
		
		$mapper->delete(1);
		$this->assertEqual(0, $this->db->getOne('SELECT id FROM ' . $this->db_prefix . '_channels_email_mbox'));

		$this->assertWantedPattern('/deleting.*mbox 1/i', $this->get_log_message());
		$this->assertWantedPattern('/saving.*mbox 1/i', $this->get_log_message());
	}
//*/
}