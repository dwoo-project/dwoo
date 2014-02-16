<?php
namespace Dwoo\Plugins\Blocks;
use Dwoo\Block\Plugin;
use Dwoo\Compiler;
use Dwoo\ICompilable\Block;

/**
 * This plugin serves as a {else} block specifically for the {with} plugin.
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-06
 * @package    Dwoo
 */
class BlockWithelse extends Plugin implements Block {
	public function init() {
	}

	public static function preProcessing(Compiler $compiler, array $params, $prepend, $append, $type) {
		$with =& $compiler->findBlock('with', true);

		$params['initialized'] = true;
		$compiler->injectBlock($type, $params);

		return '';
	}

	public static function postProcessing(Compiler $compiler, array $params, $prepend, $append, $content) {
		if (!isset($params['initialized'])) {
			return '';
		}

		$block                      =& $compiler->getCurrentBlock();
		$block['params']['hasElse'] = Compiler::PHP_OPEN . "else {\n" . Compiler::PHP_CLOSE . $content . Compiler::PHP_OPEN . "\n}" . Compiler::PHP_CLOSE;

		return '';
	}
}