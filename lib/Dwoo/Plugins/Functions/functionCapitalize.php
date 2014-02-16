<?php
namespace Dwoo\Plugins\Functions;
use Dwoo\Core;

/**
 * Capitalizes the first letter of each word
 * <pre>
 *  * value : the string to capitalize
 *  * numwords : if true, the words containing numbers are capitalized as well
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-03
 * @package    Dwoo
 */
function functionCapitalize(Core $dwoo, $value, $numwords = false) {
	if ($numwords || preg_match('#^[^0-9]+$#', $value)) {
		return mb_convert_case((string)$value, MB_CASE_TITLE, $dwoo->getCharset());
	}
	else {
		$bits = explode(' ', (string)$value);
		$out  = '';
		while (list(, $v) = each($bits)) {
			if (preg_match('#^[^0-9]+$#', $v)) {
				$out .= ' ' . mb_convert_case($v, MB_CASE_TITLE, $dwoo->getCharset());
			}
			else {
				$out .= ' ' . $v;
			}
		}

		return substr($out, 1);
	}
}