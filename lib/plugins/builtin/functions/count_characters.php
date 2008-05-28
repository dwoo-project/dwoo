<?php

/**
 * Counts the characters in a string
 * <pre>
 *  * value : the string to process
 *  * count_spaces : if true, the white-space characters are counted as well
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
 * @version    0.9.0
 * @date       2008-05-10
 * @package    Dwoo
 */
function Dwoo_Plugin_count_characters_compile(Dwoo_Compiler $compiler, $value, $count_spaces=false)
{
	if($count_spaces==='false')
		return 'preg_match_all(\'#[^\s\pZ]#u\', '.$value.', $tmp)';
	else
		return 'mb_strlen('.$value.', $this->charset)';
}
