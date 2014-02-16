<?php
namespace Dwoo\Plugins\Functions;
use Dwoo\Compiler;

/**
 * Concatenates any number of variables or strings fed into it
 * <pre>
 *  * rest : two or more strings that will be merged into one
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
function functionCatCompile(Compiler $compiler, $value, array $rest) {
	return '(' . $value . ').(' . implode(').(', $rest) . ')';
}