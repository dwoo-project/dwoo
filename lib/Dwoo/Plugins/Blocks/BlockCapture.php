<?php
namespace Dwoo\Plugins\Blocks;
use Dwoo\Block\Plugin;
use Dwoo\Compiler;
use Dwoo\ICompilable\Block;

/**
 * Captures all the output within this block and saves it into {$.capture.default} by default,
 * or {$.capture.name} if you provide another name.
 * <pre>
 *  * name : capture name, used to read the value afterwards
 *  * assign : if set, the value is also saved in the given variable
 *  * cat : if true, the value is appended to the previous one (if any) instead of overwriting it
 * </pre>
 * If the cat parameter is true, the content
 * will be appended to the existing content
 * Example :
 * <code>
 * {capture "foo"}
 *   Anything in here won't show, it will be saved for later use..
 * {/capture}
 * Output was : {$.capture.foo}
 * </code>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-02
 * @package    Dwoo
 */
class BlockCapture extends Plugin implements Block {

	public function begin($name = 'default', $assign = null, $cat = false, $trim = false) {
	}

	public static function preProcessing(Compiler $compiler, array $params, $prepend, $append, $type) {
		return Compiler::PHP_OPEN . $prepend . 'ob_start();' . $append . Compiler::PHP_CLOSE;
	}

	public static function postProcessing(Compiler $compiler, array $params, $prepend, $append, $content) {
		$params = $compiler->getCompiledParams($params);

		$out = $content . Compiler::PHP_OPEN . $prepend . "\n" . '$tmp = ob_get_clean();';
		if ($params['trim'] !== 'false' && $params['trim'] !== 0) {
			$out .= "\n" . '$tmp = trim($tmp);';
		}
		if ($params['cat'] === 'true' || $params['cat'] === 1) {
			$out .= "\n" . '$tmp = $this->readVar(\'dwoo.capture.\'.' . $params['name'] . ') . $tmp;';
		}
		if ($params['assign'] !== 'null') {
			$out .= "\n" . '$this->scope[' . $params['assign'] . '] = $tmp;';
		}

		return $out . "\n" . '$this->globals[\'capture\'][' . $params['name'] . '] = $tmp;' . $append . Compiler::PHP_CLOSE;
	}
}