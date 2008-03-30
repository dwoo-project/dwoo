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
function DwooPlugin_capitalize(Dwoo $dwoo, $value, $numwords=false)
{
	if($numwords || preg_match('#^[^0-9]+$#',$value))
	{
		if($dwoo->getCharset() === 'iso-8859-1')
			return ucwords((string) $value);
		return mb_convert_case((string) $value,MB_CASE_TITLE, $dwoo->getCharset());
	}
	else
	{
		$bits = explode(' ', (string) $value);
		$out = '';
		while(list(,$v) = each($bits))
			if(preg_match('#^[^0-9]+$#', $v))
				$out .=	' '.mb_convert_case($v, MB_CASE_TITLE, $dwoo->getCharset());
			else
				$out .=	' '.$v;

		return substr($out, 1);
	}
}

?>