<?php
namespace Dwoo;

/**
 * interface that represents a compilable plugin
 *
 * implement this to notify the compiler that this plugin does not need to be loaded at runtime.
 *
 * to implement it right, you must implement <em>public static function compile(Compiler $compiler, $arg, $arg, ...)</em>,
 * which replaces the <em>process()</em> method (that means <em>compile()</em> should have all arguments it requires).
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-01
 * @package    Dwoo
 */
interface ICompilable {
	// this replaces the process function
	//public static function compile(Compiler $compiler, $arg, $arg, ...);
}
