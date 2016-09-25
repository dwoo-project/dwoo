<?php
/**
 * Copyright (c) 2013-2016
 *
 * @category  Library
 * @package   Dwoo\Plugins\Functions
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2016 David Sanchez
 * @license   http://dwoo.org/LICENSE Modified BSD License
 * @version   1.3.0
 * @date      2016-09-19
 * @link      http://dwoo.org/
 */

namespace Dwoo\Plugins\Functions;

use Dwoo\Core;

/**
 * Reads a file
 * <pre>
 *  * file : path or URI of the file to read (however reading from another website is not recommended for performance
 *  reasons)
 *  * assign : if set, the file will be saved in this variable instead of being output
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
function PluginFetch(Core $dwoo, $file, $assign = null)
{
    if ($file === '') {
        return '';
    }

    if ($policy = $dwoo->getSecurityPolicy()) {
        while (true) {
            if (preg_match('{^([a-z]+?)://}i', $file)) {
                $dwoo->triggerError('The security policy prevents you to read files from external sources.', E_USER_WARNING);
            }

            $file = realpath($file);
            $dirs = $policy->getAllowedDirectories();
            foreach ($dirs as $dir => $dummy) {
                if (strpos($file, $dir) === 0) {
                    break 2;
                }
            }
            $dwoo->triggerError('The security policy prevents you to read <em>' . $file . '</em>', E_USER_WARNING);
        }
    }
    $file = str_replace(array(
        "\t",
        "\n",
        "\r"
    ), array(
        '\\t',
        '\\n',
        '\\r'
    ), $file);

    $out = file_get_contents($file);

    if ($assign === null) {
        return $out;
    }
    $dwoo->assignInScope($out, $assign);
}
