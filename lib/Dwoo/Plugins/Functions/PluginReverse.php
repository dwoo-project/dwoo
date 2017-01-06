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
 * Reverses a string or an array
 * <pre>
 *  * value : the string or array to reverse
 *  * preserve_keys : if value is an array and this is true, then the array keys are left intact
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginReverse extends Plugin
{
    /**
     * @param string $value
     * @param bool   $preserve_keys
     *
     * @return array|string
     */
    public function process($value, $preserve_keys = false)
    {
        if (is_array($value)) {
            return array_reverse($value, $preserve_keys);
        } elseif (($charset = $this->core->getCharset()) === 'iso-8859-1') {
            return strrev((string)$value);
        }

        $strlen = mb_strlen($value);
        $out    = '';
        while ($strlen --) {
            $out .= mb_substr($value, $strlen, 1, $charset);
        }

        return $out;
    }
}