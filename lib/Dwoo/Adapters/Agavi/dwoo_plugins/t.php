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
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    1.0.0
 * @date       2008-10-23
 * @package    Dwoo
 */
function Dwoo_Plugin_t_compile(Dwoo_Compiler $compiler, $string)
{
	return '$this->data[\'tm\']->_('.$string.')';
}