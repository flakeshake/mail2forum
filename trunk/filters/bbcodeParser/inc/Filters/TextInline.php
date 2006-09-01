<?php
 /* vim: set expandtab tabstop=4 shiftwidth=4: */
// +----------------------------------------------------------------------+
// | PHP Version 4                                                        |
// +----------------------------------------------------------------------+
// | Copyright (c) 1997-2003 The PHP Group                                |
// +----------------------------------------------------------------------+
// | This source file is subject to version 2.02 of the PHP license,      |
// | that is bundled with this package in the file LICENSE, and is        |
// | available at through the world-wide-web at                           |
// | http://www.php.net/license/2_02.txt.                                 |
// | If you did not receive a copy of the PHP license and are unable to   |
// | obtain it through the world-wide-web, please send a note to          |
// | license@php.net so we can mail you a copy immediately.               |
// +----------------------------------------------------------------------+
// | Author: Stijn de Reede <sjr@gmx.co.uk>                               |
// +----------------------------------------------------------------------+
//

/**
* @package  HTML_BBCodeParser
* @author   Seth Price  <seth@pricepages.org>
*/

//require_once('filters/bbcode_ascii_html.php');

class HTML_BBCodeParser_Filter_TextInline extends HTML_BBCodeParser
{

    function HTML_BBCodeParser_Filter_TextInline()
    {
    	parent::HTML_BBCodeParser();
			$this->_definedTags['quote'] = array( 'htmlopen' => 'div class="' . M2F_BBCODE_HTML_QUOTE_CLASS . '"',
																						'htmlclose' => 'div',
																						'isValidIn' => array('all'),
																						'attributes'=> array('quote' => ''),
																						'extra_tag' => '<span class="' . M2F_BBCODE_HTML_QUOTE_LABLE_CLASS . '">%s</span>');
		}

    /**
    * An array of tags parsed by the engine
    *
    * @access   private
    * @var      array
    */
    var $_definedTags = array(
            'color' => array( 'htmlopen'  => 'span',
                              'htmlclose' => 'span',
                              'attributes'=> array('color' =>'style=%2$scolor:%1$s%2$s')),

            'size'  => array( 'htmlopen'  => 'span',
                              'htmlclose' => 'span',
                              'attributes'=> array('size' =>'style=%2$sfont-size:%1$spt%2$s')),

            'font'  => array( 'htmlopen'  => 'span',
                              'htmlclose' => 'span',
                              'attributes'=> array('font' =>'style=%2$sfont-family:%1$s%2$s')),

            'code'  => array( 'htmlopen'  => 'code',
                              'htmlclose' => 'code',
                              'attributes'=> array()),

            'abbr'  => array( 'htmlopen'  => 'abbr',
                              'htmlclose' => 'abbr',
                              'attributes'=> array('abbr' =>'title=%2$s%1$s%2$s')));



    /**
     * Render as ASCII 
     * 
     * @param   array    All of the defined tags and their filters.
     * @param   array    The tag for this.
     * @param   array    Children of this tag.
     * @param   integer  The width to render this element at (in columns)
     * @return  array    This rendered element, including text and formatting
     * @access  protected
     */
    function renderAsASCII(&$definedTags, &$tag, &$items, $width, $forceWrap = false){

				if ($tag['tag'] == 'quote')
				{
					if (isset($tag['attributes']['quote']))
					{
						$quote_before = sprintf(M2F_BBCODE_ASCII_AUTHORED_QUOTE_BEFORE, $tag['attributes']['quote']);
					}
					else
					{
						$quote_before = M2F_BBCODE_ASCII_QUOTE_BEFORE;
					}
					
					$width -= 2; 
					$block = parent::renderAsASCII($definedTags, $tag, $items, $width);

					$wrapped = trim(wordwrap($block['lines'][0], $width, "\n", 1));
					$block['lines'][0] = "\n" . $quote_before . '> ' . preg_replace('#\n(?(?=' . $quote_before . ')|\s*)#', "\n> ", $wrapped) . M2F_BBCODE_ASCII_QUOTE_AFTER . "\n";
				}
				else
				{
					$block = parent::renderAsASCII($definedTags, $tag, $items, $width);
				}

        return $block;
    }

}

?>