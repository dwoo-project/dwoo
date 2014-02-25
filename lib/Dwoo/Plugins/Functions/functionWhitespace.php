<?php
namespace Dwoo\Plugins\Functions;

use Dwoo\Compiler;
use Dwoo\ICompilable;
use Dwoo\Plugin;

/**
 * Replaces all white-space characters with the given string
 * <pre>
 *  * value : the text to process
 *  * with : the replacement string, note that any number of consecutive white-space characters will be replaced by a single replacement string
 * </pre>
 * Example :
 *
 * <code>
 * {"a    b  c        d
 *
 * e"|whitespace}
 *
 * results in : a b c d e
 * </code>
 *
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
class FunctionWhitespace extends Plugin implements ICompilable {

	public static function compile(Compiler $compiler, $value, $with = ' ') {
		return "preg_replace('#\s+#'.(strcasecmp(\$this->charset, 'utf-8')===0?'u':''), $with, $value)";
	}
}
