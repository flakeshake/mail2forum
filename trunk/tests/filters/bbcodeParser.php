<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



define('M2F_BBCODE_ASCII_QUOTE_BEFORE', "  Quote:\n");
define('M2F_BBCODE_ASCII_AUTHORED_QUOTE_BEFORE', "  %s wrote:\n");
define('M2F_BBCODE_ASCII_QUOTE_AFTER', '');

define('M2F_BBCODE_HTML_QUOTE_CLASS', 'quote');
define('M2F_BBCODE_HTML_QUOTE_LABLE_CLASS', 'quotelable');

class bbcode_parser extends m2fUnitTestCase 
{

//*

	// HTML
	function TestSimpleHTMLFilter()
	{
		$this->generic_message->html_body = 'some [b]bold[/b] text';
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter('html_body'));
		$this->assertIdentical($this->bbcodeParser_filter->message->html_body, 'some <strong>bold</strong> text');
	}
	
	function TestEntityNotConverted()
	{
		$this->generic_message->html_body = '&amp;';
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter('html_body'));
		$this->assertIdentical($this->bbcodeParser_filter->message->html_body, '&amp;');
	}

	function TestDefaultListStyle()
	{
		$this->generic_message->html_body = '[list][*]list1[*]list2[/list]';
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter('html_body'));
		$this->assertWantedPattern('#disc#', $this->bbcodeParser_filter->message->html_body);
	}


	function TestLineBreaksConverted()
	{
		$this->generic_message->html_body = "[b]hello[/b]\n[b]goodbye[/b]";
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter('html_body'));
		$this->assertEqual("<strong>hello</strong><br />\n<strong>goodbye</strong>", $this->bbcodeParser_filter->message->html_body);
	}

	function TestLineBreaksConvertedInNestedTags()
	{
		$this->generic_message->html_body = "[code][b]hello[/b]\n[b]goodbye[/b][/code]";
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter('html_body'));
		$this->assertEqual("<code><strong>hello</strong><br />\n<strong>goodbye</strong></code>", $this->bbcodeParser_filter->message->html_body);
	}

	function TestMultipleNewLines()
	{
		$this->generic_message->body = "1 empty line:\n\n[b]bold[/b]\n2 empty lines:\n\n\n[b]bold[/b]";
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter());
		$this->assertEqual("1 empty line:\n\nbold\n2 empty lines:\n\n\nbold", $this->bbcodeParser_filter->message->body);
	}
	
	
	// Plain Text
	function TestSimpleFilter()
	{
		$this->generic_message->body = 'some [b]bold[/b] text';
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter());
		$this->assertIdentical($this->bbcodeParser_filter->message->body, 'some bold text');
	}

	function TestPlainEntityNotConverted()
	{
		$this->generic_message->body = '&amp;';
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter());
		$this->assertIdentical($this->bbcodeParser_filter->message->body, '&amp;');
	}
	
	function TestSimpleLink()
	{
		$this->generic_message->body = '[url]http://www.google.com[/url]';
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter());
		$this->assertIdentical($this->bbcodeParser_filter->message->body, 'http://www.google.com/');
	}

	function TestLabelledLink()
	{
		$this->generic_message->body = '[url=http://www.google.com]Google[/url]';
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter());
		$this->assertIdentical($this->bbcodeParser_filter->message->body, 'Google (http://www.google.com/)');
	}

	function TestLabelSameAsLink()
	{
		$this->generic_message->body = '[url=http://www.google.com]http://www.google.com[/url]';
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter());
		$this->assertIdentical($this->bbcodeParser_filter->message->body, 'http://www.google.com/');
	}

	function TestBlankLabelledLink()
	{
		$this->generic_message->body = '[url=http://www.google.com][/url]';
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter());
		$this->assertIdentical($this->bbcodeParser_filter->message->body, 'http://www.google.com/');
	}

	function TestUntaggedLink()
	{
		$this->generic_message->body = 'http://www.google.com';
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter());
		$this->assertIdentical($this->bbcodeParser_filter->message->body, 'http://www.google.com/');
	}
	
	//Wrapping
	function TestNoWrapping()
	{
		$this->generic_message->body = '123456789012345678901234567890123456789012345678901234567890123456789012';
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter());
		$this->assertIdentical($this->bbcodeParser_filter->message->body, '123456789012345678901234567890123456789012345678901234567890123456789012');
	}

	function TestWrapping()
	{
		$this->generic_message->body = '1234567890123456789012345678901234567890123456789012345678901234567890123';
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter());
		$this->assertIdentical($this->bbcodeParser_filter->message->body, "123456789012345678901234567890123456789012345678901234567890123456789012\n3");
	}

	function TestWrappingOnSpace()
	{
		$this->generic_message->body = '123456789012345678901234567890123456789012345678901234567890 1234567890123';
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter());
		$this->assertIdentical($this->bbcodeParser_filter->message->body, "123456789012345678901234567890123456789012345678901234567890\n1234567890123");
	}
	
	
	// Quotes
	function TestPlainSimpleQuoteConversion()
	{
		$this->generic_message->body = 'before[quote]here is a quote[/quote]after';
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter());
		$this->assertEqual('before
  Quote:
> here is a quote
after', $this->bbcodeParser_filter->message->body);
	}


	function TestPlainMultipleLineQuote()
	{
		$this->generic_message->body = "before[quote]here\nis a \nquote[/quote]after";
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter());
		$this->assertEqual('before
  Quote:
> here
> is a 
> quote
after', $this->bbcodeParser_filter->message->body);
	}

	function TestPlainWrappedQuote()
	{
		$this->generic_message->body = "before[quote]12345678901234567890123456789012345678901234567890123456789012345678901234567890xxx[/quote]after";
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter());
		$this->assertEqual('before
  Quote:
> 1234567890123456789012345678901234567890123456789012345678901234567890
> 1234567890xxx
after', $this->bbcodeParser_filter->message->body);
	}
	
	function TestNestedQuote()
	{
		$this->generic_message->body = 'before[quote]here is a quote [quote]internal quote[/quote] that is now finished[/quote]after';
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter());
		$this->assertEqual('before
  Quote:
> here is a quote 
>   Quote:
> > internal quote
> that is now finished
after', $this->bbcodeParser_filter->message->body);
	}
	
	function TestPlainAuthoredQuote()
	{
		$this->generic_message->body = 'before[quote="George"]here is a quote[/quote]after';
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter());
		$this->assertEqual('before
  George wrote:
> here is a quote
after', $this->bbcodeParser_filter->message->body);
	}

	function TestHTMLSimpleQuote()
	{
		$this->generic_message->html_body = 'before[quote]here is a quote[/quote]after';
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter('html_body'));
		$this->assertEqual('before<span class="quotelable">Quote:</span><div class="quote">here is a quote</div>after', $this->bbcodeParser_filter->message->html_body);
	}

	function TestHTMLAuthoredQuote()
	{
		$this->generic_message->html_body = 'before[quote="George"]here is a quote[/quote]after';
		$this->bbcodeParser_filter->message = $this->generic_message;
		$this->assertTrue($this->bbcodeParser_filter->filter('html_body'));
		$this->assertEqual('before<span class="quotelable">George wrote:</span><div class="quote">here is a quote</div>after', $this->bbcodeParser_filter->message->html_body);
	}
	
	



//*/

}