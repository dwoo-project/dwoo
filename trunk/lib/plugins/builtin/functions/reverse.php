<?php

/**
 * Reverses a string or an array
 * <pre>
 *  * value : the string or array to reverse
 *  * preserve_keys : if value is an array and this is true, then the array keys are left intact
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
function Dwoo_Plugin_reverse(Dwoo $dwoo, $value, $preserve_keys=false)
{
	if (is_array($value)) {
		return array_reverse($value, $preserve_keys);
	} elseif(($charset=$dwoo->getCharset()) === 'iso-8859-1') {
		return strrev((string) $value);
	} else {
	    $strlen = mb_strlen($value);
	    $out = '';
	    while ($strlen--) {
	        $out .= mb_substr($value, $strlen, 1, $charset);
	    }
		return $out;
	}
}
