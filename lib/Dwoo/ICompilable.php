<?php

/**
 * interface that represents a compilable plugin
 *
 * implement this to notify the compiler that this plugin does not need to be loaded at runtime.
 *
 * to implement it right, you must implement <em>public static function compile(Dwoo_Compiler $compiler, $arg, $arg, ...)</em>,
 * which replaces the <em>process()</em> method (that means <em>compile()</em> should have all arguments it requires).
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
 * @version    0.9.1
 * @date       2008-05-30
 * @package    Dwoo
 */
interface Dwoo_ICompilable
{
	// this replaces the process function
	//public static function compile(Dwoo_Compiler $compiler, $arg, $arg, ...);
}
