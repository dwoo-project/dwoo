<?php

/**
 * Replaces the search string by the replace string
 * <pre>
 *  * value : the string to search into
 *  * search : the string to search for
 *  * replace : the string to use as a replacement
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  2008-2013 Jordi Boggiano
 * @copyright  2013-2016 David Sanchez
 * @license    http://dwoo.org/LICENSE   Modified BSD License
 * @link       http://dwoo.org/
 * @version    1.2.3
 * @date       2016-10-15
 * @package    Dwoo
 */
function Dwoo_Plugin_replace_compile(Dwoo_Compiler $compiler, $value, $search, $replace, $case_sensitive = true)
{
	if ($case_sensitive == 'false' || (bool)$case_sensitive === false) {
		return 'str_ireplace('.$search.', '.$replace.', '.$value.')';
	} else {
		return 'str_replace('.$search.', '.$replace.', '.$value.')';
	}
}
