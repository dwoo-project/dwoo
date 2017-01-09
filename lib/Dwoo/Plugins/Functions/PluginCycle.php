<?php
/**
 * Copyright (c) 2013-2017
 *
 * @category  Library
 * @package   Dwoo\Plugins\Functions
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2017 David Sanchez
 * @license   http://dwoo.org/LICENSE Modified BSD License
 * @version   1.3.2
 * @date      2017-01-06
 * @link      http://dwoo.org/
 */

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
 */
class PluginCycle extends Plugin
{
    protected $cycles = array();

    /**
     * @param string $name
     * @param null   $values
     * @param bool   $print
     * @param bool   $advance
     * @param string $delimiter
     * @param null   $assign
     * @param bool   $reset
     *
     * @return string|null
     */
    public function process($name = 'default', $values = null, $print = true, $advance = true, $delimiter = ',', $assign = null, $reset = false)
    {
        if ($values !== null) {
            if (is_string($values)) {
                $values = explode($delimiter, $values);
            }

            if (!isset($this->cycles[$name]) || $this->cycles[$name]['values'] !== $values) {
                $this->cycles[$name]['index'] = 0;
            }

            $this->cycles[$name]['values'] = array_values($values);
        } elseif (isset($this->cycles[$name])) {
            $values = $this->cycles[$name]['values'];
        }

        if ($reset) {
            $this->cycles[$name]['index'] = 0;
        }

        if ($print) {
            $out = $values[$this->cycles[$name]['index']];
        } else {
            $out = null;
        }

        if ($advance) {
            if ($this->cycles[$name]['index'] >= count($values) - 1) {
                $this->cycles[$name]['index'] = 0;
            } else {
                ++ $this->cycles[$name]['index'];
            }
        }

        if ($assign === null) {
            return $out;
        }
        $this->core->assignInScope($out, $assign);

        return null;
    }
}
