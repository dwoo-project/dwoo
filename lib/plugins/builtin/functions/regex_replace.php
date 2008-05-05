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
function Dwoo_Plugin_regex_replace(Dwoo $dwoo, $value, $search, $replace)
{
	// Credits for this to Monte Ohrt who made smarty's regex_replace modifier
	if(($pos = strpos($search,"\0")) !== false)
		$search = substr($search,0,$pos);

	if(preg_match('#([a-z\s]+)$#i', $search, $m) && strpos($m[0], 'e') !== false) {
		$search = substr($search, 0, -strlen($m[0])) . str_replace('e', '', $m[0]);
	}

	return preg_replace($search, $replace, $value);
}
