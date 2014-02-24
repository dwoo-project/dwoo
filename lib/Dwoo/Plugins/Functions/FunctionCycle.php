<?php
namespace Dwoo\Plugins\Functions;

use Dwoo\Plugin;

/**
 * Cycles between several values and returns one of them on each call
 * <pre>
 *  * name : the cycler name, specify if you need to have multiple concurrent cycles running
 *  * values : an array of values or a string of values delimited by $delimiter
 *  * print : if false, the pointer will go to the next one but not print anything
 *  * advance : if false, the pointer will not advance to the next value
 *  * delimiter : the delimiter used to split values if they are provided as a string
 *  * assign : if set, the value is saved in that variable instead of being output
 *  * reset : if true, the pointer is reset to the first value
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
class FunctionCycle extends Plugin {

	protected $cycles = array();

	public function process($name = 'default', $values = null, $print = true, $advance = true, $delimiter = ',', $assign = null, $reset = false) {
		if ($values !== null) {
			if (is_string($values)) {
				$values = explode($delimiter, $values);
			}

			if (!isset($this->cycles[$name]) || $this->cycles[$name]['values'] !== $values) {
				$this->cycles[$name]['index'] = 0;
			}

			$this->cycles[$name]['values'] = array_values($values);
		}
		elseif (isset($this->cycles[$name])) {
			$values = $this->cycles[$name]['values'];
		}

		if ($reset) {
			$this->cycles[$name]['index'] = 0;
		}

		if ($print) {
			$out = $values[$this->cycles[$name]['index']];
		}
		else {
			$out = null;
		}

		if ($advance) {
			if ($this->cycles[$name]['index'] >= count($values) - 1) {
				$this->cycles[$name]['index'] = 0;
			}
			else {
				$this->cycles[$name]['index']++;
			}
		}

		if ($assign === null) {
			return $out;
		}
		$this->core->assignInScope($out, $assign);

		return null;
	}
}