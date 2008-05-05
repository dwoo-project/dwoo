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
class Dwoo_Plugin_with extends Dwoo_Block_Plugin implements Dwoo_ICompilable_Block
{
	protected static $cnt=0;

	public function init($var)
	{
	}

	public static function preProcessing(Dwoo_Compiler $compiler, array $params, $prepend='', $append='', $type)
	{
		$rparams = $compiler->getRealParams($params);
		$cparams = $compiler->getCompiledParams($params);

		$compiler->setScope($rparams['var']);

		$params =& $compiler->getCurrentBlock();
		$params['params']['postOutput'] = Dwoo_Compiler::PHP_OPEN."\n// -- end with output\n".'$this->forceScope($_with'.(self::$cnt).');'."\n}\n".Dwoo_Compiler::PHP_CLOSE;

		return Dwoo_Compiler::PHP_OPEN.'if('.$cparams['var'].')'."\n{\n".'$_with'.(self::$cnt++).' = $this->setScope("'.$rparams['var'].'");'."\n// -- start with output\n".Dwoo_Compiler::PHP_CLOSE;
	}

	public static function postProcessing(Dwoo_Compiler $compiler, array $params, $prepend='', $append='')
	{
		return $params['postOutput'];
	}
}
