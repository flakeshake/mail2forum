<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



class TestFilters extends m2fUnitTestCase 
{
//*

	function TestFactoryCreatesFilterObjects()
	{
		$this->assertIsA($this->toUppercase_filter, 'm2f_filters_toUppercase');
	}

	function TestBadTargetRemoved()
	{
		$filter = new m2f_filter;
		$this->assertTrue($filter->filter(array('body', 'crap', 'bullshit', 'subject', 'html_body')));
		$this->assertEqual($filter->_targets, array('body', 'subject', 'html_body'));
	}


	function TestDefaultFilterConvertsPlainOnly()
	{
		$this->generic_message->html_body = 'some [b]bold[/b] text';
		$this->generic_message->body = 'some [b]bold[/b] text';
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter());
		$this->assertEqual($this->bbcodeParser_filter->message->body, 'some bold text');
		$this->assertEqual($this->bbcodeParser_filter->message->html_body, 'some [b]bold[/b] text');
	}
	
//*/
}
    
    
?>