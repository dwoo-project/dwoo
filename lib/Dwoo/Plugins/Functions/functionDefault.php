<?php
namespace Dwoo\Plugins\Functions;
use Dwoo\Compiler;

/**
 * Returns a variable or a default value if it's empty
 * <pre>
 *  * value : the variable to check
 *  * default : fallback value if the first one is empty
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE   Modified BSD License
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-06
 * @package    Dwoo
 */
function functionDefaultCompile(Compiler $compiler, $value, $default = '') {
	return '(($tmp = ' . $value . ')===null||$tmp===\'\' ? ' . $default . ' : $tmp)';
}
