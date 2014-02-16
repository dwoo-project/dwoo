<?php
namespace Dwoo\Plugins\Blocks;
use Dwoo\Compiler;
use Dwoo\ICompilable\Block;
use Dwoo\IElseable;

/**
 * Acts as a php elseif block, allowing you to add one more condition
 * if the previous one(s) didn't match. See the {if} plugin for syntax details
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-07
 * @package    Dwoo
 */
class BlockElseif extends BlockIf implements Block, IElseable {

	public function begin(array $rest) {

	}

	public static function preProcessing(Compiler $compiler, array $params, $prepend, $append, $type) {
		$preContent = '';
		while (true) {
			$preContent .= $compiler->removeTopBlock();
			$block      =& $compiler->getCurrentBlock();

			$reflectionClass = new \ReflectionClass($block['class']);
			if ($reflectionClass->implementsInterface('Dwoo\IElseable')) {
				break;
			}
		}

		$params['initialized'] = true;
		$compiler->injectBlock($type, $params);

		return $preContent;
	}

	public static function postProcessing(Compiler $compiler, array $params, $prepend, $append, $content) {
		if (! isset($params['initialized'])) {
			return '';
		}

		$tokens = $compiler->getParamTokens($params);
		$params = $compiler->getCompiledParams($params);

		$pre  = Compiler::PHP_OPEN . "else if (" . implode(' ', self::replaceKeywords($params['*'], $tokens['*'], $compiler)) . ") {\n" . Compiler::PHP_CLOSE;
		$post = Compiler::PHP_OPEN . "\n}" . Compiler::PHP_CLOSE;

		if (isset($params['hasElse'])) {
			$post .= $params['hasElse'];
		}

		$block                      =& $compiler->getCurrentBlock();
		$block['params']['hasElse'] = $pre . $content . $post;

		return '';
	}
}