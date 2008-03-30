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
 * @version    0.3.3
 * @date       2008-03-19
 * @package    Dwoo
 */
function DwooPlugin_escape(Dwoo $dwoo, $value='', $format='html', $charset=null)
{
	if($charset === null)
		$charset = $dwoo->getCharset();

	switch($format)
	{
		case 'html':
			return htmlspecialchars((string) $value, ENT_QUOTES, $charset);
		case 'htmlall':
		    return htmlentities((string) $value, ENT_QUOTES, $charset);
		case 'url':
		    return rawurlencode((string) $value);
		case 'urlpathinfo':
			return str_replace('%2F', '/', rawurlencode((string) $value));
		case 'quotes':
			return preg_replace("#(?<!\\\\)'#", "\\'", (string) $value);
		case 'hex':
			$out = '';
			$cnt = strlen((string) $value);
			for ($i=0; $i < $cnt; $i++) {
			    $out .= '%' . bin2hex((string) $value[$i]);
	    	}
		    return $out;
		case 'hexentity':
			$out = '';
			$cnt = strlen((string) $value);
			for($i=0; $i < $cnt; $i++)
			    $out .= '&#x' . bin2hex((string) $value[$i]) . ';';
		    return $out;
		case 'javascript':
			return strtr((string) $value, array('\\'=>'\\\\',"'"=>"\\'",'"'=>'\\"',"\r"=>'\\r',"\n"=>'\\n','</'=>'<\/'));
		case 'mail':
			return str_replace(array('@', '.'), array('&nbsp;(AT)&nbsp;', '&nbsp;(DOT)&nbsp;'), (string) $value);
		default:
			throw new Exception('Escape\'s format argument must be one of : html, htmlall, url, urlpathinfo, hex, hexentity, javascript or mail, "'.$format.'" given.');
	}
}

?>