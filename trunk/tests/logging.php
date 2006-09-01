<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



class TestLogging extends m2fUnitTestCase 
{
	function TestPluralTagLog()
	{
		$this->assertTrue($this->pop->import());
		
		$this->assertWantedPattern('/Found 3 messages/i', $this->get_log_message(7));
	}
	
	function TestSingularTagLog()
	{
		$handle = fopen($this->mbox_file, 'w');
		fwrite($handle, $this->mbox_file_contents);
		fclose($handle);
		$this->assertTrue($this->mbox->import());

		$this->assertWantedPattern('/Found 1 message(?!\(s\))/i', $this->get_log_message(2));
	}

	function TestDoubleLog()
	{
		$this->chain->add_element($this->randomTextGenerator, 'in');
		$this->chain->add_element($this->fileWriter, 'out');
		$this->assertTrue($this->chain->run());
		
		$this->assertWantedPattern('/Cleaning up/i', $this->get_log_message());
		$this->assertWantedPattern('/Wrote 1 message to file/i', $this->get_log_message());
		$this->assertWantedPattern('/Exporting.*File.*channel/i', $this->get_log_message());
		$this->assertWantedPattern('/Export starting/i', $this->get_log_message());
		$this->assertWantedPattern('/Adding 1 message .*file.*writer/i', $this->get_log_message());
		$this->assertWantedPattern('/Generated random text/i', $this->get_log_message());
		$this->assertWantedPattern('/Importing.*Random.*channel/i', $this->get_log_message());
		$this->assertWantedPattern('/Import starting/i', $this->get_log_message());
		$this->assertWantedPattern('/Running chain/i', $this->get_log_message());
	}

	
//*/

}
    
    
?>