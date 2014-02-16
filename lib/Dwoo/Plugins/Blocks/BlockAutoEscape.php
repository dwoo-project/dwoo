<?php
namespace Dwoo\Plugins\Blocks;
use Dwoo\Block\Plugin;
use Dwoo\Exception\CompilationException;
use Dwoo\Compiler;
use Dwoo\ICompilable\Block;

/**
 * Overrides the compiler auto-escape setting within the block
 * <pre>
 *  * enabled : if set to "on", "enable", true or 1 then the compiler autoescaping is enabled inside this block. set to "off", "disable", false or 0 to disable it
 * </pre>
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
class BlockAutoEscape extends Plugin implements Block {

	protected static $stack = array();

	public function begin($enabled) {

	}

	public static function preProcessing(Compiler $compiler, array $params, $prepend, $append, $type) {
		$params = $compiler->getCompiledParams($params);
		switch (strtolower(trim((string)$params['enabled'], '"\''))) {

			case 'on':
			case 'true':
			case 'enabled':
			case 'enable':
			case '1':
				$enable = true;
				break;
			case 'off':
			case 'false':
			case 'disabled':
			case 'disable':
			case '0':
				$enable = false;
				break;
			default:
				throw new CompilationException($compiler, 'Auto_Escape : Invalid parameter (' . $params['enabled'] . '), valid parameters are "enable"/true or "disable"/false');

		}

		self::$stack[] = $compiler->getAutoEscape();
		$compiler->setAutoEscape($enable);

		return '';
	}

	public static function postProcessing(Compiler $compiler, array $params, $prepend, $append, $content) {
		$compiler->setAutoEscape(array_pop(self::$stack));

		return $content;
	}
}