<?php
namespace Dwoo\Plugins\Functions;

use \DateTimeZone;
use Dwoo\Exception;
use Dwoo\Plugin;

/**
 * Formats a date
 * <pre>
 *  * value : a date/time string, see (@link http://www.php.net/manual/en/datetime.formats.php) for supported formats
 *  * format : output format, see {@link http://www.php.net/manual/en/function.date.php} for details
 *  * timestamp : a valid timestamp value (int) needed
 *  * timeZone :
 *  * modify :
 * </pre>
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     David Sanchez <david38.sanchez@gmail.com>
 * @copyright  Copyright (c) 2013, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2014-02-24
 * @package    Dwoo
 */
class FunctionDateFormat extends Plugin {

	public function process($value = 'now', $format = 'M n, Y', $timestamp = 0, $timeZone = 2047, $modify = '') {
		if (!is_string($value)) {
			throw new Exception('$value is not a valid value: must be a string');
		}

		$dateTime = new \DateTime($value);

		if ($timestamp != 0) {
			$dateTime->setTimestamp($timestamp);
		}

		if ($timeZone != 2047) {
			$dateTime->setTimezone(new \DateTimeZone($timeZone));
		}

		if ($modify != '') {
			$dateTime->modify($modify);
		}

		return $dateTime->format($format);
	}
}

