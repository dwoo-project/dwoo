<?php

/**
 * Converts line breaks into <br /> tags
 * <pre>
 *  * value : the string to process
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
function Dwoo_Plugin_nl2br_compile(Dwoo_Compiler $compiler, $value)
{
    return 'nl2br((string) '.$value.')';
}
