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
 * @version    0.3.3
 * @date       2008-03-19
 * @package    Dwoo
 */
class DwooPlugin_elseif extends DwooPlugin_if implements DwooICompilableBlock
{
	public function init(array $rest)
	{
	}

	public static function preProcessing(DwooCompiler $compiler, array $params, $prepend='', $append='', $type)
	{
		$if =& $compiler->findBlock('if', true);
		$out = $if['params']['postOutput'];
		$if['params']['postOutput'] = '';

		$compiler->injectBlock($type, $params, 1);
		$currentBlock =& $compiler->getCurrentBlock();
		$currentBlock['params']['postOutput'] = DwooCompiler::PHP_OPEN."\n}".DwooCompiler::PHP_CLOSE;

		if($out === '')
			$out = DwooCompiler::PHP_OPEN."\n}";
		else
			$out = substr($out, 0, -strlen(DwooCompiler::PHP_CLOSE));

		return $out . " elseif(".implode(' ', self::replaceKeywords($params, $compiler)).") {\n" . DwooCompiler::PHP_CLOSE;
	}

	public static function postProcessing(DwooCompiler $compiler, array $params, $prepend='', $append='')
	{
		if(isset($params['postOutput']))
			return $params['postOutput'];
	}
}

?>