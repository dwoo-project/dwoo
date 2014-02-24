<?php
namespace Dwoo\Plugins\Functions;

use Dwoo\Plugin;

/**
 * Initiates a counter that is incremented every time you call it
 * <pre>
 *  * name : the counter name, define it if you want to have multiple concurrent counters
 *  * start : the start value, if it's set, it will reset the counter to this value, defaults to 1
 *  * skip : the value to add to the counter at each call, defaults to 1
 *  * direction : "up" (default) or "down" to define whether the counter increments or decrements
 *  * print : if false, the counter will not output the current count, defaults to true
 *  * assign : if set, the counter is saved into the given variable and does not output anything, overriding the print parameter
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
class FunctionCounter extends Plugin {
	protected $counters = array();

	public function process($name = 'default', $start = null, $skip = null, $direction = null, $print = null, $assign = null) {
		// init counter
		if (! isset($this->counters[$name])) {
			$this->counters[$name] = array
			(
				'count'     => $start === null ? 1 : (int)$start,
				'skip'      => $skip === null ? 1 : (int)$skip,
				'print'     => $print === null ? true : (bool)$print,
				'assign'    => $assign === null ? null : (string)$assign,
				'direction' => strtolower($direction) === 'down' ? - 1 : 1,
			);
		}
		// increment
		else {
			// override setting if present
			if ($skip !== null) {
				$this->counters[$name]['skip'] = (int)$skip;
			}

			if ($direction !== null) {
				$this->counters[$name]['direction'] = strtolower($direction) === 'down' ? - 1 : 1;
			}

			if ($print !== null) {
				$this->counters[$name]['print'] = (bool)$print;
			}

			if ($assign !== null) {
				$this->counters[$name]['assign'] = (string)$assign;
			}

			if ($start !== null) {
				$this->counters[$name]['count'] = (int)$start;
			}
			else {
				$this->counters[$name]['count'] += ($this->counters[$name]['skip'] * $this->counters[$name]['direction']);
			}
		}

		$out = $this->counters[$name]['count'];

		if ($this->counters[$name]['assign'] !== null) {
			$this->core->assignInScope($out, $this->counters[$name]['assign']);
		}
		elseif ($this->counters[$name]['print'] === true) {
			return $out;
		}
		return null;
	}
}
