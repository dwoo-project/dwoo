<?php

/**
 * Returns a variable or a default value if it's empty
 * 
 *  * value : the variable to check
 *  * default : fallback value if the first one is empty
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
 * @version    0.9.0
 * @date       2008-05-10
 * @package    Dwoo
 */
function Dwoo_Plugin_default_compile(Dwoo_Compiler $compiler, $value, $default='')
{
	return '(($tmp = '.$value.')===null||$tmp===\'\' ? '.$default.' : $tmp)';
}
