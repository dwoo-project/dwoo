<?php

/**
 * This plugin serves as a {else} block specifically for the {with} plugin.
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
 * @version    0.9.0
 * @date       2008-05-10
 * @package    Dwoo
 */
class Dwoo_Plugin_withelse extends Dwoo_Block_Plugin implements Dwoo_ICompilable_Block
{
	public function init()
	{
	}

	public static function preProcessing(Dwoo_Compiler $compiler, array $params, $prepend='', $append='', $type)
	{
		$foreach =& $compiler->findBlock('with', true);
		$out = $foreach['params']['postOutput'];
		$foreach['params']['postOutput'] = '';

		$compiler->injectBlock($type, $params, 1);

		if(substr($out, -strlen(Dwoo_Compiler::PHP_CLOSE)) === Dwoo_Compiler::PHP_CLOSE)
			$out = substr($out, 0, -strlen(Dwoo_Compiler::PHP_CLOSE));
		else
			$out .= Dwoo_Compiler::PHP_OPEN;

		return $out . "else\n{" . Dwoo_Compiler::PHP_CLOSE;
	}

	public static function postProcessing(Dwoo_Compiler $compiler, array $params, $prepend='', $append='')
	{
		return Dwoo_Compiler::PHP_OPEN.'}'.Dwoo_Compiler::PHP_CLOSE;
	}
}
