<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



class TestChannels extends m2fUnitTestCase 
{
//*

	function TestFactoryCreatesChannelObjects()
	{
		$this->assertIsA($this->randomTextGenerator, 'm2f_channels_randomTextGenerator');
		$this->assertIsA($this->fileWriter, 'm2f_channels_fileWriter');
	}

	function TestChannelsCanImportOrExportOrBoth()
	{
		$this->assertEqual($this->randomTextGenerator->properties, M2F_CHANNEL_CAN_IMPORT);
		$this->assertEqual($this->fileWriter->properties, M2F_CHANNEL_CAN_EXPORT);
	}
	
	function TestRandomTextGeneratorReturns20Words()
	{
		$this->assertTrue($messages =& $this->randomTextGenerator->import());
		$this->assertIsA($messages[0], 'm2f_generic_message');
		$this->assertWantedPattern('/(\w+ ){19}\w+/', $messages[0]->body);
	}
	
	function TestFileWriterWritesToFile()
	{
		$this->assertTrue($messages =& $this->randomTextGenerator->import());
		$this->fileWriter->add_message($messages[0]);
		$this->assertTrue($this->fileWriter->export());
		$this->assertIdentical(file_get_contents($this->fileWriter_path), $messages[0]->body);
	}
	
	function TestImportReturnsReference()
	{
		$this->assertTrue($messages =& $this->randomTextGenerator->import());
		$this->assertReference($messages, $this->randomTextGenerator->_messages);
	}
	
	function TestImportReturnsGeneratedID()
	{
		$this->assertTrue($messages =& $this->randomTextGenerator->import());
		$this->assertTrue(($messages[0]->id == time()) || ($messages[0]->id == time() - 1));
	}


//*/
}
    
    
?>