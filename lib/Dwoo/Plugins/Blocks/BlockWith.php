<?php
namespace Dwoo\Plugins\Blocks;
use Dwoo\Block\Plugin;
use Dwoo\Compiler;
use Dwoo\ICompilable\Block;
use Dwoo\IElseable;

/**
 * Moves the scope down into the provided variable, allowing you to use shorter
 * variable names if you repeatedly access values into a single array
 * The with block won't display anything at all if the provided scope is empty,
 * so in effect it acts as {if $var}*content*{/if}
 * <pre>
 *  * var : the variable name to move into
 * </pre>
 * Example :
 * instead of the following :
 * <code>
 * {if $long.boring.prefix}
 *   {$long.boring.prefix.val} - {$long.boring.prefix.secondVal} - {$long.boring.prefix.thirdVal}
 * {/if}
 * </code>
 * you can use :
 * <code>
 * {with $long.boring.prefix}
 *   {$val} - {$secondVal} - {$thirdVal}
 * {/with}
 * </code>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-03
 * @package    Dwoo
 */
class BlockWith extends Plugin implements Block, IElseable {

	protected static $cnt = 0;

	public function begin($var) {

	}

	public static function preProcessing(Compiler $compiler, array $params, $prepend, $append, $type) {
		return '';
	}

	public static function postProcessing(Compiler $compiler, array $params, $prepend, $append, $content) {
		$rparams = $compiler->getRealParams($params);
		$cparams = $compiler->getCompiledParams($params);

		$compiler->setScope($rparams['var']);


		$pre = Compiler::PHP_OPEN . 'if (' . $cparams['var'] . ')' . "\n{\n" . '$_with' . (self::$cnt) . ' = $this->setScope("' . $rparams['var'] . '");' . "\n/* -- start with output */\n" . Compiler::PHP_CLOSE;

		$post = Compiler::PHP_OPEN . "\n/* -- end with output */\n" . '$this->setScope($_with' . (self::$cnt ++) . ', true);' . "\n}\n" . Compiler::PHP_CLOSE;

		if (isset($params['hasElse'])) {
			$post .= $params['hasElse'];
		}

		return $pre . $content . $post;
	}
}