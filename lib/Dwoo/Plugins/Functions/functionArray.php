<?php
namespace Dwoo\Plugins\Functions;

use Dwoo\Compiler;
use Dwoo\ICompilable;
use Dwoo\Plugin;

/**
 * Builds an array with all the provided variables, use named parameters to make an associative array
 * <pre>
 *  * rest : any number of variables, strings or anything that you want to store in the array
 * </pre>
 * Example :
 *
 * <code>
 * {array(a, b, c)} results in array(0=>'a', 1=>'b', 2=>'c')
 * {array(a=foo, b=5, c=array(4,5))} results in array('a'=>'foo', 'b'=>5, 'c'=>array(0=>4, 1=>5))
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
class FunctionArray extends Plugin implements ICompilable {

	public static function compile(Compiler $compiler, array $rest = array()) {
		$out = array();
		foreach ($rest as $key => $value) {
			if (!is_numeric($key) && !strstr($key, '$this->scope')) {
				$key = "'" . $key . "'";
			}
			$out[] = $key . '=>' . $value;
		}

		return 'array(' . implode(', ', $out) . ')';
	}
}