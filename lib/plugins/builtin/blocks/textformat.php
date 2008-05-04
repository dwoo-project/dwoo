<?php

/**
 * TOCOM
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * This file is released under the LGPL
 * "GNU Lesser General Public License"
 * More information can be found here:
 * {@link http://www.gnu.org/copyleft/lesser.html}
 *
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @copyright  Copyright (c) 2008, Jordi Boggiano
 * @license    http://www.gnu.org/copyleft/lesser.html  GNU Lesser General Public License
 * @link       http://dwoo.org/
 * @version    0.3.4
 * @date       2008-04-09
 * @package    Dwoo
 */
class Dwoo_Plugin_textformat extends Dwoo_Block_Plugin
{
	protected $wrap;
	protected $wrapChar;
	protected $wrapCut;
	protected $indent;
	protected $indChar;
	protected $indFirst;
	protected $assign;

	public function init($wrap=80, $wrap_char="\r\n", $wrap_cut=false, $indent=0, $indent_char=" ", $indent_first=0, $style="", $assign="")
	{
		if($indent_char === 'tab')
			$indent_char = "\t";

		switch($style)
		{
			case 'email':
				$wrap = 72;
				$indent_first = 0;
				break;
			case 'html':
				$wrap_char = '<br />';
				$indent_char = $indent_char == "\t" ? '&nbsp;&nbsp;&nbsp;&nbsp;':'&nbsp;';
				break;
		}

		$this->wrap = (int) $wrap;
		$this->wrapChar = (string) $wrap_char;
		$this->wrapCut = (bool) $wrap_cut;
		$this->indent = (int) $indent;
		$this->indChar = (string) $indent_char;
		$this->indFirst = (int) $indent_first + $this->indent;
		$this->assign = (string) $assign;
	}

	public function process()
	{
		// gets paragraphs
		$pgs = explode("\n", str_replace(array("\r\n", "\r"), "\n", $this->buffer));

		while(list($i,) = each($pgs))
		{
			if(empty($pgs[$i]))
				continue;

			// removes line breaks and extensive white space
			$pgs[$i] = preg_replace(array('#\s+#', '#^\s*(.+?)\s*$#m'), array(' ', '$1'), str_replace("\n", '', $pgs[$i]));

			// wordwraps + indents lines
			$pgs[$i] = str_repeat($this->indChar, $this->indFirst) .
			   		wordwrap(
							$pgs[$i],
							max($this->wrap - $this->indent, 1),
							$this->wrapChar . str_repeat($this->indChar, $this->indent),
							$this->wrapCut
					);
		}


		if($this->assign !== '')
			$this->dwoo->assignInScope(implode($this->wrapChar . $this->wrapChar, $pgs), $this->assign);
		else
			return implode($this->wrapChar . $this->wrapChar, $pgs);
	}
}

?>