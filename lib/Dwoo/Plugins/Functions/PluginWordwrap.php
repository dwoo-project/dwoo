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
 * Wraps a text at the given line length
 * <pre>
 *  * value : the text to wrap
 *  * length : maximum line length
 *  * break : the character(s) to use to break the line
 *  * cut : if true, the line is cut at the exact length instead of breaking at the nearest space
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginWordwrap extends Plugin implements ICompilable
{
    /**
     * @param Compiler $compiler
     * @param string   $value
     * @param int      $length
     * @param string   $break
     * @param bool     $cut
     *
     * @return string
     */
    public static function compile(Compiler $compiler, $value, $length = 80, $break = "\n", $cut = false)
    {
        return 'wordwrap(' . $value . ',' . $length . ',' . $break . ',' . $cut . ')';
    }
}