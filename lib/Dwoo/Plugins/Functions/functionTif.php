<?php
namespace Dwoo\Plugins\Functions;
use Dwoo\Exception\CompilationException;
use Dwoo\Compiler;
use Dwoo\Plugins\Blocks\BlockIf;

/**
 * Ternary if operation
 *
 * It evaluates the first argument and returns the second if it's true, or the third if it's false
 * <pre>
 *  * rest : you can not use named parameters to call this, use it either with three arguments in the correct order (expression, true result, false result) or write it as in php (expression ? true result : false result)
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @copyright  Copyright (c) 2008, Jordi Boggiano
 * @license    http://dwoo.org/LICENSE   Modified BSD License
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-06
 * @package    Dwoo
 */
function functionTifCompile(Compiler $compiler, array $rest, array $tokens) {
	// load if plugin
	if (!class_exists('\Dwoo\Plugins\Blocks\BlockIf')) {
		try {
			$compiler->getDwoo()->getLoader()->loadPlugin('if');
		}
		catch (\Exception $e) {
			throw new CompilationException($compiler, 'Tif: the if plugin is required to use Tif');
		}
	}

	if (count($rest) == 1) {
		return $rest[0];
	}

	// fetch false result and remove the ":" if it was present
	$falseResult = array_pop($rest);

	if (trim(end($rest), '"\'') === ':') {
		// remove the ':' if present
		array_pop($rest);
	}
	elseif (trim(end($rest), '"\'') === '?' || count($rest) === 1) {
		if ($falseResult === '?' || $falseResult === ':') {
			throw new CompilationException($compiler, 'Tif: incomplete tif statement, value missing after ' . $falseResult);
		}
		// there was in fact no false result provided, so we move it to be the true result instead
		$trueResult  = $falseResult;
		$falseResult = "''";
	}

	// fetch true result if needed
	if (! isset($trueResult)) {
		$trueResult = array_pop($rest);
		// no true result provided so we use the expression arg
		if ($trueResult === '?') {
			$trueResult = true;
		}
	}

	// remove the '?' if present
	if (trim(end($rest), '"\'') === '?') {
		array_pop($rest);
	}

	// check params were correctly provided
	if (empty($rest) || $trueResult === null || $falseResult === null) {
		throw new CompilationException($compiler, 'Tif: you must provide three parameters serving as <expression> ? <true value> : <false value>');
	}

	// parse condition
	$condition = BlockIf::replaceKeywords($rest, $tokens, $compiler);

	return '((' . implode(' ', $condition) . ') ? ' . ($trueResult === true ? implode(' ', $condition) : $trueResult) . ' : ' . $falseResult . ')';
}
