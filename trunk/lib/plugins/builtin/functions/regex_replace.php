<?php

/**
 * Replaces the search string by the replace string using regular expressions
 * <pre>
 *  * value : the string to search into
 *  * search : the string to search for, must be a complete regular expression including delimiters
 *  * replace : the string to use as a replacement, must be a complete regular expression including delimiters
 * </pre>
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
 * @version    0.9.1
 * @date       2008-05-30
 * @package    Dwoo
 */
function Dwoo_Plugin_regex_replace(Dwoo $dwoo, $value, $search, $replace)
{
	// Credits for this to Monte Ohrt who made smarty's regex_replace modifier
	if (($pos = strpos($search, "\0")) !== false) {
		$search = substr($search, 0, $pos);
	}

	if (preg_match('#([a-z\s]+)$#i', $search, $m) && strpos($m[0], 'e') !== false) {
		$search = substr($search, 0, -strlen($m[0])) . str_replace('e', '', $m[0]);
	}

	return preg_replace($search, $replace, $value);
}
