<?php
namespace Dwoo\Plugins\Functions;
use Dwoo\Compiler;

/**
 * Checks whether a variable is not null
 * <pre>
 *  * var : variable to check
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @copyright  Copyright (c) 2008, Jordi Boggiano
 * @license    http://dwoo.org/LICENSE   Modified BSD License
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-06
 * @package    Dwoo
 */
function functionIssetCompile(Compiler $compiler, $var) {
	return '(' . $var . ' !== null)';
}
