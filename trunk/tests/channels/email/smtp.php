<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



class smtp extends m2fUnitTestCase 
{
	function TestFactoryCreatesChannelObjects()
	{
		$this->assertIsA($this->smtp, 'm2f_channels_email_smtp');
	}
	
	function TestChannelsCanImportOrExportOrBoth()
	{
		$this->assertEqual($this->smtp->properties, M2F_CHANNEL_CAN_EXPORT);
	}
	
	function TestSMTPMailSent()
	{
		$this->pear_smtp->expectOnce('send');
		$this->smtp->add_message($this->generic_message);
		$this->assertTrue($this->smtp->export());
		$this->assertIdentical($this->smtp->_messages[0]->body, $this->generic_message->body);
		$this->pear_smtp->tally();
	}

	function TestSMTPLog()
	{
		$this->assertTrue($this->smtp->add_message($this->generic_message));
		$this->assertTrue($this->smtp->export());
		$this->assertWantedPattern('/Sent 1 email via SMTP/i', $this->get_log_message());
		$this->assertWantedPattern('/Exporting.*SMTP.*channel/i', $this->get_log_message());
		$this->assertWantedPattern('/Export starting/i', $this->get_log_message());
	}

	function TestPOPToSMTPLog()
	{
		$this->chain->add_element($this->pop, 'in');
		$this->chain->add_element($this->smtp, 'out');
		$this->chain->run();
		
		$this->assertWantedPattern('/Sent 3 emails via SMTP/i', $this->get_log_message(6));
	}
	
}