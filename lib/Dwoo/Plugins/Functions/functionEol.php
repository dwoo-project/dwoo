<?php
namespace Dwoo\Plugins\Functions;
use Dwoo\Compiler;

/**
 * Returns the correct end of line character(s) for the current operating system
 *
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
function functionEolCompile(Compiler $compiler) {
	return 'PHP_EOL';
}