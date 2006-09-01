<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/



    class m2f_HtmlReporter extends SimpleReporter {
        var $_character_set;
        
        /**
         *    Does nothing yet. The first output will
         *    be sent on the first test start. For use
         *    by a web browser.
         *    @access public
         */
        function HtmlReporter($character_set = 'ISO-8859-1') {
            $this->SimpleReporter();
            $this->_character_set = $character_set;
        }
        
        /**
         *    Paints the top of the web page setting the
         *    title to the name of the starting test.
         *    @param string $test_name      Name class of test.
         *    @access public
         */
				function paintHeader($test_name) 
				{
					global $test_files;
					
					$this->sendNoCacheHeaders();
					print "<html>\n<head>\n<title>$test_name</title>\n";
					print "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=" .
									$this->_character_set . "\">\n";
					print '<link href="display/style.css" rel="stylesheet" type="text/css" />';
					print '	<script type="text/javascript" language="javascript">

									var checked = false;
									function check_uncheck() {
										var inputs = document.getElementsByTagName("input");
										checked = !checked;
										for (index = 0; index < inputs.length; index++) {
											if (inputs[index].name == \'tests[]\') {
												inputs[index].checked = checked;
											}
										}
									}        

									function toggleDisplay()
									{
										tmp = document.getElementsByTagName("span");
										for (i=0; i<tmp.length; i++)
										{
											if (tmp[i].className == "error") tmp[i].style.display = (tmp[i].style.display == "none") ? "block" : "none";
										}
									}

									</script>';
					print "\n</head>\n<body>\n";
					
					print '<form id="m2f_test_files" action="run.php" method="GET">';
					foreach ($test_files as $test_file)
					{
						echo '<p><input type="checkbox" name="tests[]" value="' . $test_file . '"';
						if (isset($_GET['tests']) && in_array($test_file, $_GET['tests'])) echo ' checked';
						echo ' /> ' . $test_file . '</p>';
					}
					print '<p>&nbsp;</p>
						<p><input type="submit" name="selected" value="Run Selected" />&nbsp;&nbsp;&nbsp;<a href="javascript:void(null);" onClick="check_uncheck()">Toggle all</a><br /><br /></p>
						<p><input type="submit" name="all" value="Run All" />&nbsp;&nbsp;&nbsp;<a href="javascript:toggleDisplay()">show/hide errors</a></p>
						</form>';
					
					print "\n\n<h1>$test_name</h1>\n";
					flush();
				}
        
        /**
         *    Send the headers necessary to ensure the page is
         *    reloaded on every request. Otherwise you could be
         *    scratching your head over out of date test data.
         *    @access public
         *    @static
         */
        function sendNoCacheHeaders() {
            if (! headers_sent()) {
                header("Expires: Mon, 26 Jul 1997 05:00:00 GMT");
                header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
                header("Cache-Control: no-store, no-cache, must-revalidate");
                header("Cache-Control: post-check=0, pre-check=0", false);
                header("Pragma: no-cache");
            }
        }
        
        /**
         *    Paints the CSS. Add additional styles here.
         *    @return string            CSS code as text.
         *    @access protected
         */
        function _getCss() {
            return "";
        }
        
        /**
         *    Paints the end of the test with a summary of
         *    the passes and failures.
         *    @param string $test_name        Name class of test.
         *    @access public
         */
        function paintFooter($test_name) {
            $colour = ($this->getFailCount() + $this->getExceptionCount() > 0 ? "red" : "green");
            print "<div id=\"results\" style=\"background-color: $colour;\">";
            print $this->getTestCaseProgress() . "/" . $this->getTestCaseCount();
            print " test cases complete:\n";
            print "<b>" . $this->getPassCount() . "</b> passes, ";
            print "<b>" . $this->getFailCount() . "</b> fails and ";
            print "<b>" . $this->getExceptionCount() . "</b> exceptions.";
            print "</div>\n";
            print "</body>\n</html>\n";
        }
        
        /**
         *    Paints the test failure with a breadcrumbs
         *    trail of the nesting test suites below the
         *    top level test.
         *    @param string $message    Failure message displayed in
         *                              the context of the other tests.
         *    @access public
         */
				function paintFail($message) {
            parent::paintFail($message);
						$breadcrumb = $this->getTestList();
						array_shift($breadcrumb);
						
						print "<div><span class=\"red\">Fail:</span> <strong>";
						print implode(" -&gt; ", $breadcrumb);
						print " </strong><br />\n<span class=\"fail\">" . $this->_htmlEntities($message) . "</span></div>\n";
				}
        
        /**
         *    Paints a PHP error or exception.
         *    @param string $message        Message is ignored.
         *    @access public
         *    @abstract
         */
				function paintError($message) {
            parent::paintError($message);
						print "<div><span class=\"red\">Exception:</span> <strong>";
						$breadcrumb = $this->getTestList();
						array_shift($breadcrumb);
						print implode(" -&gt; ", $breadcrumb);
						print "</strong><br />\n<span class=\"exception\">" . $this->_htmlEntities($message) . "</span></div>\n";
				}
        
        /**
         *    Paints formatted text such as dumped variables.
         *    @param string $message        Text to show.
         *    @access public
         */
        function paintFormattedMessage($message) {
            print '<span class="pre">' . $this->_htmlEntities($message) . '</span>';
        }
        
        /**
         *    Character set adjusted entity conversion.
         *    @param string $message    Plain text or Unicode message.
         *    @return string            Browser readable message.
         *    @access protected
         */
        function _htmlEntities($message) {
            return nl2br(htmlentities($message, ENT_COMPAT, $this->_character_set));
        }
    }

?>