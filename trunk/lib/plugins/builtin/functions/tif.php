<?php

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
 * This file is released under the LGPL
 * "GNU Lesser General Public License"
 * More information can be found here:
 * {@link http://www.gnu.org/copyleft/lesser.html}
 *
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @copyright  Copyright (c) 2008, Jordi Boggiano
 * @license    http://www.gnu.org/copyleft/lesser.html  GNU Lesser General Public License
 * @link       http://dwoo.org/
 * @version    0.9.1
 * @date       2008-05-30
 * @package    Dwoo
 */
function Dwoo_Plugin_tif_compile(Dwoo_Compiler $compiler, array $rest)
{
	// load if plugin
	if (!class_exists('Dwoo_Plugin_if', false)) {
		try {
			Dwoo_Loader::loadPlugin('if');
		} catch (Exception $e) {
			throw new Dwoo_Compilation_Exception($compiler, 'Tif: the if plugin is required to use Tif');
		}
	}

	// fetch false result and remove the ":" if it was present
	$falseResult = array_pop($rest);

	if (trim(end($rest), '"\'') === ':') {
		// remove the ':' if present
		array_pop($rest);
	} elseif (trim(end($rest), '"\'') === '?' || count($rest) === 1) {
		// there was in fact no false result provided, so we move it to be the true result instead
		$trueResult = $falseResult;
		$falseResult = "''";
	}

	// fetch true result if needed
	if (!isset($trueResult)) {
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
	if (empty($rest) || empty($trueResult) || empty($falseResult)) {
		throw new Dwoo_Compilation_Exception($compiler, 'Tif: you must provide three parameters serving as <expression> ? <true value> : <false value>');
	}

	// parse condition
	$condition = Dwoo_Plugin_if::replaceKeywords($rest, $compiler);

    return '(('.implode(' ', $condition).') ? '.($trueResult===true ? implode(' ', $condition) : $trueResult).' : '.$falseResult.')';
}
