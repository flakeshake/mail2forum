<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



class randomTextGenerator extends m2fUnitTestCase 
{
	function TestRandomTextLog()
	{
		$this->assertTrue($this->randomTextGenerator->import());
		$this->assertWantedPattern('/Generated random text/i', $this->get_log_message());
		$this->assertWantedPattern('/Importing.*Random.*channel/i', $this->get_log_message());
		$this->assertWantedPattern('/Import starting/i', $this->get_log_message());
	}

	
}