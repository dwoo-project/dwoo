<?php
namespace Dwoo\Plugins\Functions;

use Dwoo\Compiler;
use Dwoo\ICompilable;
use Dwoo\Plugin;

/**
 * Formats a string using the sprintf function
 * <pre>
 *  * value : the string to format
 *  * format : the format to use, see {@link http://php.net/sprintf} for details
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2014-02-24
 * @package    Dwoo
 */
class FunctionStringFormat extends Plugin implements ICompilable {

	public static function compile(Compiler $compiler, $value, $format) {
		return 'sprintf(' . $format . ',' . $value . ')';
	}
}
