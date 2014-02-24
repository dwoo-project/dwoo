<?php
namespace Dwoo\Plugins\Functions;

use Dwoo\Compiler;
use Dwoo\ICompilable;
use Dwoo\Plugin;

/**
 * Wraps a text at the given line length
 * <pre>
 *  * value : the text to wrap
 *  * length : maximum line length
 *  * break : the character(s) to use to break the line
 *  * cut : if true, the line is cut at the exact length instead of breaking at the nearest space
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
class FunctionWordwrap extends Plugin implements ICompilable {

	public static function compile(Compiler $compiler, $value, $length = 80, $break = "\n", $cut = false) {
		return 'wordwrap(' . $value . ',' . $length . ',' . $break . ',' . $cut . ')';
	}
}

