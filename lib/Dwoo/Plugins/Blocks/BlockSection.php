<?php
namespace Dwoo\Plugins\Blocks;
use Dwoo\Block\Plugin;
use Dwoo\Compiler;
use Dwoo\ICompilable\Block;
use Dwoo\IElseable;

/**
 * Compatibility plugin for smarty templates, do not use otherwise, this is deprecated.
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
class BlockSection extends Plugin implements Block, IElseable {
	public static $cnt = 0;

	public function init($name, $loop, $start = null, $step = null, $max = null, $show = true) {
	}

	public static function preProcessing(Compiler $compiler, array $params, $prepend, $append, $type) {
		return '';
	}

	public static function postProcessing(Compiler $compiler, array $params, $prepend, $append, $content) {
		$output = Compiler::PHP_OPEN;
		$params = $compiler->getCompiledParams($params);

		// assigns params
		$loop  = $params['loop'];
		$start = $params['start'];
		$max   = $params['max'];
		$name  = $params['name'];
		$step  = $params['step'];
		$show  = $params['show'];

		// gets unique id
		$cnt = self::$cnt++;

		$output .= '$this->globals[\'section\'][' . $name . '] = array();' . "\n" . '$_section' . $cnt . ' =& $this->globals[\'section\'][' . $name . '];' . "\n";

		if ($loop !== 'null') {
			$output .= '$_section' . $cnt . '[\'loop\'] = is_array($tmp = ' . $loop . ') ? count($tmp) : max(0, (int) $tmp);' . "\n";
		}
		else {
			$output .= '$_section' . $cnt . '[\'loop\'] = 1;' . "\n";
		}

		if ($show !== 'null') {
			$output .= '$_section' . $cnt . '[\'show\'] = ' . $show . ";\n";
		}
		else {
			$output .= '$_section' . $cnt . '[\'show\'] = true;' . "\n";
		}

		if ($name !== 'null') {
			$output .= '$_section' . $cnt . '[\'name\'] = ' . $name . ";\n";
		}
		else {
			$output .= '$_section' . $cnt . '[\'name\'] = true;' . "\n";
		}

		if ($max !== 'null') {
			$output .= '$_section' . $cnt . '[\'max\'] = (int)' . $max . ";\n" . 'if($_section' . $cnt . '[\'max\'] < 0) { $_section' . $cnt . '[\'max\'] = $_section' . $cnt . '[\'loop\']; }' . "\n";
		}
		else {
			$output .= '$_section' . $cnt . '[\'max\'] = $_section' . $cnt . '[\'loop\'];' . "\n";
		}

		if ($step !== 'null') {
			$output .= '$_section' . $cnt . '[\'step\'] = (int)' . $step . ' == 0 ? 1 : (int) ' . $step . ";\n";
		}
		else {
			$output .= '$_section' . $cnt . '[\'step\'] = 1;' . "\n";
		}

		if ($start !== 'null') {
			$output .= '$_section' . $cnt . '[\'start\'] = (int)' . $start . ";\n";
		}
		else {
			$output .= '$_section' . $cnt . '[\'start\'] = $_section' . $cnt . '[\'step\'] > 0 ? 0 : $_section' . $cnt . '[\'loop\'] - 1;' . "\n" . 'if ($_section' . $cnt . '[\'start\'] < 0) { $_section' . $cnt . '[\'start\'] = max($_section' . $cnt . '[\'step\'] > 0 ? 0 : -1, $_section' . $cnt . '[\'loop\'] + $_section' . $cnt . '[\'start\']); } ' . "\n" . 'else { $_section' . $cnt . '[\'start\'] = min($_section' . $cnt . '[\'start\'], $_section' . $cnt . '[\'step\'] > 0 ? $_section' . $cnt . '[\'loop\'] : $_section' . $cnt . '[\'loop\'] -1); }' . "\n";
		}

		$output .= 'if ($_section' . $cnt . '[\'show\']) {' . "\n";
		if ($start === 'null' && $step === 'null' && $max === 'null') {
			$output .= '	$_section' . $cnt . '[\'total\'] = $_section' . $cnt . '[\'loop\'];' . "\n";
		}
		else {
			$output .= '	$_section' . $cnt . '[\'total\'] = min(ceil(($_section' . $cnt . '[\'step\'] > 0 ? $_section' . $cnt . '[\'loop\'] - $_section' . $cnt . '[\'start\'] : $_section' . $cnt . '[\'start\'] + 1) / abs($_section' . $cnt . '[\'step\'])), $_section' . $cnt . '[\'max\']);' . "\n";
		}
		$output .= '	if ($_section' . $cnt . '[\'total\'] == 0) {' . "\n" . '		$_section' . $cnt . '[\'show\'] = false;' . "\n" . '	}' . "\n" . '} else {' . "\n" . '	$_section' . $cnt . '[\'total\'] = 0;' . "\n}\n";
		$output .= 'if ($_section' . $cnt . '[\'show\']) {' . "\n";
		$output .= "\t" . 'for ($this->scope[' . $name . '] = $_section' . $cnt . '[\'start\'], $_section' . $cnt . '[\'iteration\'] = 1; ' . '$_section' . $cnt . '[\'iteration\'] <= $_section' . $cnt . '[\'total\']; ' . '$this->scope[' . $name . '] += $_section' . $cnt . '[\'step\'], $_section' . $cnt . '[\'iteration\']++) {' . "\n";
		$output .= "\t\t" . '$_section' . $cnt . '[\'rownum\'] = $_section' . $cnt . '[\'iteration\'];' . "\n";
		$output .= "\t\t" . '$_section' . $cnt . '[\'index_prev\'] = $this->scope[' . $name . '] - $_section' . $cnt . '[\'step\'];' . "\n";
		$output .= "\t\t" . '$_section' . $cnt . '[\'index_next\'] = $this->scope[' . $name . '] + $_section' . $cnt . '[\'step\'];' . "\n";
		$output .= "\t\t" . '$_section' . $cnt . '[\'first\']      = ($_section' . $cnt . '[\'iteration\'] == 1);' . "\n";
		$output .= "\t\t" . '$_section' . $cnt . '[\'last\']       = ($_section' . $cnt . '[\'iteration\'] == $_section' . $cnt . '[\'total\']);' . "\n";

		$output .= Compiler::PHP_CLOSE . $content . Compiler::PHP_OPEN;

		$output .= "\n\t}\n} " . Compiler::PHP_CLOSE;

		if (isset($params['hasElse'])) {
			$output .= $params['hasElse'];
		}

		return $output;
	}
}
