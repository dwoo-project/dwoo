<?php

/**
 * Acts as a php elseif block, allowing you to add one more condition
 * if the previous one(s) didn't match. See the {if} plugin for syntax details
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
 * @version    0.9.1
 * @date       2008-05-30
 * @package    Dwoo
 */
class Dwoo_Plugin_elseif extends Dwoo_Plugin_if implements Dwoo_ICompilable_Block
{
	public static $types = array
	(
		'if' => true, 'elseif' => true
	);

	public function init(array $rest)
	{
	}

	public static function preProcessing(Dwoo_Compiler $compiler, array $params, $prepend, $append, $type)
	{
		// delete this block
		$compiler->removeTopBlock();
		// fetch the top of the stack
		$parent =& $compiler->getCurrentBlock();
		// loop until we get an elseif or if block
		$out = '';
		while (!isset(self::$types[$parent['type']])) {
			$out .= $compiler->removeTopBlock();
			$parent =& $compiler->getCurrentBlock();
		}
		//
		$out .= $parent['params']['postOutput'];
		$parent['params']['postOutput'] = '';

		// reinsert this block
		$compiler->injectBlock($type, $params, 1);

		// generate post-output
		$currentBlock =& $compiler->getCurrentBlock();
		$currentBlock['params']['postOutput'] = Dwoo_Compiler::PHP_OPEN."\n}".Dwoo_Compiler::PHP_CLOSE;

		if ($out === '') {
			$out = Dwoo_Compiler::PHP_OPEN."\n}";
		} else {
			$out = substr($out, 0, -strlen(Dwoo_Compiler::PHP_CLOSE));
		}

		$params = $compiler->getCompiledParams($params);

		return $out . " elseif (".implode(' ', self::replaceKeywords($params['*'], $compiler)).") {\n" . Dwoo_Compiler::PHP_CLOSE;
	}

	public static function postProcessing(Dwoo_Compiler $compiler, array $params, $prepend, $append, $content)
	{
		if (isset($params['postOutput'])) {
			return $content . $params['postOutput'];
		} else {
			return $content;
		}
	}
}
