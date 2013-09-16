<?php
namespace Dwoo\Plugins\Functions;
use Dwoo\Compiler;

/**
 * Counts the sentences in a string
 * <pre>
 *  * value : the string to process
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @source     http://snipplr.com/view/6910/
 * @author     David Sanchez <david38.sanchez@gmail.com>
 * @copyright  Copyright (c) 2013, David Sanchez
 * @license    http://dwoo.org/LICENSE   Modified BSD License
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-05
 * @package    Dwoo
 */
function functionCountSentencesCompile(Compiler $compiler, $value) {
	return "preg_match_all('/[^\\s](\\.|\\!|\\?)(?!\\w)/',$value, \$tmp)";
}

