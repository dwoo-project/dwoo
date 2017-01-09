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
 * Marks the variable as safe and removes the auto-escape function, only useful if you turned auto-escaping on
 * <pre>
 *  * var : the variable to pass through untouched
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginSafe extends Plugin implements ICompilable
{

    /**
     * @param Compiler $compiler
     * @param mixed    $var
     *
     * @return mixed
     */
    public static function compile(Compiler $compiler, $var)
    {
        return preg_replace('#\(is_string\(\$tmp=(.+)\) \? htmlspecialchars\(\$tmp, ENT_QUOTES, \$this->charset\) : \$tmp\)#',
            '$1',
            $var);
    }
}