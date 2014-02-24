<?php
namespace Dwoo\Plugins\Functions;

use Dwoo\Plugin;

/**
 * Reverses a string or an array
 * <pre>
 *  * value : the string or array to reverse
 *  * preserve_keys : if value is an array and this is true, then the array keys are left intact
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
class FunctionReverse extends Plugin {

	public function process($value, $preserve_keys = false) {
		if (is_array($value)) {
			return array_reverse($value, $preserve_keys);
		}
		else if (($charset = $this->core->getCharset()) === 'iso-8859-1') {
			return strrev((string)$value);
		}
		else {
			$strlen = mb_strlen($value);
			$out    = '';
			while ($strlen --) {
				$out .= mb_substr($value, $strlen, 1, $charset);
			}

			return $out;
		}
	}
}

