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

use Dwoo\Compiler;
use Dwoo\ICompilable;
use Dwoo\Plugin;

/**
 * Formats a string using the sprintf function
 * <pre>
 *  * value : the string to format
 *  * format : the format to use, see {@link http://php.net/sprintf} for details
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginStringFormat extends Plugin implements ICompilable
{
    /**
     * @param Compiler $compiler
     * @param string   $value
     * @param string   $format
     *
     * @return string
     */
    public static function compile(Compiler $compiler, $value, $format)
    {
        return 'sprintf(' . $format . ',' . $value . ')';
    }
}