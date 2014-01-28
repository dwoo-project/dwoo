<?php
namespace Dwoo\Plugins\Blocks;
use Dwoo\Block\Plugin;
use Dwoo\Compiler;
use Dwoo\ICompilable\Block;

/**
 * Captures all the output within this block and saves it static into a variable
 * multiple captures to the same name will get appended.
 * <pre>
 *  * name : capture name, used to read the value afterwards
 * </pre>
 *
 * Used with the output_script function
 * Example :
 *
 * <code>
 * {script_capture "myscript"}
 *   alert('hello world');
 * {/script_capture}
 * {output_script myscript}
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
 * @author     Harro van der Klauw <mroximoron@gmail.com>
 * @copyright  Copyright (c) 2008, Harro van der Klauw
 * @license    http://www.gnu.org/copyleft/lesser.html  GNU Lesser General Public License
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-09
 * @package    Dwoo
 */
class BlockScriptCapture extends Plugin implements Block {
	protected static $scripts = array();

	public function begin($name = 'head') {

	}

	public static function preProcessing(Compiler $compiler, array $params, $prepend, $append, $type) {
		return Compiler::PHP_OPEN . $prepend . 'ob_start();' . $append . Compiler::PHP_CLOSE;
	}

	public static function postProcessing(Compiler $compiler, array $params, $prepend, $append, $content) {
		$params = $compiler->getCompiledParams($params);
		$out    = $content . Compiler::PHP_OPEN . $prepend . "\n" . '$tmp = ob_get_clean();';

		return $out . "\n" . 'if (!class_exists(\'\Dwoo\Plugins\Blocks\BlockScriptCapture\')) { $this->getLoader()->loadPlugin(\'script_capture\'); }' . "\n" . '\Dwoo\Plugins\Blocks\BlockScriptCapture::addScript(' . $params['name'] . ',$tmp);' . $append . Compiler::PHP_CLOSE;
	}

	public static function addScript($name, $script) {
		self::$scripts[$name][] = $script;
	}

	public static function getScripts($name) {
		if (! isset(self::$scripts[$name])) {
			return '';
		}

		return implode("\n", self::$scripts[$name]);

	}
}
