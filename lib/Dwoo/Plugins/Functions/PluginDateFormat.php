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

use Dwoo\Core;
use Dwoo\Plugin;

/**
 * Formats a date
 * <pre>
 *  * value : the date, as a unix timestamp, mysql datetime or whatever strtotime() can parse
 *  * format : output format, see {@link http://php.net/strftime} for details
 *  * default : a default timestamp value, if the first one is empty
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginDateFormat extends Plugin
{
    /**
     * @param mixed  $value
     * @param string $format
     * @param null   $default
     *
     * @return string
     */
    public function process($value, $format = '%b %e, %Y', $default = null)
    {
        if (!empty($value)) {
            // convert if it's not a valid unix timestamp
            if (preg_match('#^-?\d{1,10}$#', $value) === 0) {
                $value = strtotime($value);
            }
        } elseif (!empty($default)) {
            // convert if it's not a valid unix timestamp
            if (preg_match('#^-?\d{1,10}$#', $default) === 0) {
                $value = strtotime($default);
            } else {
                $value = $default;
            }
        } else {
            return '';
        }

        // Credits for that windows compat block to Monte Ohrt who made smarty's date_format plugin
        if (DIRECTORY_SEPARATOR == '\\') {
            $_win_from = array(
                '%D',
                '%h',
                '%n',
                '%r',
                '%R',
                '%t',
                '%T'
            );
            $_win_to   = array(
                '%m/%d/%y',
                '%b',
                "\n",
                '%I:%M:%S %p',
                '%H:%M',
                "\t",
                '%H:%M:%S'
            );
            if (strpos($format, '%e') !== false) {
                $_win_from[] = '%e';
                $_win_to[]   = sprintf('%\' 2d', date('j', $value));
            }
            if (strpos($format, '%l') !== false) {
                $_win_from[] = '%l';
                $_win_to[]   = sprintf('%\' 2d', date('h', $value));
            }
            $format = str_replace($_win_from, $_win_to, $format);
        }

        return strftime($format, $value);
    }
}