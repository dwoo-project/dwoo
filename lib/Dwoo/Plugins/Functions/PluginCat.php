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
 * Concatenates any number of variables or strings fed into it
 * <pre>
 *  * rest : two or more strings that will be merged into one
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginCat extends Plugin implements ICompilable
{
    /**
     * @param Compiler $compiler
     * @param string   $value
     * @param array    $rest
     *
     * @return string
     */
    public static function compile(Compiler $compiler, $value, array $rest)
    {
        return '(' . $value . ').(' . implode(').(', $rest) . ')';
    }
}
