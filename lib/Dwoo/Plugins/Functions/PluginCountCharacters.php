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
 * Counts the characters in a string
 * <pre>
 *  * value : the string to process
 *  * count_spaces : if true, the white-space characters are counted as well
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginCountCharacters extends Plugin implements ICompilable
{
    /**
     * @param Compiler $compiler
     * @param string   $value
     * @param bool     $count_spaces
     *
     * @return string
     */
    public static function compile(Compiler $compiler, $value, $count_spaces = false)
    {
        if ($count_spaces === 'false') {
            return 'preg_match_all(\'#[^\s\pZ]#u\', ' . $value . ', $tmp)';
        }

        return 'mb_strlen(' . $value . ', $this->charset)';
    }
}