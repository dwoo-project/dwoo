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
 * Replaces the search string by the replace string
 * <pre>
 *  * value : the string to search into
 *  * search : the string to search for
 *  * replace : the string to use as a replacement
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginReplace extends Plugin implements ICompilable
{
    /**
     * @param Compiler $compiler
     * @param string   $value
     * @param string   $search
     * @param string   $replace
     * @param bool     $case_sensitive
     *
     * @return string
     */
    public static function compile(Compiler $compiler, $value, $search, $replace, $case_sensitive = true)
    {
        if ($case_sensitive == 'false' || (bool)$case_sensitive === false) {
            return 'str_ireplace(' . $search . ', ' . $replace . ', ' . $value . ')';
        } else {
            return 'str_replace(' . $search . ', ' . $replace . ', ' . $value . ')';
        }
    }
}