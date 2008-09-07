<?php

/**
 * <strong>Agavi specific plugin</strong>
 *
 * uses AgaviTranslationManager to localize a string
 *
 * <pre>
 *  * string : the string to localize
 * </pre>
 *
 * Examples:
 * <code>
 * {t "Hello"}
 * {t $header}
 * </code>
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * This file is released under the LGPL
 * "GNU Lesser General Public License"
 * More information can be found here:
 * {@link http://www.gnu.org/copyleft/lesser.html}
 *
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @copyright  Copyright (c) 2008, Jordi Boggiano
 * @license    http://www.gnu.org/copyleft/lesser.html  GNU Lesser General Public License
 * @link       http://dwoo.org/
 * @version    1.0.0
 * @date       2008-09-08
 * @package    Dwoo
 */
function Dwoo_Plugin_t_compile(Dwoo_Compiler $compiler, $string)
{
	return '$this->data[\'tm\']->_('.$string.')';
}