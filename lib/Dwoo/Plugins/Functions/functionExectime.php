<?php
namespace Dwoo\Plugins\Functions;
use Dwoo\Core;

/**
 * Return execution time of the php program.
 * <pre>
 *  * $precision : The optional number of decimal digits to round to.
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 * @author     David Sanchez <david38.sanchez@gmail.com>
 * @copyright  Copyright (c) 2013, David Sanchez
 * @license    http://dwoo.org/LICENSE   Modified BSD License
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-07
 * @package    Dwoo
 */
function functionExectime(Core $core, $precision = 0) {
	if (version_compare(PHP_VERSION, '5.4', '<')) {
		return round(((float) array_sum(explode(' ',microtime())) - ((float) $_SERVER['REQUEST_TIME'])) * 10, $precision);
	}
	else {
		return round((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000, $precision);
	}
}