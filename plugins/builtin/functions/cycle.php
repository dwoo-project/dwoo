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
 * @version    0.3.3
 * @date       2008-03-19
 * @package    Dwoo
 */
class DwooPlugin_cycle extends DwooPlugin
{
	protected $cycles = array();

	public function process($name = 'default', $values = null, $print = true, $advance = true, $delimiter = ',', $assign = null, $reset = false)
	{
		if($values !== null)
		{
			if(is_string($values))
				$values = explode($delimiter, $values);

			if(!isset($this->cycles[$name]) || $this->cycles[$name]['values'] !== $values)
				$this->cycles[$name]['index'] = 0;

			$this->cycles[$name]['values'] = array_values($values);
		}
		elseif(isset($this->cycles[$name]))
		{
			$values = $this->cycles[$name]['values'];
		}

		if($reset)
			$this->cycles[$name]['index'] = 0;

		if($print)
			$out = $values[$this->cycles[$name]['index']];
		else
			$out = null;

		if($advance)
	    {
			if($this->cycles[$name]['index'] >= count($values)-1)
				$this->cycles[$name]['index'] = 0;
			else
				$this->cycles[$name]['index']++;
		}

		if($assign !== null)
			$this->dwoo->assignInScope($assign, $out);
		else
			return $out;
	}
}

?>