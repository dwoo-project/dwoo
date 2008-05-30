<?php

/**
 * Generic else block, it supports all builtin optional-display blocks which are if/for/foreach/loop/with
 * 
 * If any of those block contains an else statement, the content between {else} and {/block} (you do not 
 * need to close the else block) will be shown if the block's condition has no been met
 * 
 * Example :
 * 
 * <code>
 * {foreach $array val}
 *   $array is not empty so we display it's values : {$val}
 * {else}
 *   if this shows, it means that $array is empty or doesn't exist.
 * {/foreach}
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
 * @version    0.9.1
 * @date       2008-05-30
 * @package    Dwoo
 */
class Dwoo_Plugin_else extends Dwoo_Block_Plugin implements Dwoo_ICompilable_Block
{
	public function init()
	{
	}

	public static function preProcessing(Dwoo_Compiler $compiler, array $params, $prepend='', $append='', $type)
	{
		$block =& $compiler->getCurrentBlock();
		$out = '';
		while ($block['type'] !== 'if' && $block['type'] !== 'foreach' && $block['type'] !== 'for' && $block['type'] !== 'with' && $block['type'] !== 'loop') {
			$out .= $compiler->removeTopBlock();
			$block =& $compiler->getCurrentBlock();
		}

		$out .= $block['params']['postOutput'];
		$block['params']['postOutput'] = '';
		$out = substr($out, 0, -strlen(Dwoo_Compiler::PHP_CLOSE));

		$currentBlock =& $compiler->getCurrentBlock();
		$currentBlock['params']['postOutput'] = Dwoo_Compiler::PHP_OPEN."\n}".Dwoo_Compiler::PHP_CLOSE;

		return $out . "else {\n".Dwoo_Compiler::PHP_CLOSE;
	}

	public static function postProcessing(Dwoo_Compiler $compiler, array $params, $prepend='', $append='')
	{
		if (isset($params['postOutput'])) {
			return $params['postOutput'];
		}
	}
}
