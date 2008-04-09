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
class DwooPlugin_with extends DwooBlockPlugin implements DwooICompilableBlock
{
	protected static $cnt=0;

	public function init($var)
	{
	}

	public static function preProcessing(DwooCompiler $compiler, array $params, $prepend='', $append='', $type)
	{
		$rparams = $compiler->getRealParams($params);
		$cparams = $compiler->getCompiledParams($params);

		$c = $rparams['var'];

		$compiler->setScope($c);

		$params =& $compiler->getCurrentBlock();
		$params['params']['postOutput'] = DwooCompiler::PHP_OPEN."\n// -- end with output\n".'$this->forceScope($_with'.(self::$cnt).');'."\n}\n".DwooCompiler::PHP_CLOSE;

		return DwooCompiler::PHP_OPEN.'if('.$cparams['var'].')'."\n{\n".'$_with'.(self::$cnt++).' = $this->setScope("'.$c.'");'."\n// -- start with output\n".DwooCompiler::PHP_CLOSE;
	}

	public static function postProcessing(DwooCompiler $compiler, array $params, $prepend='', $append='')
	{
		return $params['postOutput'];
	}
}

?>