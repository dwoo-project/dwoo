<?php

/**
 * Returns a variable or a default value if it's empty
 * <pre>
 *  * value : the variable to check
 *  * default : fallback value if the first one is empty
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  2008-2013 Jordi Boggiano
 * @copyright  2013-2016 David Sanchez
 * @license    http://dwoo.org/LICENSE   Modified BSD License
 *
 * @link       http://dwoo.org/
 *
 * @version    1.2.3
 * @date       2016-10-15
 */
function Dwoo_Plugin_default_compile(Dwoo_Compiler $compiler, $value, $default = '')
{
    return '(($tmp = '.$value.')===null||$tmp===\'\' ? '.$default.' : $tmp)';
}
