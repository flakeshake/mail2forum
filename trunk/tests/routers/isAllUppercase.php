<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



class isAllUppercase extends m2fUnitTestCase 
{
	function TestIsAllUppercaseRouterLog()
	{
		$this->isAllUppercase_router->add_option($this->fileWriter, TRUE);
		$this->isAllUppercase_router->add_option($this->smtp, FALSE);
		
		$this->chain->add_element($this->randomTextGenerator, 'in');
		$this->chain->add_element($this->toUppercase_filter);
		$this->chain->add_element($this->isAllUppercase_router);
		$this->chain->run();
		
		$this->assertWantedPattern('/Cleaning up/i', $this->get_log_message());
		$this->assertWantedPattern('/Wrote 1/i', $this->get_log_message());
		$this->assertWantedPattern('/Exporting.*File writer.*channel/i', $this->get_log_message());
		$this->assertWantedPattern('/Export starting/i', $this->get_log_message());
		$this->assertWantedPattern('/Router matched/i', $this->get_log_message());
		$this->assertWantedPattern('/Checking.*uppercase/i', $this->get_log_message());
		$this->assertWantedPattern('/Routing.*1/i', $this->get_log_message());
		$this->assertWantedPattern('/Running router/i', $this->get_log_message());
		$this->assertWantedPattern('/Transforming/i', $this->get_log_message());
		$this->assertWantedPattern('/Filter/i', $this->get_log_message());
		$this->assertWantedPattern('/Generated random text/i', $this->get_log_message());
		$this->assertWantedPattern('/Importing.*Random.*channel/i', $this->get_log_message());
		$this->assertWantedPattern('/Import starting/i', $this->get_log_message());
		$this->assertWantedPattern('/Running chain/i', $this->get_log_message());
	}
	
}