<?php
namespace Dwoo\Plugins\Functions;
use Dwoo\Compiler;

/**
 * Counts the characters in a string
 * <pre>
 *  * value : the string to process
 *  * count_spaces : if true, the white-space characters are counted as well
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-05
 * @package    Dwoo
 */
function functionCountCharactersCompile(Compiler $compiler, $value, $count_spaces = false) {
	if ($count_spaces === false) {
		return 'preg_match_all(\'#[^\s\pZ]#u\', ' . $value . ', $tmp)';
	}
	else {
		return 'mb_strlen(' . $value . ', $this->charset)';
	}
}