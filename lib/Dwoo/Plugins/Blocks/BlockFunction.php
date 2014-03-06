<?php
namespace Dwoo\Plugins\Blocks;

use Dwoo\Block\Plugin;
use Dwoo\Exception\CompilationException;
use Dwoo\Compiler;
use Dwoo\ICompilable\Block;

/**
 * Defines a function (sub-template) that can then be called (even recursively) with the defined arguments
 * <pre>
 *  * name : template name
 *  * rest : list of arguments and optional default values
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2014-03-06
 * @package    Dwoo
 */
class BlockFunction extends Plugin implements Block {

	public function begin($name, array $rest = array()) {
	}

	public static function preProcessing(Compiler $compiler, array $params, $prepend, $append, $type) {
		$params       = $compiler->getCompiledParams($params);
		$parsedParams = array();
		if (!isset($params['*'])) {
			$params['*'] = array();
		}
		foreach ($params['*'] as $param => $defValue) {
			if (is_numeric($param)) {
				$param    = $defValue;
				$defValue = null;
			}
			$param = trim($param, '\'"');
			if (!preg_match('#^[a-z0-9_]+$#i', $param)) {
				throw new CompilationException($compiler, 'Function : parameter names must contain only A-Z, 0-9 or _');
			}
			$parsedParams[$param] = $defValue;
		}
		$params['name'] = substr($params['name'], 1, -1);
		$params['*']    = $parsedParams;
		$params['uuid'] = uniqid();
		$compiler->addTemplatePlugin($params['name'], $parsedParams, $params['uuid']);
		$currentBlock           =& $compiler->getCurrentBlock();
		$currentBlock['params'] = $params;

		return '';
	}

	public static function postProcessing(Compiler $compiler, array $params, $prepend, $append, $content) {
		$paramstr = '\Dwoo\Core $core';
		$init     = 'static $_callCnt = 0;' . "\n" . '$core->scope[\' ' . $params['uuid'] . '\'.$_callCnt] = array();' . "\n" . '$_scope = $core->setScope(array(\' ' . $params['uuid'] . '\'.($_callCnt++)));' . "\n";
		$cleanup  = '/* -- template end output */ $core->setScope($_scope, true);';
		foreach ($params['*'] as $param => $defValue) {
			if ($defValue === null) {
				$paramstr .= ', $' . $param;
			}
			else {
				$paramstr .= ', $' . $param . ' = ' . $defValue;
			}
			$init .= '$core->scope[\'' . $param . '\'] = $' . $param . ";\n";
		}
		$init .= '/* -- template start output */';

		$funcName = $params['name']. $params['uuid'];

		$search      = array(
			'$this->charset', '$this->', '$this,',
		);
		$replacement = array(
			'$core->getCharset()', '$core->', '$core,',
		);
		$content     = str_replace($search, $replacement, $content);

		$body = '$' . $funcName . ' = function(' . $paramstr . ') use(&$' . $funcName . ') {' . "\n$init" . Compiler::PHP_CLOSE . $prepend . $content . $append . Compiler::PHP_OPEN . $cleanup . "\n};";
		$compiler->addTemplatePlugin($params['name'], $params['*'], $params['uuid'], $body);
	}
}