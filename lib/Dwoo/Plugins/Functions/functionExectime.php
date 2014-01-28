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
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2014-01-24
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