<?php

/**
 * TOCOM
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
 * @version    0.3.4
 * @date       2008-04-09
 * @package    Dwoo
 */
class DwooPlugin_else extends DwooBlockPlugin implements DwooICompilableBlock
{
	public function init()
	{
	}

	public static function preProcessing(DwooCompiler $compiler, array $params, $prepend='', $append='', $type)
	{
		$block =& $compiler->getCurrentBlock();
		$out = '';
		while($block['type'] !== 'if' && $block['type'] !== 'foreach' && $block['type'] !== 'for' && $block['type'] !== 'with' && $block['type'] !== 'loop')
		{
			$out .= $compiler->removeTopBlock();
			$block =& $compiler->getCurrentBlock();
		}

		$out .= $block['params']['postOutput'];
		$block['params']['postOutput'] = '';
		$out = substr($out, 0, -strlen(DwooCompiler::PHP_CLOSE));

		$currentBlock =& $compiler->getCurrentBlock();
		$currentBlock['params']['postOutput'] = DwooCompiler::PHP_OPEN."\n}".DwooCompiler::PHP_CLOSE;

		return $out . "else {\n".DwooCompiler::PHP_CLOSE;
	}

	public static function postProcessing(DwooCompiler $compiler, array $params, $prepend='', $append='')
	{
		if(isset($params['postOutput']))
			return $params['postOutput'];
	}
}

?>