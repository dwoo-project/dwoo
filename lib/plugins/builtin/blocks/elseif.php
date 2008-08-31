<?php

/**
 * Acts as a php elseif block, allowing you to add one more condition
 * if the previous one(s) didn't match. See the {if} plugin for syntax details
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
class Dwoo_Plugin_elseif extends Dwoo_Plugin_if implements Dwoo_ICompilable_Block, Dwoo_IElseable
{
	public function init(array $rest)
	{
	}

	public static function preProcessing(Dwoo_Compiler $compiler, array $params, $prepend, $append, $type)
	{
		$preContent = '';
		while (true) {
			$preContent .= $compiler->removeTopBlock();
			$block =& $compiler->getCurrentBlock();
			$interfaces = class_implements($block['class'], false);
			if (in_array('Dwoo_IElseable', $interfaces) !== false) {
				break;
			}
		}

		$params['initialized'] = true;
		$compiler->injectBlock($type, $params);
		return $preContent;
	}

	public static function postProcessing(Dwoo_Compiler $compiler, array $params, $prepend, $append, $content)
	{
		if (!isset($params['initialized'])) {
			return '';
		}

		$params = $compiler->getCompiledParams($params);

		$pre = Dwoo_Compiler::PHP_OPEN."elseif (".implode(' ', self::replaceKeywords($params['*'], $compiler)).") {\n" . Dwoo_Compiler::PHP_CLOSE;
		$post = Dwoo_Compiler::PHP_OPEN."\n}".Dwoo_Compiler::PHP_CLOSE;

		if (isset($params['hasElse'])) {
			$post .= $params['hasElse'];
		}

		$block =& $compiler->getCurrentBlock();
		$block['params']['hasElse'] = $pre . $content . $post;
		return '';
	}
}
