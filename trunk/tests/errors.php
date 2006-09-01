<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



class TestErrors extends m2fUnitTestCase 
{
//*
	// m2f core
	function TestClassFactoryError()
	{
		$error = m2f_factory::make_object('non_existant_class');
		$this->assertTrue(m2f::is_error($error));
		$this->assertWantedPattern('/load.*non_existant_class/i', $this->get_log_message());
	}


	// File Writer Channel
	function TestFileWriterBadPath()
	{
		$this->fileWriter->filepath = '/';
		
		$this->fileWriter->add_message($this->generic_message);
		$error = $this->fileWriter->export();
		$this->assertTrue(m2f::is_error($error));
		$this->assertWantedPattern('#/#i', $this->get_log_message());
	}

	function TestFileWriterBadPermissions()
	{
		touch($this->fileWriter_path);
		chmod($this->fileWriter_path, 0555);

		$this->fileWriter->add_message($this->generic_message);
		$error = $this->fileWriter->export();
		$this->assertTrue(m2f::is_error($error));
		$this->assertWantedPattern('/' . preg_quote($this->fileWriter_path, '/') . '/i', $this->get_log_message());
		chmod($this->fileWriter_path, 0777);
	}


	// Mbox Channel
	
	// POP channel
	function TestPOPConnectionError()
	{
		$pear_pop =& $this->get_mocked_PEAR_POP();
		$pear_pop->setReturnValue('connect', FALSE);
		$pop =& $this->get_mocked_POP($pear_pop);
		
		$error = $pop->import();
		$this->assertWantedPattern('/server.*port/i', $this->get_log_message(2));
	}

	function TestPOPLoginError()
	{
		$pear_pop =& $this->get_mocked_PEAR_POP();
		$pear_pop->setReturnValue('connect', TRUE);
		$pear_pop->setReturnValue('login', m2f::raise_error('Login error', __LINE__, __FILE__));
		
		$pop =& $this->get_mocked_POP($pear_pop);

		$error = $pop->import();
		$this->assertWantedPattern('/username.*abc/i', $this->get_log_message(2));
	}
	
	function TestPOPGetNumMessagesError()
	{
		$pear_pop =& $this->get_mocked_PEAR_POP();
		$pear_pop->setReturnValue('connect', TRUE);
		$pear_pop->setReturnValue('login', TRUE);
		$pear_pop->setReturnValue('numMsg', FALSE);
		
		$pop =& $this->get_mocked_POP($pear_pop);
		
		$error = $pop->import();
		$this->assertWantedPattern('/number.*messages/i', $this->get_log_message(2));
	}
//*		

	function TestPOPGetMessageError()
	{
		$pear_pop =& $this->get_mocked_PEAR_POP();
		$pear_pop->setReturnValue('connect', TRUE);
		$pear_pop->setReturnValue('login', TRUE);
		$pear_pop->setReturnValue('numMsg', 3);
		$pear_pop->setReturnValue('getMsg', FALSE);
		
		$pop =& $this->get_mocked_POP($pear_pop);
		
		$error = $pop->import();
		$this->assertWantedPattern('/message.*1/i', $this->get_log_message(2));
	}

	function TestPOPDeleteMessageError()
	{
		$pear_pop =& $this->get_mocked_PEAR_POP();
		$pear_pop->setReturnValue('connect', TRUE);
		$pear_pop->setReturnValue('login', TRUE);
		$pear_pop->setReturnValue('numMsg', 3);
		$pear_pop->setReturnValue('getMsg', TRUE);
		$pear_pop->setReturnValue('deleteMsg', FALSE);

		$pop =& $this->get_mocked_POP($pear_pop);
		
		$error = $pop->import();
		$this->assertTrue(m2f::is_error($error));
		$this->assertWantedPattern('/delete.*message.*1/i', $this->get_log_message());
	}

	function TestPOPDisconnectError()
	{
		$pear_pop =& $this->get_mocked_PEAR_POP();
		$pear_pop->setReturnValue('connect', TRUE);
		$pear_pop->setReturnValue('login', TRUE);
		$pear_pop->setReturnValue('numMsg', 3);
		$pear_pop->setReturnValue('getMsg', TRUE);
		$pear_pop->setReturnValue('deleteMsg', TRUE);
		$pear_pop->setReturnValue('disconnect', FALSE);
		
		$pop =& $this->get_mocked_POP($pear_pop);
		
		$error = $pop->import();
		$this->assertTrue(m2f::is_error($error));
		$this->assertWantedPattern('/disconnect/i', $this->get_log_message());
	}


	// Mail channel
	function TestMailDecodeEmptyMessageError()
	{
		$email =& new m2f_channels_email;
		$message = '';
		$error = $email->_transform($message);
		$this->assertTrue(m2f::is_error($error));
		$this->assertWantedPattern('/blank.*message/i',$this->get_log_message());
	}
	
	
	// SMTP channel
	function TestSMTPHostError()
	{
		$pear_smtp =& $this->get_mocked_PEAR_SMTP();
		$pear_smtp->setReturnValue('send', m2f::raise_error('Cannot send mail error host localhostttt', __LINE__, __FILE__));
		$smtp =& $this->get_mocked_SMTP($pear_smtp);

		$smtp->host = 'localhostttt';

		$smtp->add_message($this->generic_message);
		$error = $smtp->export();
		$this->assertTrue(m2f::is_error($error));
		$this->assertWantedPattern('/Cannot send mail.*localhostttt/i', $this->get_log_message());
	}

	function TestSMTPPortError()
	{
		$pear_smtp =& $this->get_mocked_PEAR_SMTP();
		$pear_smtp->setReturnValue('send', m2f::raise_error('Cannot send mail error port 0', __LINE__, __FILE__));
		$smtp =& $this->get_mocked_SMTP($pear_smtp);

		$smtp->port = 0;

		$smtp->add_message($this->generic_message);
		$error = $smtp->export();
		$this->assertTrue(m2f::is_error($error));
		$this->assertWantedPattern('/Cannot send mail.*0/i', $this->get_log_message());
	}

	// Chains, Filters, Routers
	function TestEmptyChainError()
	{
		$error = $this->chain->run();
		$this->assertTrue(m2f::is_error($error));
		$this->assertWantedPattern('/chain.*no elements/i', $this->get_log_message());
	}

	function TestRouterWithNoOptionsWritesErrorButDoesntHaltImport()
	{
		$this->assertTrue($this->chain->add_element($this->randomTextGenerator, 'in'));
		$this->assertTrue($this->chain->add_element($this->isAllUppercase_router));
		$this->assertTrue($this->chain->add_element($this->fileWriter, 'out'));
		
		$error =  $this->chain->run();
		$this->assertFalse(m2f::is_error($error));
		
		$this->assertIdentical(file_get_contents($this->fileWriter_path), $this->chain->_messages[0]->body);

		$this->assertWantedPattern('/router.*no options/i', $this->get_log_message(6));
	}
//*/
	
	

}
    
    
?>