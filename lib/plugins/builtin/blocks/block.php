<?php

/**
 * This is used only when rendering a template that has blocks but is not extending anything, 
 * it doesn't do anything by itself and should not be used outside of template inheritance context,
 * see {@link http://wiki.dwoo.org/index.php/TemplateInheritance} to read more about it.
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
 * @version    0.9.1
 * @date       2008-05-30
 * @package    Dwoo
 */
class Dwoo_Plugin_block extends Dwoo_Block_Plugin implements Dwoo_ICompilable_Block
{
	public function init($name='')
	{
	}

	public static function preProcessing(Dwoo_Compiler $compiler, array $params, $prepend='', $append='', $type)
	{
		return '';
	}

	public static function postProcessing(Dwoo_Compiler $compiler, array $params, $prepend='', $append='')
	{
		return '';
	}
}
