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
class DwooPlugin_foreachelse extends DwooBlockPlugin implements DwooICompilableBlock
{
	public function init()
	{
	}

	public static function preProcessing(DwooCompiler $compiler, array $params, $prepend='', $append='', $type)
	{
		$foreach =& $compiler->findBlock('foreach', true);
		$out = $foreach['params']['postOutput'];
		$foreach['params']['postOutput'] = '';

		$compiler->injectBlock($type, $params, 1);

		if(substr($out, -strlen(DwooCompiler::PHP_CLOSE)) === DwooCompiler::PHP_CLOSE)
			$out = substr($out, 0, -strlen(DwooCompiler::PHP_CLOSE));
		else
			$out .= DwooCompiler::PHP_OPEN;

		return $out . "else\n{" . DwooCompiler::PHP_CLOSE;
	}

	public static function postProcessing(DwooCompiler $compiler, array $params, $prepend='', $append='')
	{
		return DwooCompiler::PHP_OPEN.'}'.DwooCompiler::PHP_CLOSE;
	}
}

?>