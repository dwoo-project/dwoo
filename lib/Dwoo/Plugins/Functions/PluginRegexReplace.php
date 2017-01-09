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
 * Replaces the search string by the replace string using regular expressions
 * <pre>
 *  * value : the string to search into
 *  * search : the string to search for, must be a complete regular expression including delimiters
 *  * replace : the string to use as a replacement, must be a complete regular expression including delimiters
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginRegexReplace extends Plugin
{
    /**
     * @param string $value
     * @param string $search
     * @param string $replace
     *
     * @return mixed
     */
    public function process($value, $search, $replace)
    {
        $search = (array)$search;
        $cnt    = count($search);

        for ($i = 0; $i < $cnt; ++ $i) {
            // Credits for this to Monte Ohrt who made smarty's regex_replace modifier
            if (($pos = strpos($search[$i], "\0")) !== false) {
                $search[$i] = substr($search[$i], 0, $pos);
            }

            if (preg_match('#[a-z\s]+$#is', $search[$i], $m) && (strpos($m[0], 'e') !== false)) {
                $search[$i] = substr($search[$i], 0, - strlen($m[0])) . str_replace(array(
                        'e',
                        ' '
                    ),
                        '',
                        $m[0]);
            }
        }

        return preg_replace($search, $replace, $value);
    }
}