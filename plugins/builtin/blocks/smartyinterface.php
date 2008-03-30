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
class DwooPlugin_smartyinterface extends DwooPlugin
{
	public function init($__funcname, $__functype, array $rest=array()) {}

	public static function preProcessing(DwooCompiler $compiler, array $params, $prepend='', $append='', $type)
	{
		$params = $compiler->getCompiledParams($params);
		$func = $params['__funcname'];
		$pluginType = $params['__functype'];
		$params = $params['*'];

		if($pluginType & Dwoo::CUSTOM_PLUGIN)
		{
			$callback = $compiler->customPlugins[$func]['callback'];
			if(is_array($callback))
			{
				if(is_object($callback[0]))
					$callback = '$this->customPlugins[\''.$func.'\'][0]->'.$callback[1].'(';
				else
					$callback = ''.$callback[0].'::'.$callback[1].'(';
			}
			else
				$callback = $callback.'(';
		}
		else
			$callback = 'smarty_block_'.$func.'(';

		$compiler->curBlock['params']['postOut'] = DwooCompiler::PHP_OPEN.' $_block_content = ob_get_clean(); $_block_repeat=false; echo '.$callback.'$_tag_stack[count($_tag_stack)-1], $_block_content, $this, $_block_repeat); } array_pop($_tag_stack);'.DwooCompiler::PHP_CLOSE;

		return DwooCompiler::PHP_OPEN.$prepend.' if(!isset($_tag_stack)){ $_tag_stack = array(); } $_tag_stack[] = '.var_export($params,true).'; $_block_repeat=true; '.$callback.'$_tag_stack[count($_tag_stack)-1], null, $this, $_block_repeat); while ($_block_repeat) { ob_start();'.DwooCompiler::PHP_CLOSE;
	}

	public static function postProcessing(DwooCompiler $compiler, array $params, $prepend='', $append='')
	{
		return $params['postOut'];
	}
}

?>