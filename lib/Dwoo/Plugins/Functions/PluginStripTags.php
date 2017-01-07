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
 * @version   1.3.3
 * @date      2017-01-07
 * @link      http://dwoo.org/
 */

namespace Dwoo\Plugins\Functions;

use Dwoo\Compiler;
use Dwoo\ICompilable;
use Dwoo\Plugin;

/**
 * Removes all html tags
 * <pre>
 *  * value: the string to process
 *  * addspace: if true, a space is added in place of every removed tag
 *  * allowable_tags: specify tags which should not be stripped
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginStripTags extends Plugin implements ICompilable
{
    /**
     * @param Compiler    $compiler
     * @param string      $value
     * @param bool        $addspace
     * @param null|string $allowable_tags
     *
     * @return string
     */
    public static function compile(Compiler $compiler, $value, $addspace = true, $allowable_tags = null)
    {
        if ($addspace === 'true') {
            if ("null" == $allowable_tags) {
                return "preg_replace('#<[^>]*>#', ' ', $value)";
            }

            return "preg_replace('#<\\s*\\/?(" . $allowable_tags . ")\\s*[^>]*?>#im', ' ', $value)";
        }

        return "strip_tags($value, $allowable_tags)";
    }
}