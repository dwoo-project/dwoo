<?php
namespace Dwoo\Plugins\Functions;

use Dwoo\Plugin;

/**
 * Formats a given filesize in bytes into a human readable filesize
 * <pre>
 *  * size : the filesize in bytes
 *  * unit : the output unit desired
 *  * decimals : round to display
 * </pre>
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * This file is released under the LGPL
 * "GNU Lesser General Public License"
 * More information can be found here:
 * {@link http://www.gnu.org/copyleft/lesser.html}
 *
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2014-02-24
 * @package    Dwoo
 */
class FunctionFormatSize extends Plugin {

	public function process($size, $unit = '', $decimals = 2) {
		$units = array('B'  => 0,
					   'KB' => 1,
					   'MB' => 2,
					   'GB' => 3,
					   'TB' => 4,
					   'PB' => 5,
					   'EB' => 6,
					   'ZB' => 7,
					   'YB' => 8
		);

		$value = 0;
		if ($size > 0) {
			// Generate automatic prefix by bytes
			// If wrong prefix given
			if (!array_key_exists($unit, $units)) {
				$pow  = floor(log($size) / log(1024));
				$unit = array_search($pow, $units);
			}

			// Calculate byte value by prefix
			$value = ($size / pow(1024, floor($units[$unit])));
		}

		// If decimals is not numeric or decimals is less than 0
		// then set default value
		if (!is_numeric($decimals) || $decimals < 0) {
			$decimals = 2;
		}

		// Format output
		return sprintf('%.' . $decimals . 'f ' . $unit, $value);
	}
}
