<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



class toUppercaseFilter extends m2fUnitTestCase 
{
	function TestUppercaseFilter()
	{
		$this->toUppercase_filter->message = $this->generic_message;
		$this->assertTrue($this->toUppercase_filter->filter());
		$this->assertIdentical($this->toUppercase_filter->message->body, strtoupper($this->generic_message->body));
	}

	function TestUppercaseFilterLog()
	{
		$this->chain->add_element($this->randomTextGenerator, 'in');
		$this->chain->add_element($this->toUppercase_filter);
		$this->chain->add_element($this->fileWriter, 'out');
		$this->assertTrue($this->chain->run());
		
		$this->assertWantedPattern('/Cleaning up/i', $this->get_log_message());
		$this->assertWantedPattern('/Wrote 1/i', $this->get_log_message());
		$this->assertWantedPattern('/Exporting.*File writer.*channel/i', $this->get_log_message());
		$this->assertWantedPattern('/Export starting/i', $this->get_log_message());
		$this->assertWantedPattern('/Adding 1 message .*file.*writer/i', $this->get_log_message());
		$this->assertWantedPattern('/Transforming/i', $this->get_log_message());
		$this->assertWantedPattern('/Filter/i', $this->get_log_message());
		$this->assertWantedPattern('/Generated random text/i', $this->get_log_message());
		$this->assertWantedPattern('/Importing.*Random.*channel/i', $this->get_log_message());
		$this->assertWantedPattern('/Import starting/i', $this->get_log_message());
		$this->assertWantedPattern('/Running chain/i', $this->get_log_message());
	}
	
}