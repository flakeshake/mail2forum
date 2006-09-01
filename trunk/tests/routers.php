<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



class TestRouters extends m2fUnitTestCase 
{
//*
	function TestFactoryCreatesRouterObjects()
	{
		$this->assertIsA($this->hasAngleBracket_router, 'm2f_routers_hasAngleBracket');
		$this->assertIsA($this->isAllUppercase_router, 'm2f_routers_isAllUppercase');
		$this->assertIsA($this->countOCharacters_router, 'm2f_routers_countOCharacters');
	}
	
	function TestSimpleRouterDoesntRoute()
	{
		$this->assertTrue($this->hasAngleBracket_router->add_option($this->fileWriter, TRUE));
		$this->assertTrue($this->hasAngleBracket_router->add_option($this->fileWriter2, FALSE));
		
		$this->assertTrue($this->chain->add_element($this->randomTextGenerator, 'in'));
		$this->assertTrue($this->chain->add_element($this->hasAngleBracket_router));
		$this->assertTrue($this->chain->run());

		$this->assertIdentical(file_get_contents($this->fileWriter2_path), $this->randomTextGenerator->_messages[0]->body);
		$this->assertFalse(is_file($this->fileWriter_path));
	}
	
	function TestUppercaseRouterDoesntRoute()
	{
		$this->assertTrue($this->isAllUppercase_router->add_option($this->fileWriter, TRUE));
		$this->assertTrue($this->isAllUppercase_router->add_option($this->fileWriter2, FALSE));
		
		$this->assertTrue($this->chain->add_element($this->randomTextGenerator, 'in'));
		$this->assertTrue($this->chain->add_element($this->isAllUppercase_router));
		$this->assertTrue($this->chain->run());
		
		$this->assertIdentical(file_get_contents($this->fileWriter2_path), $this->randomTextGenerator->_messages[0]->body);
		$this->assertFalse(is_file($this->fileWriter_path));
	}

	function TestUppercaseRouterDoesRoute()
	{
		$this->assertTrue($this->isAllUppercase_router->add_option($this->fileWriter, TRUE));
		$this->assertTrue($this->isAllUppercase_router->add_option($this->fileWriter2, FALSE));
		
		$this->assertTrue($this->chain->add_element($this->randomTextGenerator, 'in'));
		$this->assertTrue($this->chain->add_element($this->toUppercase_filter));
		$this->assertTrue($this->chain->add_element($this->isAllUppercase_router));
		$this->assertTrue($this->chain->run());
		
		$this->assertIdentical(file_get_contents($this->fileWriter_path), $this->randomTextGenerator->_messages[0]->body);
		$this->assertFalse(is_file($this->fileWriter2_path));
	}

	function TestMultipleOptionRouter()
	{
		$this->assertTrue($this->countOCharacters_router->add_option($this->fileWriter, 0));
		$this->assertTrue($this->countOCharacters_router->add_option($this->fileWriter2, 1));
		$this->assertTrue($this->countOCharacters_router->add_option($this->fileWriter3, 2));

		$this->chain->add_message($this->generic_message);
		$this->assertTrue($this->chain->add_element($this->countOCharacters_router));
		$this->assertTrue($this->chain->run());
		
		$this->assertIdentical(file_get_contents($this->fileWriter2_path), $this->generic_message->body);
		$this->assertFalse(is_file($this->fileWriter_path));
		$this->assertFalse(is_file($this->fileWriter3_path));
	}
	
	function TestComplexRouteThenNoFilter()
	{
		$this->assertTrue($this->chain2->add_element($this->toUppercase_filter));
		$this->assertTrue($this->chain2->add_element($this->fileWriter2, 'out'));

		$this->assertTrue($this->chain3->add_element($this->fileWriter, 'out'));
		
		$this->assertTrue($this->hasAngleBracket_router->add_option($this->chain3, TRUE));
		$this->assertTrue($this->hasAngleBracket_router->add_option($this->chain2, FALSE));
		
		$this->chain->add_message($this->generic_message);
		$this->generic_message->body = 'hello. here is a message with a > in it';
		$this->assertTrue($this->chain->add_element($this->hasAngleBracket_router));
		$this->assertTrue($this->chain->run());
		
		$this->assertIdentical(file_get_contents($this->fileWriter_path), $this->generic_message->body);
		
		$this->assertFalse(is_file($this->fileWriter2_path));
	}

	function TestComplexRouteThenFilter()
	{
		$this->assertTrue($this->chain2->add_element($this->toUppercase_filter));
		$this->assertTrue($this->chain2->add_element($this->fileWriter2, 'out'));

		$this->assertTrue($this->chain3->add_element($this->fileWriter, 'out'));
		
		$this->assertTrue($this->hasAngleBracket_router->add_option($this->chain3, TRUE));
		$this->assertTrue($this->hasAngleBracket_router->add_option($this->chain2, FALSE));

		$this->chain->add_message($this->generic_message);
		$this->generic_message->body = 'hello. here is a message with no angle brackets in it';
		$this->assertTrue($this->chain->add_element($this->hasAngleBracket_router));
		$this->assertTrue($this->chain->run());
		
		$this->assertIdentical(file_get_contents($this->fileWriter2_path), strtoupper($this->generic_message->body));
	}

	function TestAddingRouterToARouter()
	{
		$this->assertTrue($this->countOCharacters_router->add_option($this->fileWriter, 0));
		$this->assertTrue($this->countOCharacters_router->add_option($this->fileWriter2, 1));
		
		$this->assertTrue($this->hasAngleBracket_router->add_option($this->fileWriter3, TRUE));
		$this->assertTrue($this->hasAngleBracket_router->add_option($this->countOCharacters_router, FALSE));
		
		$this->chain->add_message($this->generic_message);
		$this->assertTrue($this->chain->add_element($this->hasAngleBracket_router));
		$this->assertTrue($this->chain->run());

		$this->assertIdentical(file_get_contents($this->fileWriter2_path), $this->generic_message->body);
		$this->assertFalse(is_file($this->fileWriter_path));
	}
	
	function TestCannotAddImportOnlyChannelToRouter()
	{
		$error = $this->countOCharacters_router->add_option($this->randomTextGenerator, 0);
		$this->assertTrue(m2f::is_error($error));
	}
	
//*/
}
    
    
?>