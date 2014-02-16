<?php
namespace Dwoo\Plugins\Blocks;
use Dwoo\Block\Plugin;
use Dwoo\Exception\CompilationException;
use Dwoo\Compiler;
use Dwoo\ICompilable\Block;
use Dwoo\IElseable;

/**
 * Conditional block, the syntax is very similar to the php one, allowing () || && and
 * other php operators. Additional operators and their equivalent php syntax are as follow :
 * eq -> ==
 * neq or ne -> !=
 * gte or ge -> >=
 * lte or le -> <=
 * gt -> >
 * lt -> <
 * mod -> %
 * not -> !
 * X is [not] div by Y -> (X % Y) == 0
 * X is [not] even [by Y] -> (X % 2) == 0 or ((X/Y) % 2) == 0
 * X is [not] odd [by Y] -> (X % 2) != 0 or ((X/Y) % 2) != 0
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    1.0.0
 * @date       2008-10-23
 * @package    Dwoo
 */
class BlockIf extends Plugin implements Block, IElseable {

	public function begin(array $rest) {
	}

	public static function replaceKeywords(array $params, array $tokens, Compiler $compiler) {
		$p = array();

		reset($params);
		while (list($k, $v) = each($params)) {
			$v = (string)$v;
			if (substr($v, 0, 1) === '"' || substr($v, 0, 1) === '\'') {
				$vmod = strtolower(substr($v, 1, -1));
			}
			else {
				$vmod = strtolower($v);
			}
			switch ($vmod) {

				case 'and':
					if ($tokens[$k] === Compiler::T_UNQUOTED_STRING) {
						$p[] = '&&';
					}
					else {
						$p[] = $v;
					}
					break;
				case 'or':
					if ($tokens[$k] === Compiler::T_UNQUOTED_STRING) {
						$p[] = '||';
					}
					else {
						$p[] = $v;
					}
					break;
				case 'eq':
					if ($tokens[$k] === Compiler::T_UNQUOTED_STRING) {
						$p[] = '==';
					}
					else {
						$p[] = $v;
					}
					break;
				case 'ne':
				case 'neq':
					if ($tokens[$k] === Compiler::T_UNQUOTED_STRING) {
						$p[] = '!=';
					}
					else {
						$p[] = $v;
					}
					break;
				case 'gte':
				case 'ge':
					if ($tokens[$k] === Compiler::T_UNQUOTED_STRING) {
						$p[] = '>=';
					}
					else {
						$p[] = $v;
					}
					break;
				case 'lte':
				case 'le':
					if ($tokens[$k] === Compiler::T_UNQUOTED_STRING) {
						$p[] = '<=';
					}
					else {
						$p[] = $v;
					}
					break;
				case 'gt':
					if ($tokens[$k] === Compiler::T_UNQUOTED_STRING) {
						$p[] = '>';
					}
					else {
						$p[] = $v;
					}
					break;
				case 'lt':
					if ($tokens[$k] === Compiler::T_UNQUOTED_STRING) {
						$p[] = '<';
					}
					else {
						$p[] = $v;
					}
					break;
				case 'mod':
					if ($tokens[$k] === Compiler::T_UNQUOTED_STRING) {
						$p[] = '%';
					}
					else {
						$p[] = $v;
					}
					break;
				case 'not':
					if ($tokens[$k] === Compiler::T_UNQUOTED_STRING) {
						$p[] = '!';
					}
					else {
						$p[] = $v;
					}
					break;
				case '<>':
					$p[] = '!=';
					break;
				case '==':
				case '!=':
				case '>=':
				case '<=':
				case '>':
				case '<':
				case '===':
				case '!==':
				case '%':
				case '!':
					$p[] = $vmod;
					break;
				case 'is':
					if ($tokens[$k] !== Compiler::T_UNQUOTED_STRING) {
						$p[] = $v;
						break;
					}
					if (isset($params[$k + 1]) && strtolower(trim($params[$k + 1], '"\'')) === 'not' && $tokens[$k + 1] === Compiler::T_UNQUOTED_STRING) {
						$negate = true;
						next($params);
					}
					else {
						$negate = false;
					}
					$ptr = 1 + (int)$negate;
					if ($tokens[$k + $ptr] !== Compiler::T_UNQUOTED_STRING) {
						break;
					}
					if (!isset($params[$k + $ptr])) {
						$params[$k + $ptr] = '';
					}
					else {
						$params[$k + $ptr] = trim($params[$k + $ptr], '"\'');
					}
					switch ($params[$k + $ptr]) {

						case 'div':
							if (isset($params[$k + $ptr + 1]) && strtolower(trim($params[$k + $ptr + 1], '"\'')) === 'by') {
								$p[] = ' % ' . $params[$k + $ptr + 2] . ' ' . ($negate ? '!' : '=') . '== 0';
								next($params);
								next($params);
								next($params);
							}
							else {
								throw new CompilationException($compiler, 'If : Syntax error : syntax should be "if $a is [not] div by $b", found ' . $params[$k - 1] . ' is ' . ($negate ? 'not ' : '') . 'div ' . $params[$k + $ptr + 1] . ' ' . $params[$k + $ptr + 2]);
							}
							break;
						case 'even':
							$a = array_pop($p);
							if (isset($params[$k + $ptr + 1]) && strtolower(trim($params[$k + $ptr + 1], '"\'')) === 'by') {
								$b   = $params[$k + $ptr + 2];
								$p[] = '(' . $a . ' / ' . $b . ') % 2 ' . ($negate ? '!' : '=') . '== 0';
								next($params);
								next($params);
							}
							else {
								$p[] = $a . ' % 2 ' . ($negate ? '!' : '=') . '== 0';
							}
							next($params);
							break;
						case 'odd':
							$a = array_pop($p);
							if (isset($params[$k + $ptr + 1]) && strtolower(trim($params[$k + $ptr + 1], '"\'')) === 'by') {
								$b   = $params[$k + $ptr + 2];
								$p[] = '(' . $a . ' / ' . $b . ') % 2 ' . ($negate ? '=' : '!') . '== 0';
								next($params);
								next($params);
							}
							else {
								$p[] = $a . ' % 2 ' . ($negate ? '=' : '!') . '== 0';
							}
							next($params);
							break;
						default:
							throw new CompilationException($compiler, 'If : Syntax error : syntax should be "if $a is [not] (div|even|odd) [by $b]", found ' . $params[$k - 1] . ' is ' . $params[$k + $ptr + 1]);

					}
					break;
				default:
					$p[] = $v;

			}
		}

		return $p;
	}

	public static function preProcessing(Compiler $compiler, array $params, $prepend, $append, $type) {
		return '';
	}

	public static function postProcessing(Compiler $compiler, array $params, $prepend, $append, $content) {
		$tokens = $compiler->getParamTokens($params);
		$params = $compiler->getCompiledParams($params);
		$pre    = Compiler::PHP_OPEN . 'if (' . implode(' ', self::replaceKeywords($params['*'], $tokens['*'], $compiler)) . ") {\n" . Compiler::PHP_CLOSE;

		$post = Compiler::PHP_OPEN . "\n}" . Compiler::PHP_CLOSE;

		if (isset($params['hasElse'])) {
			$post .= $params['hasElse'];
		}

		return $pre . $content . $post;
	}
}