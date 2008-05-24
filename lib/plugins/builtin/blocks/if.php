<?php

/**
 * Conditional block, the syntax is very similar to the php one, allowing () || && and 
 * other php operators. Additional operators and their equivalent php syntax are as follow :
 * 
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
 * @version    0.9.0
 * @date       2008-05-10
 * @package    Dwoo
 */
class Dwoo_Plugin_if extends Dwoo_Block_Plugin implements Dwoo_ICompilable_Block
{
	public function init(array $rest) {}

	public static function replaceKeywords($params, Dwoo_Compiler $compiler)
	{
		$params = $compiler->getCompiledParams($params);

		$p = array();

		$params = $params['*'];

		while(list($k,$v) = each($params))
		{
			switch($v)
			{
				case '"<<"':
				case '">>"':
				case '"&&"':
				case '"||"':
				case '"|"':
				case '"^"':
				case '"&"':
				case '"~"':
				case '"("':
				case '")"':
				case '","':
				case '"+"':
				case '"-"':
				case '"*"':
				case '"/"':
					$p[] = trim($v, '"');
					break;
				case '"=="':
				case '"eq"':
					$p[] = '==';
					break;
				case '"<>"':
				case '"!="':
				case '"ne"':
				case '"neq"':
					$p[] = '!=';
					break;
				case '">="':
				case '"gte"':
				case '"ge"':
					$p[] = '>=';
					break;
				case '"<="':
				case '"lte"':
				case '"le"':
					$p[] = '<=';
					break;
				case '">"':
				case '"gt"':
					$p[] = '>';
					break;
				case '"<"':
				case '"lt"':
					$p[] = '<';
					break;
				case '"==="':
					$p[] = '===';
					break;
				case '"!=="':
					$p[] = '!==';
					break;
				case '"is"':
					if($params[$k+1] === '"not"')
					{
						$negate = true;
						next($params);
					}
					else
						$negate = false;
					$ptr = 1+(int)$negate;
					switch($params[$k+$ptr])
					{
						case '"div"':
							if(isset($params[$k+$ptr+1]) && $params[$k+$ptr+1] === '"by"')
							{
								$p[] = ' % '.$params[$k+$ptr+2].' '.($negate?'!':'=').'== 0';
								next($params);
								next($params);
								next($params);
							}
							else
								throw new Dwoo_Compilation_Exception('If : Syntax error : syntax should be "if $a is [not] div by $b", found '.$params[$k-1].' is '.($negate?'not ':'').'div '.$params[$k+$ptr+1].' '.$params[$k+$ptr+2]);
							break;
						case '"even"':
							$a = array_pop($p);
							if(isset($params[$k+$ptr+1]) && $params[$k+$ptr+1] === '"by"')
							{
								$b = $params[$k+$ptr+2];
								$p[] = '('.$a .' / '.$b.') % 2 '.($negate?'!':'=').'== 0';
								next($params);
								next($params);
							}
							else
							{
								$p[] = $a.' % 2 '.($negate?'!':'=').'== 0';
							}
							next($params);
							break;
						case '"odd"':
							$a = array_pop($p);
							if(isset($params[$k+$ptr+1]) && $params[$k+$ptr+1] === '"by"')
							{
								$b = $params[$k+$ptr+2];
								$p[] = '('.$a .' / '.$b.') % 2 '.($negate?'=':'!').'== 0';
								next($params);
								next($params);
							}
							else
							{
								$p[] = $a.' % 2 '.($negate?'=':'!').'== 0';
							}
							next($params);
							break;
						default:
							throw new Dwoo_Compilation_Exception('If : Syntax error : syntax should be "if $a is [not] (div|even|odd) [by $b]", found '.$params[$k-1].' is '.$params[$k+$ptr+1]);
					}
					break;
				case '"%"':
				case '"mod"':
					$p[] = '%';
					break;
				case '"!"':
				case '"not"':
					$p[] = '!';
					break;
				default:
					$p[] = $v;
			}
		}

		return $p;
	}

	public static function preProcessing(Dwoo_Compiler $compiler, array $params, $prepend='', $append='', $type)
	{
		$currentBlock =& $compiler->getCurrentBlock();
		$currentBlock['params']['postOutput'] = Dwoo_Compiler::PHP_OPEN."\n}".Dwoo_Compiler::PHP_CLOSE;

		return Dwoo_Compiler::PHP_OPEN.'if('.implode(' ', self::replaceKeywords($params, $compiler)).") {\n".Dwoo_Compiler::PHP_CLOSE;
	}

	public static function postProcessing(Dwoo_Compiler $compiler, array $params, $prepend='', $append='')
	{
		return $params['postOutput'];
	}
}
