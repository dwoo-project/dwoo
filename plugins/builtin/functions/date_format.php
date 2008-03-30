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
function DwooPlugin_date_format(Dwoo $dwoo, $value, $format='%b %e, %Y', $default=null)
{
	if(!empty($value))
	{
		// don't convert if it's a valid unix timestamp
		if(preg_match('#^\d{10}$#', $value)===false)
			$value = strtotime($value);
	}
	elseif(!empty($default))
	{
		// don't convert if it's a valid unix timestamp
		if(preg_match('#^\d{10}$#', $default)===false)
			$value = strtotime($default);
	}
	else
		return '';

	// Credits for that windows compat block to Monte Ohrt who made smarty's date_format plugin
	if(DIRECTORY_SEPARATOR == '\\')
	{
		$_win_from = array('%D', '%h', '%n', '%r', '%R', '%t', '%T');
		$_win_to   = array('%m/%d/%y', '%b', "\n", '%I:%M:%S %p', '%H:%M', "\t", '%H:%M:%S');
		if(strpos($format, '%e') !== false)
		{
			$_win_from[] = '%e';
			$_win_to[]   = sprintf('%\' 2d', date('j', $value));
		}
		if(strpos($format, '%l') !== false)
		{
			$_win_from[] = '%l';
			$_win_to[]   = sprintf('%\' 2d', date('h', $value));
		}
		$format = str_replace($_win_from, $_win_to, $format);
	}
	return strftime($format, $value);
}

?>