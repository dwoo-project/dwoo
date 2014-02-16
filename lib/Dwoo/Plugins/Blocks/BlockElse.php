<?php
namespace Dwoo\Plugins\Blocks;
use Dwoo\Block\Plugin;
use Dwoo\Exception\CompilationException;
use Dwoo\Compiler;
use Dwoo\ICompilable\Block;

/**
 * Generic else block, it supports all builtin optional-display blocks which are if/for/foreach/loop/with
 * If any of those block contains an else statement, the content between {else} and {/block} (you do not
 * need to close the else block) will be shown if the block's condition has no been met
 * Example :
 * <code>
 * {foreach $array val}
 *   $array is not empty so we display it's values : {$val}
 * {else}
 *   if this shows, it means that $array is empty or doesn't exist.
 * {/foreach}
 * </code>
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
class BlockElse extends Plugin implements Block {

	public function begin() {

	}

	public static function preProcessing(Compiler $compiler, array $params, $prepend, $append, $type) {
		$preContent = '';
		while (true) {
			$preContent .= $compiler->removeTopBlock();
			$block =& $compiler->getCurrentBlock();

			if (! $block) {
				throw new CompilationException($compiler, 'An else block was found but it was not preceded by an if or other else-able construct');
			}
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

		$block                      =& $compiler->getCurrentBlock();
		$block['params']['hasElse'] = Compiler::PHP_OPEN . "else {\n" . Compiler::PHP_CLOSE . $content . Compiler::PHP_OPEN . "\n}" . Compiler::PHP_CLOSE;

		return '';
	}
}