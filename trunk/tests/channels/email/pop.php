<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



class pop extends m2fUnitTestCase 
{
	function TestFactoryCreatesChannelObjects()
	{
		$this->assertIsA($this->pop, 'm2f_channels_email_pop');
	}
	
	function TestChannelsCanImportOrExportOrBoth()
	{
		$this->assertEqual($this->pop->properties, M2F_CHANNEL_CAN_IMPORT);
	}

	function TestPOPReceivesMail()
	{
		$this->assertTrue(is_array($message_list =& $this->pop->import()));
		$this->assertIsA($message_list[0], 'm2f_generic_message');
	}

	function TestPOPImportReturnsCorrectID()
	{
		$this->assertTrue($messages =& $this->pop->import());
		$this->assertIdentical($messages[0]->id, '0123456789@domain.com');
	}
		
	function TestPOPDeletesMessages()
	{
		$this->pear_pop->expectCallCount('deleteMsg', 3);
		$this->assertIdentical(3, count($this->pop->import()));
		$this->pop->_delete(3);
		$this->pear_pop->tally();
	}
	
	function TestPOPDoesntDeleteMessages()
	{
		$this->pear_pop->expectNever('deleteMsg');
		$this->pop->delete_msgs = FALSE;
		$this->assertIdentical(3, count($this->pop->import()));
		$this->pear_pop->tally();
	}

	function TestPOPMessagesOnlyDeletedAfterExportFinished()
	{
		$this->pear_pop->expectCallCount('deleteMsg', 3);

		$this->assertTrue($this->chain->add_element($this->pop, 'in'));
		$this->assertTrue($this->chain->add_element($this->fileWriter, 'out'));
		
		$this->chain->_run_channel($this->chain->elements[0]);
		$this->chain->_run_channel($this->chain->elements[1]);

		$this->assertIdentical(0, $this->pear_pop->getCallCount('deletemsg'));

		$this->chain->do_clean_up();
		$this->pear_pop->tally();
	}
	
}