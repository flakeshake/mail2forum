<?php

/************************************************************
 *
 *	$Date$
 *	$Revision$
 *	$Author$
 *	$HeadURL$
 *
 /***********************************************************/


/* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4														|
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group								|
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,	  |
// | that is bundled with this package in the file LICENSE, and is		|
// | available at through the world-wide-web at						   |
// | http://www.php.net/license/2_02.txt.								 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to		  |
// | license@php.net so we can mail you a copy immediately.			   |
// +----------------------------------------------------------------------+
// | Author: Stijn de Reede <sjr@gmx.co.uk>							   |
// +----------------------------------------------------------------------+
//

/**
 * @package  HTML_BBCodeParser
 * @author   Stijn de Reede  <sjr@gmx.co.uk>
 * @author   Seth Price  <seth@pricepages.org>
 *
 *
 * This is a parser to replace UBB style tags with their html equivalents. It
 * does not simply do some regex calls, but is complete stack based parse
 * engine. This ensures that all tags are properly nested, if not, extra tags
 * are added to maintain the nesting. This parser should only produce xhtml 1.0
 * compliant code. All tags are validated and so are all their attributes. It
 * should be easy to extend this parser with your own tags, see the _definedTags
 * format description below.
 *
 *
 * Usage:
 * $parser = new HTML_BBCodeParser();
 * $parser->setText('normal [b]bold[/b] and normal again');
 * $parser->parse();
 * echo $parser->getParsed();
 * or:
 * $parser = new HTML_BBCodeParser();
 * echo $parser->qparse('normal [b]bold[/b] and normal again');
 * or:
 * echo HTML_BBCodeParser::staticQparse('normal [b]bold[/b] and normal again');
 *
 *
 * Setting the options from the ini file:
 * $config = parse_ini_file('BBCodeParser.ini', true);
 * $options = &PEAR::getStaticProperty('HTML_BBCodeParser', '_options');
 * $options = $config['HTML_BBCodeParser'];
 * unset($options);
 *
 *
 * The _definedTags variables should be in this format:
 * array('tag'								// The actual tag used
 *		   => array('htmlopen'  => 'open',  // The opening tag in html
 *					'htmlclose' => 'close', // The closing tag in html,
 *											   can be set to an empty string
 *											   if no closing tag is present
 *											   in html (like <img>)
 *					'child'	 => array(), // The valid child tags of this
 *											   tag. If a child is needed for a
 *											   parent, it is taken from here.
 *					'parent'	=> array(), // The valid parental tags of this
 *											   tag. If a parent is needed for a
 *											   child, it is taken from here.
 *					'isValidIn' => array(), // All of the tags that contain
 *											   this tag. Defaults to:
 *											   array('all',array(thisTag))
 *											   so a tag can't contain itself.
 *					'attributes' => array() // An associative array containing
 *											   the tag attributes and their
 *											   printf() html equivalents, to
 *											   which the first argument is
 *											   the value, and the second is
 *											   the quote. Default would be
 *											   something like this:
 *											   'attr' => 'attr=%2$s%1$s%2$s'
 *				   ),
 *	   'etc'
 *		   => (...)
 *	   )
 * 
 * 'child', 'parent', and 'isValidIn' are arrays in the format where the first entry
 * is the default ('all' or 'none'). The second entry is another array of all the
 * exception tags to the default.
 */

/**
 * 
 */
require_once 'PEAR.php';

/**
 * 
 */
class HTML_BBCodeParser
{
	/**
	 * An array of tags parsed by the engine, should be overwritten by filters
	 *
	 * @access   private
	 * @var	  array
	 */
	var $_definedTags  = array();

	/**
	 * A string containing the input
	 *
	 * @access   private
	 * @var	  string
	 */
	var $_text		  = '';

	/**
	 * A string containing the preparsed input
	 *
	 * @access   private
	 * @var	  string
	 */
	var $_preparsed	 = '';

	/**
	 * An array tags and texts build from the input text
	 *
	 * @access   private
	 * @var	  array
	 */
	var $_tagArray	  = array();

	/**
	 * A string containing the parsed version of the text
	 *
	 * @access   private
	 * @var	  string
	 */
	var $_parsed		= '';

	/**
	 * An array of options, filled by an ini file or through the contructor
	 *
	 * @access   private
	 * @var	  array
	 */
	var $_options = array(
		'quotestyle'	=> 'single', //double|single
		'quotewhat'	 => 'all', //all|strings|nothing
		'open'		  => '[',
		'close'		 => ']',
		'xmlclose'	  => true,
		'filters'	   => 'Basic',
		'onError'	   => 'ignore', //abort|delete|ignore
		'onWarn'		=> 'correct', //abort|correct|ignore|delete
		'escapeWhat'	=> 'all', //all|attributes|text|nothing
		
		//Options for custom renders
		'wrapAt'		=> '72' //ASCII renderer, value doesn't include the newline
	);

	/**
	 * An array of filters used for parsing
	 *
	 * @access   private
	 * @var	  array
	 */
	var $_filters	   = array();

	/**
	 * Tag detecting regular expressions
	 * 
	 * @access  private
	 * @var	 string
	 */
	var $_keyRegex = '';
	var $_valRegex = '';
	
	/**
	 * Has there been a code problem?
	 * 
	 * @access  private
	 * @var	 boolean
	 */
	var $_error		= array();
	var $_warn		 = array();
	var $_mayBeInvalid = false;

	/**
	 * Constructor, initialises the options and filters
	 *
	 * Sets the private variable _options with base options defined with
	 * &PEAR::getStaticProperty(), overwriting them with (if present)
	 * the argument to this method.
	 * Then it sets the extra options to properly escape the tag
	 * characters in preg_replace() etc. The set options are
	 * then stored back with &PEAR::getStaticProperty(), so that the filter
	 * classes can use them.
	 * All the filters in the options are initialised and their defined tags
	 * are copied into the private variable _definedTags.
	 *
	 * @param	array		   options to use, can be left out
	 * @return   none
	 * @access   public
	 * @author   Stijn de Reede  <sjr@gmx.co.uk>
	 */
	function HTML_BBCodeParser($options = array())
	{
		// set the already set options
		$baseoptions = &PEAR::getStaticProperty('HTML_BBCodeParser', '_options');
		if (is_array($baseoptions)) {
			foreach ($baseoptions as  $k => $v)  {
				$this->_options[$k] = $v;
			}
		}

		// set the options passed as an argument
		foreach ($options as $k => $v )  {
		   $this->_options[$k] = $v;
		}

		// add escape open and close chars to the options for preg escaping
		$preg_escape = '\^$.[]|()?*+{}';
		if (strstr($preg_escape, $this->_options['open'])) {
			$this->_options['open_esc'] = $oe = "\\".$this->_options['open'];
		} else {
			$this->_options['open_esc'] = $oe = $this->_options['open'];
		}
		if (strstr($preg_escape, $this->_options['close'])) {
			$this->_options['close_esc'] = $ce = "\\".$this->_options['close'];
		} else {
			$this->_options['close_esc'] = $ce = $this->_options['close'];
		}
		
		/* now that we have those chars set, let's set the preg esc strings */
		$this->_keyRegex = '([A-Za-z0-9]+)';
		$this->_valRegex = '=(?>\"([^\"'.$ce.']+)\"|\'([^\''.$ce.']+)\'|([^\s'.$ce.']+))';	 // <?

		// set the options back so that child classes can use them
		$baseoptions = $this->_options;
		unset($baseoptions);

		// return if this is a subclass
		if (is_subclass_of($this, 'HTML_BBCodeParser')) {
			return;
		}

		// extract the definedTags from subclasses
		$this->addFilters($this->_options['filters']);
	}

	/**
	 * Option setter
	 *
	 * @param string option name
	 * @param mixed  option value
	 * @author Lorenzo Alberton <l.alberton@quipo.it>
	 */
	function setOption($name, $value)
	{
		$this->_options[$name] = $value;
	}

	/**
	 * Add a new filter
	 *
	 * @param string filter
	 * @author Lorenzo Alberton <l.alberton@quipo.it>
	 */
	function addFilter($filter)
	{
		$filter = ucfirst($filter);
		if (!array_key_exists($filter, $this->_filters)) {
			$class = 'HTML_BBCodeParser_Filter_'.$filter;
			include_once M2F_BBCODE_INC_PATH . 'Filters/' . $filter . '.php';
			if (!class_exists($class)) {
				return PEAR::raiseError("Failed to load filter $filter", null, PEAR_ERROR_DIE);
			}
			$this->_filters[$filter] =& new $class;
			foreach ($this->_filters[$filter]->_definedTags as $k => $v){
				$this->_definedTags[$k] = $v;
				$this->_definedTags[$k]['filter'] =& $this->_filters[$filter];
			}
		}
	}

	/**
	 * Remove an existing filter
	 *
	 * @param string $filter
	 * @author Lorenzo Alberton <l.alberton@quipo.it>
	 */
	function removeFilter($filter)
	{
		$filter = ucfirst(trim($filter));
		if (!empty($filter) && array_key_exists($filter, $this->_filters)) {
			unset($this->_filters[$filter]);
		}
		// also remove the related $this->_definedTags for this filter,
		// preserving the others
		$this->_definedTags = array();
		foreach (array_keys($this->_filters) as $filter) {
			$this->_definedTags = array_merge(
				$this->_definedTags,
				$this->_filters[$filter]->_definedTags
			);
		}
	}

	/**
	 * Add new filters
	 *
	 * @param mixed (array or string)
	 * @author Lorenzo Alberton <l.alberton@quipo.it>
	 */
	function addFilters($filters)
	{
		if (is_string($filters)) {
			//comma-separated list
			if (strpos($filters, ',') !== false) {
				$filters = explode(',', $filters);
			} else {
				$filters = array($filters);
			}
		}
		if (!is_array($filters)) {
			//invalid format
			return;
		}
		foreach ($filters as $filter) {
			if (trim($filter)){
				$this->addFilter($filter);
			}
		}
	}

	/**
	 * Executes statements before the actual array building starts
	 *
	 * This method should be overwritten in a filter if you want to do
	 * something before the parsing process starts. This can be useful to
	 * allow certain short alternative tags which then can be converted into
	 * proper tags with preg_replace() calls.
	 * The main class walks through all the filters and and calls this
	 * method. The filters should modify their private $_preparsed
	 * variable, with input from $_text.
	 *
	 * @return   none
	 * @access   private
	 * @see	  $_text
	 * @author   Stijn de Reede  <sjr@gmx.co.uk>
	 */
	function _preparse()
	{
		// default: assign _text to _preparsed, to be overwritten by filters
		$this->_preparsed = $this->_text;

		// return if this is a subclass
		if (is_subclass_of($this, 'HTML_BBCodeParser')) {
			return;
		}

		// walk through the filters and execute _preparse
		foreach ($this->_filters as $filter) {
			$filter->setText($this->_preparsed);
			$this->_preparsed = $filter->getPreparsed();
		}
	}

	/**
	 * Builds the tag array from the input string $_text
	 * 
	 * An array consisting of tag and text elements is contructed from the
	 * $_preparsed variable. The method uses _build*Tag() to check if a tag is
	 * valid and to build the actual tag to be added to the tag array.
	 * 
	 * @return  none
	 * @access  private
	 * @see	 _buildOpenTag()
	 * @see	 _buildCloseTag()
	 * @see	 _buildTextTag()
	 * @see	 $_text
	 * @see	 $_tagArray
	 * @author  Seth Price  <seth@pricepages.org>
	 */
	function _buildTagArray()
	{
		$oe = $this->_options['open_esc'];
		$ce = $this->_options['close_esc'];
		
		$regex =
			'!(?>'.$oe.'/'.$this->_keyRegex.$ce.')|' .
			'(?>'.$oe.$this->_keyRegex.'(?>'.$this->_valRegex.')?([^'.$oe.$ce.']+)?'.$ce.')|' .
			'(?>'.$oe.'?[^'.$oe.']*)!';	  // <?
		

		$matches = array();
		preg_match_all($regex, $this->_preparsed, $matches, PREG_SET_ORDER|PREG_OFFSET_CAPTURE);
		
		$lines = explode("\n", $this->_preparsed);
		$lineNum = 1;
		$charNum = 1;
		$lineOffBeg = 0;
		$lineOffEnd = 1 + strlen(array_shift($lines));

		$this->_tagArray = array();
		foreach ($matches as $m){
			$tag = false;
			
			//Calc the position of this token
			while ($m[0][1] >= $lineOffEnd && $lines){
				//Advance token if needed
				$lineOffBeg = $lineOffEnd;
				$lineOffEnd += 1 + strlen(array_shift($lines));
				++$lineNum;
			}
			
			$charNum = $m[0][1] - $lineOffBeg + 1;
			
			//Match an opening tag with attributes
			if (isset($m[6])){
				$tag = $this->_buildOpenTag($m[0][0], $m[2][0], $lineNum, $charNum, $m[0][0]);
			}
			//Match a opening tag with one attribute
			elseif (isset($m[3])){
				/*
				 * Note that only one of $m[3].$m[4].$m[5] should contain
				 * data, so we should be safe concatinating.
				 */
				$attr = @array($m[2][0]=>$m[3][0].$m[4][0].$m[5][0]);
				$tag = $this->_buildOpenTag($m[0][0], $m[2][0], $lineNum, $charNum, $attr);
		   }
			//Match a opening tag
			elseif (isset($m[2])){
				$tag = $this->_buildOpenTag($m[0][0], $m[2][0], $lineNum, $charNum);
			}
			//Match a closing tag
			elseif (isset($m[1])){
				$tag = $this->_buildCloseTag($m[0][0], $m[1][0], $lineNum, $charNum);
			}
			
			//If we have a valid tag, then keep it, otherwise, make a text tag.
			if ($tag){
				$this->_tagArray[] = $tag;
			} else {
				//If the previous tag is also a text tag, then combine them.
				$prevTag = end($this->_tagArray);
				if ($prevTag['type'] === 0){
					array_pop($this->_tagArray);
					$this->_tagArray[] = $this->_buildTextTag($prevTag['text'].$m[0][0], $lineNum, $charNum);
				} else {
					$this->_tagArray[] = $this->_buildTextTag($m[0][0], $lineNum, $charNum);
				}
			}
		}
	}
	
	/**
	 * Builds a textual tag
	 * 
	 * @param	string  The text
	 * @param	integer Tag line number.
	 * @param	integer Tag char number.
	 * @return   array   The tag in array format
	 * @access   private
	 * @see	  _buildTagArray()
	 * @author   Seth Price  <seth@pricepages.org>
	 */
	function _buildTextTag($txt, $lineNum, $charNum)
	{
		return array('type'=>0,'text'=>$txt,'line'=>$lineNum,'char'=>$charNum);
	}
	
	/**
	 * Builds a "closing" tag.
	 * 
	 * If no valid tag can be created, false is returned.
	 * 
	 * @param	string  The original tag
	 * @param	string  The tag name as defined in a filter
	 * @param	integer Tag line number.
	 * @param	integer Tag char number.
	 * @return   array   The tag in array format
	 * @access   private
	 * @see	  _buildTagArray()
	 * @author   Seth Price  <seth@pricepages.org>
	 */
	function _buildCloseTag($txt, $t, $lineNum, $charNum)
	{
		$t = strtolower($t);
		
		//Check that it is a valid tag
		if(!$t || !isset($this->_definedTags[$t])){
			return false;
		}

		return array('text'=>$txt,'type'=>2,'tag'=>$t,'line'=>$lineNum,'char'=>$charNum);
	}
	
	/**
	 * Builds a tag with attributes
	 * 
	 * Typically, this is a tag that may contain attributes. This could be an
	 * opening tag, or a stand-alone tag. The parameters should be the original
	 * string of this tag and the tag name (as would match the appropriate
	 * filter). The tag name is forced to lowercase within this function. The
	 * last parameter is the set of attributes that are associated with this
	 * tag. If you are feeling lazy, just pass a string and the attributes will
	 * be parsed out of it.
	 * 
	 * If no valid tag can be created, false is returned.
	 * 
	 * @param	string  The original tag
	 * @param	string  The tag name as defined in a filter
	 * @param	integer Tag line number.
	 * @param	integer Tag char number.
	 * @param	mixed   Any attributes associated with this tag
	 * @return   array   The tag in array format
	 * @access   private
	 * @see	  _buildTagArray()
	 * @author   Seth Price  <seth@pricepages.org>
	 */
	function _buildOpenTag($txt, $t, $lineNum, $charNum, $attr = false)
	{
		$t = strtolower($t);
		
		//Check that it is a valid tag
		if(!$t || !isset($this->_definedTags[$t])){
			return false;
		}
		
		//Init a tag
		$tag = array('text'=>$txt,'type'=>1,'tag'=>$t,'line'=>$lineNum,'char'=>$charNum,'attributes'=>array());

		//Were we passed an array of attributes?
		if(is_array($attr)){
			foreach($attr as $k => $v){
				//Only add attributes that are defined
				if(isset($this->_definedTags[$t]['attributes'][$k])){
					$tag['attributes'][$k] = $v;
				}
			}
		}
		
		//Were we just passed a string that we need to parse?
		elseif($attr !== false){
			$matches = array();
			preg_match_all('!'.$this->_keyRegex.$this->_valRegex.'!', $attr, $matches, PREG_SET_ORDER);
			foreach($matches as $m){
				//Only add attributes that are defined
				if(isset($this->_definedTags[$t]['attributes'][$m[1]])){
					//Only one of them should be actually valid
					@$tag['attributes'][$m[1]] = $m[2].$m[3].$m[4];
				}
			}
		}
		
		return $tag;
	}

	/**
	 * Validates the tag array, regarding the allowed tags
	 *
	 * While looping through the tag array, two following text tags are joined,
	 * and it is checked that the tag is allowed inside the last opened tag. By
	 * remembering what tags have been opened it is checked that there is
	 * correct (xml compliant) nesting. In the end all still opened tags are
	 * closed.
	 *
	 * @return   none
	 * @access   private
	 * @see	  $_tagArray
	 * @author   Seth Price  <seth@pricepages.org>
	 */
	function _validateTagArray()
	{
		$newTagArray = array();
		$openTags = array();
		$ret = true;

		$o = $this->_options['open'];
		$c = $this->_options['close'];
		
		$num = count($this->_tagArray);
		for ($i = 0; $i < $num; $i++) {
			//Do whatever it is we do to tags
			switch ($this->_tagArray[$i]['type']) {
			case 0:
				$ret = $this->_validateText($i, $newTagArray, $openTags);
				break;
			case 1:
				$ret = $this->_validateOpen($i, $newTagArray, $openTags);
				break;
			case 2:
				$ret = $this->_validateClose($i, $newTagArray, $openTags);
				break;
			}
			
			//Catch the abort
			if(!$ret){
				return false;
			}
		}

		//Close any tags that are still open.
		if(($numOpen = count($openTags)) != 0){
			$this->_warn = array_merge($this->_warn, $openTags);
			switch($this->_options['onWarn']){
			case 'abort':
				return false;
			case 'correct':
				while ($oTag = array_pop($openTags)) {
					$newTagArray[] = $this->_buildCloseTag($o.'/'.$oTag['tag'].$c, $oTag['tag'], 0, 0);
				}
				break;
			case 'ignore':
				for ($i = 0; $i < $numOpen; $i++){
					$this->_ignoreTag($openTags[$i]);
				}
				break;
			case 'delete':
				for ($i = 0; $i < $numOpen; $i++){
					$this->_delTag($openTags[$i]);
				}
				break;
			}
		}

		//Save the new version of the tag array
		$this->_tagArray = $newTagArray;
		return true;
	}
	
	/**
	 * Validate a text tag.
	 * 
	 * Text. If the previous tag was also text, then just leave it. Otherwise we
	 * need to make sure that the nesting is ok.
	 * 
	 * @param	integer	Current index in _tagArray
	 * @param	array	  Destination array
	 * @param	array	  Stack of open tags
	 * @return   boolean	Was tag handled OK?
	 * @access   private
	 * @see	  _validateTagArray
	 * @author   Seth Price  <seth@pricepages.org>
	 */
	function _validateText($tagNum, &$newTags, &$openTags)
	{
		$tag = $this->_tagArray[$tagNum];

	   //If it has no items, skip it (tho, maybe the regex should be fixed...)
		if(!$tag){
			return true;
		}
		
		//$tag['text'] = preg_replace("#^(.+)\n$#s", '\1', $tag['text']);
		
		
		
		end($newTags);
		$lastNewTagNum = key($newTags);

		//Was there a previous tag or was it text?
		if (!$newTags || $newTags[$lastNewTagNum]['type'] === 0) {
			//No harm in having text following text. Skip valididy tests.
			$newTags[] = $tag;
			return true;
		}
		
		
		/*
		 * Is a child needed between the previous tag and this tag?
		 * (ex: "<ul>txt" -> "<ul><li>txt") If so, handle it.
		 */
		$oTag = end($openTags);
		if( trim($tag['text']) &&
		   ($child = $this->_childNeeded($oTag['tag'], 'text')) !== false){

			//Child needed but none given, error.
			if($child === true){
				$this->_error[] = $tag;
				$handleVia = $this->_options['onError'];
			}
			//Child needed and given, warning.
			else {
				$this->_warn[] = $tag;
				$handleVia = $this->_options['onWarn'];
			}
			
			//Handle the problem
			switch($handleVia){
			case 'abort':
				return false;
			case 'correct':
				$newTags[] = $child;
				$openTags[] =& $newTags[++$lastNewTagNum];
				break;
			case 'ignore':
				/*
				 * This is tricky. If a child is needed, but we are requested to
				 * ignore the problem, we can't ignore text (it's already
				 * "ignored"), so we are forced to ignore the parent. I hope
				 * that I can get away with this, because it possibly throws the
				 * parent into a invalid state.
				 */
				$this->_ignoreTag($newTags[$lastNewTagNum]);
				$this->_mayBeInvalid = true;
				break;
			case 'delete':
				/*
				 * This is also tricky. If a child is needed, but we are
				 * requested to delete the problem, should we delete the text
				 * or the parent? I'm going to delete the parent so we don't
				 * lose information.
				 */
				$this->_delTag($newTags[$lastNewTagNum]);
				break;
			}
		}
		
		$newTags[] = $tag;
		return true;
	}
	
	/**
	 * Validate an open tag
	 * 
	 * Opening tags. Here is where most of the magic happens. If the child tag
	 * needs a parent inserted, or the parent tag needs a parent inserted, then
	 * do the inserting.
	 * 
	 * Note that a parent or child value of true means that one is needed, but
	 * no suggestion is made. It is the equivlent of an error, and should be
	 * delt with by ignoring the tag in some way.
	 * 
	 * @param	integer	Current index in _tagArray
	 * @param	array	  Destination array
	 * @param	array	  Stack of open tags
	 * @return   boolean	Was tag handled OK?
	 * @access   private
	 * @see	  _validateTagArray
	 * @author   Seth Price  <seth@pricepages.org>
	 */
	function _validateOpen($tagNum, &$newTags, &$openTags)
	{
		$tag = $this->_tagArray[$tagNum];
		$handleVia = false;
		$popOpen = false;

		$oTag = end($openTags);

		//Check to see if we need a child for the parent.
		$child = $this->_childNeeded($oTag['tag'], $tag['tag']);
		if($child === true){
			$this->_error[] = $tag;
			$handleVia = $this->_options['onError'];
		}
		//Use child if we have one.
		elseif($child) {
			$this->_warn[] = $tag;
			$handleVia = $this->_options['onWarn'];
			$newTag = $child;
		} else {
			//Do we need a parent for the child?
			if($parent = $this->_parentNeeded($oTag['tag'], $tag['tag'])){
				/*
				 * If there is an error/warning of some type, first we can check
				 * if the previous open tag is equal to the current invalid one.
				 * If it is, we can close the previous one and re-open it. In
				 * that case it should be valid again. Nice how that works.
				 * 
				 * Note that this line of reasoning only matters if we have been
				 * directed to correct tags.
				 */
				if ($tag['tag'] == $oTag['tag'] && $this->_options['onWarn'] == 'correct'){
					$this->_warn[] = $tag;
					$handleVia = $this->_options['onWarn'];
					
					$o = $this->_options['open'];
					$c = $this->_options['close'];
					$newTag = $this->_buildCloseTag($o.'/'.$tag['tag'].$c, $tag['tag'], 0, 0);
					$popOpen = true;
				}
				/*
				 * Handle error if a parent is needed, but no suggestions are
				 * made.
				 */
				elseif ($parent === true){
					/*
					 * Let's see what would happen if we closed the parent tag.
					 * If that has no errors, than lets do it that way.
					 * Otherwise, we'll just error out.
					 */
					$gpTag = prev($openTags);
					if ($this->_parentNeeded($gpTag['tag'], $tag['tag'])){
						$this->_error[] = $tag;
						$handleVia = $this->_options['onError'];
					} else {
						$this->_warn[] = $tag;
						$handleVia = $this->_options['onWarn'];
						
						$o = $this->_options['open'];
						$c = $this->_options['close'];
						$newTag = $this->_buildCloseTag($o.'/'.$oTag['tag'].$c, $oTag['tag'], 0, 0);
						$popOpen = true;
					}
				}
				//Use parent since we have one.
				else {
					$this->_warn[] = $tag;
					$handleVia = $this->_options['onWarn'];
					$newTag = $parent;
				}
			}
		}
		
		//If there are no errors or warnings so far, lets check nesting valididy
		if(!$handleVia && !$this->_isValidSubTag($openTags, $tag)){
		
			$this->_error[] = $tag;
			$handleVia = $this->_options['onError'];
			/*
			 * Note that we need to ignore the child tag on error, which is
			 * currently the case. Be careful with that $child in the ignore
			 * though.
			 */
		}

		if($handleVia){
			//Handle the problem
			switch($handleVia){
			case 'abort':
				return false;
			case 'correct':
				$newTags[] = $newTag;
				if($popOpen){
					array_pop($openTags);
				} else {
					/*
					 * This can get tricky because a new parent may put it's
					 * parent in an invalid state.
					 */
					end($newTags);
					$openTags[] =& $newTags[key($newTags)];
					$this->_mayBeInvalid = true;
				}
				break;
			case 'ignore':
				if($child){
					/*
					 * This is risky. We're requested to ignore the parent, but
					 * that may put the parent in an invalid state. Hold on
					 * tight... we're in for a bumpy ride!!!
					 */
					end($newTags);
					$this->_ignoreTag($newTags[key($newTags)]);
					$this->_mayBeInvalid = true;
				} else {
					//Child needs a parent, ignore child
					$this->_ignoreTag($tag);
				}
				$newTags[] = $tag;
				//Don't append to open stack because it is ignored
				return true;
			case 'delete':
				//Do nothing with the tag.
				return true;
			}
		}

		//All clear, append open tag or close this one
		$newTags[] = $tag;
		end($newTags);
		$openTags[] =& $newTags[key($newTags)];

		return true;
	}
	
	/**
	 * Validate an close tag.
	 * 
	 * Closing tags. If the tag is listed as open, process it. Otherwise, ignore
	 * and delete the tag. We ignore correcting for tag nesting here because it
	 * is assumed that we took care of that little problem when opening the
	 * tags.
	 * 
	 * @param	integer	Current index in _tagArray
	 * @param	array	  Destination array
	 * @param	array	  Stack of open tags
	 * @return   boolean	Was tag handled OK?
	 * @access   private
	 * @see	  _validateTagArray
	 * @author   Seth Price  <seth@pricepages.org>
	 */
	function _validateClose($tagNum, &$newTags, &$openTags){
		
		$tag = $this->_tagArray[$tagNum];
		$handleVia = false;
		$probType = false;
		$match = false;
		
		//Are there open tags?
		if ($openTags){
			//Does this tag match any of the open tags?
			end($openTags);
			$openI = key($openTags);
			for ($i = 0; $i <= $openI; $i++){
				if($tag['tag'] === $openTags[$i]['tag']){
					$match = true;
					break;
				}
			}
		}

		//Does this tag match the current open tag?
		if ($match) {
			$oTag =& $openTags[$openI];
			
			if($oTag['tag'] !== $tag['tag']){
				$this->_warn[] = $tag;
				$handleVia = $this->_options['onWarn'];
				$probType = 'warn';
			}
		} else{
			$this->_error[] = $tag;
			$handleVia = $this->_options['onError'];
			$probType = 'error';
		}

		if($handleVia){
			//Handle the problem
			switch($handleVia){
			case 'abort':
				return false;
			case 'correct':
				/*
				 * What to do if we are trying to close a tag, but said tag
				 * isn't on the top of the open tags stack? (But it is
				 * _somewhere_ in the stack)
				 * 
				 * The code that used to be here would close tags until the
				 * proper one was closed, then open the closed tags back up. So
				 * the BBCode "[b]txt[i]txt[/b]txt[/i]" would render as
				 * "<b>txt<i>txt</i></b><i>txt</i>".
				 * 
				 * This is nice and all, but it leads to things like list
				 * items being rendered outside of lists. "[list][li]txt
				 * [/list]" -> "<ul><li>txt</li></ul><li></li>". Blech.
				 * 
				 * The current code just close the damn tags and doesn't open
				 * them. If users want text formatted _their_ way, then they
				 * should learn to write correct code and not expect us to fix
				 * it for them.
				 */
				$o = $this->_options['open'];
				$c = $this->_options['close'];
				do {
					$newTags[] = $this->_buildCloseTag($o.'/'.$oTag['tag'].$c, $oTag['tag'], 0, 0);
					array_pop($openTags);
					$oTag =& $openTags[--$openI];
				} while ($oTag && $oTag['tag'] !== $tag['tag']);
				break;
			
			//These end up being handled in a similar manner
			case 'ignore':
			case 'delete':
				if($handleVia == 'ignore'){
					$fxn = '_ignoreTag';
				} else {
					$fxn = '_delTag';
				}
				/*
				 * Error means no opening tags exist, so problem is with the
				 * closing tag. Warning means that an opening tag exists, but it
				 * is not at the top of the open stack.
				 */
				if($probType == 'error'){
					//Ignored, so exit early and don't pop the open stack
					$this->$fxn($tag);
					$newTags[] = $tag;
					return true;
				} else {
					/*
					 * Ignore opening tags until we hit the opening tag that
					 * matches
					 */
					do {
						$this->$fxn($oTag);
						array_pop($openTags);
						$oTag =& $openTags[--$openI];
					} while ($oTag && $oTag['tag'] !== $tag['tag']);
				}
				break;
			}
		}
		
		$newTags[] = $tag;
		array_pop($openTags);
		return true;
	}

	/**
	 * Checks to see if a parent is needed
	 *
	 * Checks to see if the current $in tag has an appropriate parent. If it
	 * does, then it returns false. If a parent is needed, then it returns the
	 * first tag in the list to add to the stack.
	 *
	 * @param	array		   tag that is on the outside
	 * @param	array		   tag that is on the inside
	 * @return   boolean		 false if not needed, tag if needed, true if out
	 *						   of  our minds
	 * @access   private
	 * @see	  _validateTagArray()
	 * @author   Seth Price <seth@pricepages.org>
	 */
	function _parentNeeded($out, $in)
	{
		$ar =& $this->_definedTags[$in]['parent'];
		if (!$ar || ($ar[0] == 'all' && empty($ar[1]))) {
			return false;
		}

		if ($ar[0] == 'none'){
			if ($out && in_array($out, $ar[1])) {
				return false;
			}
			//Create a tag from the first one on the list
			$o = $this->_options['open'];
			$c = $this->_options['close'];
			return $this->_buildOpenTag($o.$ar[1][0].$c, $ar[1][0], 0, 0);
		}
		if ($ar[0] == 'all' && (!$out || ($out && !in_array($out, $ar[1])))) {
			return false;
		}
		/*
		 * Tag is needed, we don't know which one. We could make something up,
		 * but it would be so random, I think that it would be worthless.
		 */
		return true;
	}

	/**
	 * Checks to see if a child is needed
	 * 
	 * Checks to see if the current $out tag has an appropriate child. If it
	 * does, then it returns false. If a child is needed, then it returns the
	 * first tag in the list to add to the stack.
	 *
	 * @param	array		   tag that is on the outside
	 * @param	array		   tag that is on the inside
	 * @return   boolean		 false if not needed, tag if needed, true if out
	 *						   of our minds
	 * @access   private
	 * @see	  _validateTagArray()
	 * @author   Seth Price <seth@pricepages.org>
	 */
	function _childNeeded($out, $in)
	{
		$ar =& $this->_definedTags[$out]['child'];
		if (!$ar || ($ar[0] == 'all' && !$ar[1])) {
			return false;
		}

		if ($ar[0] == 'none'){
			if (empty($ar[1]) || ($in && in_array($in, $ar[1]))) {
				return false;
			}
			//Create a tag from the first one on the list
			$o = $this->_options['open'];
			$c = $this->_options['close'];
			return $this->_buildOpenTag($o.$ar[1][0].$c, $ar[1][0], 0, 0);
		}
		if ($ar[0] == 'all' && $in && !in_array($in, $ar[1])) {
			return false;
		}
		/*
		 * Tag is needed, we don't know which one. We could make something up,
		 * but it would be so random, I think that it would be worthless.
		 */
		return true;
	}
	
	/**
	 * Checks if this tag is valid in the scope of all parent tags.
	 * 
	 * @return   boolean	  Is tag valid?
	 * @access   private
	 * @see	  _validateTagArray
	 * @author   Seth Price  <seth@pricepages.org>
	 */
	function _isValidSubTag(&$openTags, $tag){
	   /*
		 * Default to all except self are valid. This prevents things like
		 * "<b><b></b></b>" unless it is defined as ok in the tag defn.
		 */
		$ar =& $this->_definedTags[$tag['tag']]['isValidIn'];
		if (!$ar) {
			foreach($openTags as $oTag){
				if($oTag['tag'] === $tag['tag']){
					return false;
				}
			}
			return true;
		}

		//Check for nesting in nothing
		if(!isset($openTags[0])){
			return true;
		}
		//Allow nesting in nothing by default
		if($ar[0] == 'none'){
			$def = false;
		}
		//Allow nesting in everything by default
		else {
			$def = true;
		}
		//Are we the exception to the rule?
		if(!isset($ar[1])){
			return $def;
		}
		
		foreach($openTags as $oTag){
			if(in_array($oTag['tag'], $ar[1])){
				return !$def;
			}
		}
		
		return $def;
	}
	
	/**
	 * Sets a tag to be ignored.
	 * 
	 * @return   none
	 * @access   private
	 * @see	  _validateTagArray
	 * @author   Seth Price  <seth@pricepages.org>
	 */
	function _ignoreTag(&$tag){
		$tag['type'] = 0;
		unset($tag['attributes']);
		unset($tag['tag']);
	}
	
	/**
	 * Sets a tag to be deleted.
	 * 
	 * @return   none
	 * @access   private
	 * @see	  _validateTagArray
	 * @author   Seth Price  <seth@pricepages.org>
	 */
	function _delTag(&$tag){
		$tag['type'] = 3;
		unset($tag['text']);
		unset($tag['attributes']);
		unset($tag['tag']);
	}

	/**
	 * Checks all tags.
	 *
	 * Note that this is different from _validateTagArray() because this only
	 * checks each tag, and not combinations of tags.
	 * 
	 * @return   none
	 * @access   private
	 * @see	  $_tagArray
	 * @see	  _checkTag
	 * @author   Seth Price  <seth@pricepages.org>
	 */
	function _checkTags()
	{
		/*
		 * walk through the filters and find which ones are responsible for each
		 * tag and implement the _checkTag() method
		 */
		$tagValdators = array();
		foreach ($this->_filters as $filter) {
			if(method_exists($filter, '_checkTag')){
				foreach ($filter->_definedTags as $tK => $tV){
					$tagValidators[$tK][] = $filter;
				}
			}
		}
		
		/*
		 * Walk through all tags and call _checkTag() where appropriate
		 */
		$numT = count($this->_tagArray);
		for ($i = 0; $i < $numT; $i++){
			if(isset($this->_tagArray[$i]['tag']) && isset($tagValidators[$this->_tagArray[$i]['tag']])){
				foreach ($tagValidators[$this->_tagArray[$i]['tag']] as $filter){
					$filter->_checkTag($this->_tagArray, $i);
				}
				$numT = count($this->_tagArray);
			}
		}
	}

	/**
	 * Builds a parsed string based on the tag array The correct html and
	 * atribute values are extracted from the private _definedTags array.
	 *
	 * @return   none
	 * @access   private
	 * @see	  $_tagArray
	 * @see	  $_parsed
	 * @author   Stijn de Reede  <sjr@gmx.co.uk>
	 * @author   Seth Price  <seth@pricepages.org>
	 */
	function _buildXHTMLString()
	{
		$this->_parsed = '';
		
		//Precompute some things that will be useful in the loop
		$qs = ENT_COMPAT;
		if ($this->_options['quotestyle'] == 'single'){
			$q = "'";
			$qs = ENT_QUOTES;
		}
		if ($this->_options['quotestyle'] == 'double'){
			$q = '"';
		}
		
		$quoteNothing = $this->_options['quotewhat'] == 'nothing';
		$quoteStrings = $this->_options['quotewhat'] == 'strings';

		$escapeAttr = ($this->_options['escapeWhat'] == 'attributes') ||
					  ($this->_options['escapeWhat'] == 'all');
		$escapeText = ($this->_options['escapeWhat'] == 'text') ||
					  ($this->_options['escapeWhat'] == 'all');
		
		//Replace all tags
		foreach ($this->_tagArray as $tag) {
			switch ($tag['type']) {

			// just text
			case 0:
				//Handle escaping HTML
				if ($escapeText) {
					$this->_parsed .= htmlspecialchars($tag['text']);
				} else {
					$this->_parsed .= nl2br($tag['text']);
				}
				break;

			// opening tag
			case 1:
				// Extra tag before the main one? (by George)
				if (isset($this->_definedTags[$tag['tag']]['extra_tag']))
				{
					if ($tag['tag'] == 'quote')
					{
						if (isset($tag['attributes']['quote']))
						{
							$this->_parsed .= sprintf(sprintf($this->_definedTags[$tag['tag']]['extra_tag'], M2F_BBCODE_AUTHORED_QUOTE), $tag['attributes']['quote']);
						}
						else
						{
							$this->_parsed .= sprintf($this->_definedTags[$tag['tag']]['extra_tag'], M2F_BBCODE_QUOTE);
						}
					}
				}

				//Create the HTML tag
				$this->_parsed .= '<'.sprintf($this->_definedTags[$tag['tag']]['htmlopen'], $q);
				foreach ($tag['attributes'] as $a => $v) {
					if (empty($this->_definedTags[$tag['tag']]['attributes'][$a])) continue;
					if ($escapeAttr) {
						$v = htmlspecialchars($v, $qs);
					}
					if ($quoteNothing || ($quoteStrings && is_numeric($v))) {
						$this->_parsed .= ' '.sprintf($this->_definedTags[$tag['tag']]['attributes'][$a], $v, '');
					} else {
						$this->_parsed .= ' '.sprintf($this->_definedTags[$tag['tag']]['attributes'][$a], $v, $q);
					}
				}
				if ($this->_definedTags[$tag['tag']]['htmlclose'] == '' && $this->_options['xmlclose']) {
					$this->_parsed .= ' /';
				}
				$this->_parsed .= '>';
								
				break;

			// closing tag
			case 2:
				//Create closing HTML tag
				if ($this->_definedTags[$tag['tag']]['htmlclose'] != '') {
					$this->_parsed .= '</'.$this->_definedTags[$tag['tag']]['htmlclose'].'>';
				}
				break;
			
			//delete (by simply not printing the tag)
			case 3:
			default:
				break;
			}
		}
	}

	/**
	 * Renders the text as its ASCII equivalent
	 * 
	 * Builds a rendered string based on the tag array. This is a dumb renderer,
	 * but it tries to be smart.
	 *
	 * @return   none
	 * @access   private
	 * @see	  $_tagArray
	 * @see	  $_parsed
	 * @author   Seth Price  <seth@pricepages.org>
	 */
	function _buildASCIIString()
	{
		$xmlStack = array(0=>array());
		$stackIndex = 0;
		$numTags = count($this->_tagArray);

		/*
		 * Build the XML tree from tags
		 * 
		 * Idea being that we have an array of levels of our XML tree. As we add
		 * tags to the current level, the tag is simply added to an array. If we
		 * go up a level, then we begin adding to a new array at that level.
		 * This new array is stored at the next level in the stack.
		 * 
		 * When we go back down a level in the tree by a closing XML tag, we
		 * take the previous level, and add it to the array at this level. When
		 * we have traversed the tree, the entire tree should be should be
		 * stored in index 0 of the stack.
		 * 
		 * It should be noted that the first element in each array (except the
		 * root) is a tag array.
		 */
		for ($i = 0; $i < $numTags; $i++) {
			switch ($this->_tagArray[$i]['type']) {

			// just text
			case 0:
				$xmlStack[$stackIndex][] =& $this->_tagArray[$i];
				break;

			// opening tag
			case 1:
				$xmlStack[++$stackIndex] = array(&$this->_tagArray[$i]);
				break;

			// closing tag
			case 2:
				$xmlStack[$stackIndex - 1][] = $xmlStack[$stackIndex];
				--$stackIndex;
				break;
			
			//delete (by simply not adding the tag)
			case 3:
			default:
				break;
			}
		}

		/*
		 * Take the tree that we have created and feed it to the appropriate
		 * filter objects. The objects should be able to use the tree to render
		 * the XML as text from the outside in.
		 */
		$blankTag = array();
		$rendered = $this->renderAsASCII($this->_definedTags, $blankTag, $xmlStack[0], $this->_options['wrapAt'], true);
		$this->_parsed = trim(implode("\n", $rendered['lines']), "\n");
	}

	/**
	 * Sets text in the object to be parsed
	 *
	 * @param	string		  the text to set in the object
	 * @param	string		  Input text format
	 * @return   none
	 * @access   public
	 * @see	  getText()
	 * @see	  $_text
	 * @author   Seth Price  <seth@pricepages.org>
	 */
	function setText($str)
	{
		//Init internal vars
		$this->_text = $str;
		$this->_preparsed = '';
		$this->_tagArray = array();
		$this->_parsed = '';
		$this->_error = array();
		$this->_warn = array();
	}

	/**
	 * Gets the unparsed text from the object
	 *
	 * @return   string		  the text set in the object
	 * @access   public
	 * @see	  setText()
	 * @see	  $_text
	 * @author   Stijn de Reede  <sjr@gmx.co.uk>
	 */
	function getText()
	{
		return $this->_text;
	}

	/**
	 * Gets the preparsed text from the object
	 *
	 * @return   string		  the text set in the object
	 * @access   public
	 * @see	  _preparse()
	 * @see	  $_preparsed
	 * @author   Stijn de Reede  <sjr@gmx.co.uk>
	 */
	function getPreparsed()
	{
		if($this->_preparsed === ''){
				$this->_preparse();
		}
		return $this->_preparsed;
	}

	/**
	 * Gets the parsed text from the object
	 *
	 * @return   string		  the parsed text set in the object
	 * @access   public
	 * @see	  parse()
	 * @see	  $_parsed
	 * @author   Stijn de Reede  <sjr@gmx.co.uk>
	 */
	function getParsed()
	{
		return $this->_parsed;
	}

	/**
	 * Parses the text set in the object
	 *
	 * @return   boolean	Is string valid?
	 * @access   public
	 * @see	  _preparse()
	 * @see	  _buildTagArray()
	 * @see	  _validateTagArray()
	 * @see	  _buildParsedString()
	 * @author   Seth Price  <seth@pricepages.org>
	 */
	function parse($fmt = 'bbcode', $renderer = '')
	{
		//Skip step if it is already preparsed
		if($this->_preparsed === ''){
			$this->_preparse();
		}
		
		$this->_buildTagArray();
		$this->_validateTagArray();
		
		//Check for validation errors that cause an abort
		if (($this->_error && $this->_options['onError'] == 'abort') ||
			($this->_warn  && $this->_options['onWarn']  == 'abort')){
			
			$this->_tagArray = array();
			$this->_parsed = '';
			return false;
		}
		
		$this->_checkTags();
		
		$renderer = strtoupper($renderer);
		if ($renderer == 'ASCII'){
			$this->_buildASCIIString();
		} else {
			$this->_buildXHTMLString();
		}
		return true;
	}

	/**
	 * Quick method to do setText(), parse() and getParsed at once
	 *
	 * @return   none
	 * @access   public
	 * @see	  parse()
	 * @see	  $_text
	 * @author   Seth Price <seth@pricepages.org>
	 */
	function qparse($str, $fmt = 'bbcode', $renderer = '')
	{
		$this->setText($str);
		if($this->parse($fmt, $renderer)){
			return $this->getParsed();
		} else {
			return '';
		}
	}

	/**
	 * Quick static method to do setText(), parse() and getParsed at once
	 *
	 * @return   none
	 * @access   public
	 * @see	  parse()
	 * @see	  $_text
	 * @author   Stijn de Reede  <sjr@gmx.co.uk>
	 */
	function staticQparse($str)
	{
		$p = new HTML_BBCodeParser();
		$str = $p->qparse($str);
		unset($p);
		return $str;
	}
	
	/**
	 * Return all invalid tags
	 * 
	 * Returns an array of all of the errors and warnings. The errors are filed
	 * under the key ['error'] and warnings under ['warn']. In each of those
	 * arrays are all of the tags as parsed by BBCodeParser. The line and column
	 * numbers are going to be of most interest to you, which are under
	 * ['line'] and ['char'], respectively. You can use these to inform the user
	 * of their problems.
	 * 
	 * @return   array	Invalid tags
	 * @access   public
	 * @see	  _error
	 * @see	  _warn
	 * @author   Seth Price  <seth@pricepages.org>
	 */
	function getInvalid(){
		return array('error'=>$this->_error, 'warn'=>$this->warn);
	}
	
	/**
	 * Recursively called by the renderer when printing ASCII
	 * 
	 * Defaults to not making any changes.
	 * 
	 * @param   array	All of the defined tags and their filters.
	 * @param   array	The tag for this.
	 * @param   array	Children of this tag.
	 * @param   integer  The width to render this element at (in columns)
	 * @return  array	This rendered element, including text and formatting
	 * @access  protected
	 * @author  Seth Price  <seth@pricepages.org>
	 */
	function renderAsASCII(&$definedTags, &$tag, &$children, $width, $forceWrap = false) {


		$lines = array();
		$text = '';
		$displayPrev = 'start'; //start|block|inline
		$thisBlock = 'inline'; //inline|block
		
		//Render each list child as text
		foreach ($children as $child){
		
			//Join this one with previous
			if (isset($child['text'])){
				/*
				 * Ignore empty tokens except after an inline element. We need
				 * to allow a space between [b]two[/b] [s]words[/s]
				 */
				if (!trim($child['text']) && $displayPrev != 'inline'){
					continue;
				}

				//Delete newlines, we are rendering only tags here
				//$text .= str_replace(array("\n","\r", '  '), ' ', $child['text']);
			   	$text .= $child['text'];
			   	
				$displayPrev = 'inline';
			} else {
				$childTag = array_shift($child);
				$block = $definedTags[$childTag['tag']]['filter']->renderAsASCII(
								$definedTags,
								$childTag,
								$child,
								$width);

				//Attempt to render a basic block model
				if ($block['display'] == 'block'){

					/*
					 * If last block was inline, then remove any extra white
					 * space and close off that text.
					 */
					if ($displayPrev == 'inline') {
						$lines[] = trim($text);
						$text = '';
					
						/*
						 * If last block was inline and this block has a margin,
						 * insert the margin now. Otherwise, the last block was
						 * block display, so there is already a margin.
						 */
						if (!empty($block['margin'])){
							$lines[] = '';
						}
					}

					$thisBlock = 'block';
					$displayPrev = 'block';
					$lines = array_merge($lines, $block['lines']);

					/*
					 * If requested, add a margin on the bottom.
					 */
					if (!empty($block['margin'])){
						$lines[] = '';
					}
				
				//Must be inline
				} else {
					$displayPrev = 'inline';
					if (isset($block['lines'][0])){
						 $text .= $block['lines'][0];
					}
				}
			}
		}
		
		if($text){
				$lines[] = trim($text);
			$text = '';
		}
		
		//If this is inline, wrapping is someone else's problem
		if ($thisBlock == 'inline' && !$forceWrap){
			return array('display'=>'inline', 'lines'=>$lines);
		}
		
		$newLines = array();


		/*
		 * Wrap text on spaces first, then on anything. Also, trim
		 * everything to avoid formatting oddness.
		 */
		 
		foreach ($lines as $line)
		{
			$newLines[] = wordwrap($line, $width, "\n", 1);
		} 
		 
		 
		 
		 
		 
/*
		foreach ($lines as $line){
			//Split it
			while(isset($line[$width])){
				$pos = strrpos(substr($line, 0, $width + 1), ' ');
				if ($pos){
					$newLines[] = substr($line, 0, $pos);
					$line = substr($line, $pos + 1);
				} else {
					$newLines[] = substr($line, 0, $width);
					$line = substr($line, $width);
				}
			}
			
			//Clean up remainder
			$newLines[] = $line;
		}
*/		
		
		//If wrapping is applied, then we must be block
		if (isset($newLines[1])){
			$thisBlock = 'block';
		}
		
		return array('display' => $thisBlock, 'lines' => $newLines);
	}
	

}
?>