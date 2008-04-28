<?php

/**
 * TOCOM
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * This file is released under the LGPL
 * "GNU Lesser General Public License"
 * More information can be found here:
 * {@link http://www.gnu.org/copyleft/lesser.html}
 *
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @copyright  Copyright (c) 2008, Jordi Boggiano
 * @license    http://www.gnu.org/copyleft/lesser.html  GNU Lesser General Public License
 * @link       http://dwoo.org/
 * @version    0.3.4
 * @date       2008-04-09
 * @package    Dwoo
 */
class DwooPlugin_counter extends DwooPlugin
{
	protected $counters = array();

	public function process($name = 'default', $start = null, $skip = null, $direction = null, $print = null, $assign = null)
	{
		// init counter
		if(!isset($this->counters[$name]))
		{
			$this->counters[$name] = array
			(
				'count'		=>	$start===null ? 1 : (int) $start,
				'skip'		=>	$skip===null ? 1 : (int) $skip,
				'print'		=>	$print===null ? true : (bool) $print,
				'assign'	=>	$assign===null ? null : (string) $assign,
				'direction'	=>	strtolower($direction)==='down' ? -1 : 1,
			);
		}
		// increment
		else
		{
			// override setting if present
			if($skip !== null)
				$this->counters[$name]['skip'] = (int) $skip;

			if($direction !== null)
				$this->counters[$name]['direction'] = strtolower($direction)==='down' ? -1 : 1;

			if($print !== null)
				$this->counters[$name]['print'] = (bool) $print;

			if($assign !== null)
				$this->counters[$name]['assign'] = (string) $assign;

			if($start !== null)
				$this->counters[$name]['count'] = (int) $start;
			else
				$this->counters[$name]['count'] += ($this->counters[$name]['skip'] * $this->counters[$name]['direction']);
		}

		$out = $this->counters[$name]['count'];

		if($this->counters[$name]['assign'] !== null)
			$this->dwoo->assignInScope($out, $this->counters[$name]['assign']);
		elseif($this->counters[$name]['print'] === true)
			return $out;
	}
}

?>