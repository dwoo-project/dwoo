<?php
/**
 * Copyright (c) 2013-2016
 *
 * @category  Library
 * @package   Dwoo\Plugins\Blocks
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2016 David Sanchez
 * @license   http://dwoo.org/LICENSE Modified BSD License
 * @version   1.3.0
 * @date      2016-09-19
 * @link      http://dwoo.org/
 */

namespace Dwoo\Plugins\Blocks;

use Dwoo\Block\Plugin as BlockPlugin;

/**
 * Formats a string to the given format, you can wrap lines at a certain
 * length and indent them
 * <pre>
 *  * wrap : maximum line length
 *  * wrap_char : the character(s) to use to break the line
 *  * wrap_cut : if true, the words that are longer than $wrap are cut instead of overflowing
 *  * indent : amount of $indent_char to insert before every line
 *  * indent_char : character(s) to insert before every line
 *  * indent_first : amount of additional $indent_char to insert before the first line of each paragraphs
 *  * style : some predefined formatting styles that set up every required variables, can be "email" or "html"
 *  * assign : if set, the formatted text is assigned to that variable instead of being output
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginTextformat extends BlockPlugin
{
    protected $wrap;
    protected $wrapChar;
    protected $wrapCut;
    protected $indent;
    protected $indChar;
    protected $indFirst;
    protected $assign;

    /**
     * @param int    $wrap
     * @param string $wrap_char
     * @param bool   $wrap_cut
     * @param int    $indent
     * @param string $indent_char
     * @param int    $indent_first
     * @param string $style
     * @param string $assign
     */
    public function init($wrap = 80, $wrap_char = "\r\n", $wrap_cut = false, $indent = 0, $indent_char = ' ', $indent_first = 0, $style = '', $assign = '')
    {
        if ($indent_char === 'tab') {
            $indent_char = "\t";
        }

        switch ($style) {

            case 'email':
                $wrap         = 72;
                $indent_first = 0;
                break;
            case 'html':
                $wrap_char   = '<br />';
                $indent_char = $indent_char == "\t" ? '&nbsp;&nbsp;&nbsp;&nbsp;' : '&nbsp;';
                break;
        }

        $this->wrap     = (int)$wrap;
        $this->wrapChar = (string)$wrap_char;
        $this->wrapCut  = (bool)$wrap_cut;
        $this->indent   = (int)$indent;
        $this->indChar  = (string)$indent_char;
        $this->indFirst = (int)$indent_first + $this->indent;
        $this->assign   = (string)$assign;
    }

    /**
     * @return string
     */
    public function process()
    {
        // gets paragraphs
        $pgs = explode("\n", str_replace(array(
            "\r\n",
            "\r"
        ), "\n", $this->buffer));

        while (list($i) = each($pgs)) {
            if (empty($pgs[$i])) {
                continue;
            }

            // removes line breaks and extensive white space
            $pgs[$i] = preg_replace(array(
                '#\s+#',
                '#^\s*(.+?)\s*$#m'
            ), array(
                ' ',
                '$1'
            ), str_replace("\n", '', $pgs[$i]));

            // wordwraps + indents lines
            $pgs[$i] = str_repeat($this->indChar, $this->indFirst) . wordwrap($pgs[$i], max($this->wrap - $this->indent, 1), $this->wrapChar . str_repeat($this->indChar, $this->indent), $this->wrapCut);
        }

        if ($this->assign !== '') {
            $this->core->assignInScope(implode($this->wrapChar . $this->wrapChar, $pgs), $this->assign);
        } else {
            return implode($this->wrapChar . $this->wrapChar, $pgs);
        }
    }
}
