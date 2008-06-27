<?php

/**
 * Loops over an array and moves the scope into each value, allowing for shorter loop constructs
 *
 * Note that to access the array key within a loop block, you have to use the {$_key} variable,
 * you can not specify it yourself.
 * <pre>
 *  * from : the array that you want to iterate over
 *  * name : loop name to access it's iterator variables through {$.loop.name.var} see {@link http://wiki.dwoo.org/index.php/IteratorVariables} for details
 * </pre>
 * Example :
 *
 * instead of a foreach block such as :
 *
 * <code>
 * {foreach $variable value}
 *   {$value.foo} {$value.bar}
 * {/foreach}
 * </code>
 *
 * you can do :
 *
 * <code>
 * {loop $variable}
 *   {$foo} {$bar}
 * {/loop}
 * </code>
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
class Dwoo_Plugin_loop extends Dwoo_Block_Plugin implements Dwoo_ICompilable_Block
{
	public static $cnt=0;

	public function init($from, $name='default')
	{
	}

	public static function preProcessing(Dwoo_Compiler $compiler, array $params, $prepend, $append, $type)
	{
		$params = $compiler->getCompiledParams($params);
		$tpl = $compiler->getTemplateSource(true);

		// assigns params
		$src = $params['from'];
		$name = $params['name'];

		// evaluates which global variables have to be computed
		$varName = '$dwoo.loop.'.trim($name, '"\'').'.';
		$shortVarName = '$.loop.'.trim($name, '"\'').'.';
		$usesAny = strpos($tpl, $varName) !== false || strpos($tpl, $shortVarName) !== false;
		$usesFirst = strpos($tpl, $varName.'first') !== false || strpos($tpl, $shortVarName.'first') !== false;
		$usesLast = strpos($tpl, $varName.'last') !== false || strpos($tpl, $shortVarName.'last') !== false;
		$usesIndex = $usesFirst || strpos($tpl, $varName.'index') !== false || strpos($tpl, $shortVarName.'index') !== false;
		$usesIteration = $usesLast || strpos($tpl, $varName.'iteration') !== false || strpos($tpl, $shortVarName.'iteration') !== false;
		$usesShow = strpos($tpl, $varName.'show') !== false || strpos($tpl, $shortVarName.'show') !== false;
		$usesTotal = $usesLast || strpos($tpl, $varName.'total') !== false || strpos($tpl, $shortVarName.'total') !== false;

		// gets foreach id
		$cnt = self::$cnt++;

		// builds pre processing output
		$out = Dwoo_Compiler::PHP_OPEN . "\n".'$_loop'.$cnt.'_data = '.$src.';';
		// adds foreach properties
		if ($usesAny) {
			$out .= "\n".'$this->globals["loop"]['.$name.'] = array'."\n(";
			if ($usesIndex) $out .="\n\t".'"index"		=> 0,';
			if ($usesIteration) $out .="\n\t".'"iteration"		=> 1,';
			if ($usesFirst) $out .="\n\t".'"first"		=> null,';
			if ($usesLast) $out .="\n\t".'"last"		=> null,';
			if ($usesShow) $out .="\n\t".'"show"		=> $this->isArray($_loop'.$cnt.'_data, true, true),';
			if ($usesTotal) $out .="\n\t".'"total"		=> $this->isArray($_loop'.$cnt.'_data) ? count($_loop'.$cnt.'_data) : 0,';
			$out.="\n);\n".'$_loop'.$cnt.'_glob =& $this->globals["loop"]['.$name.'];';
		}
		// checks if foreach must be looped
		$out .= "\n".'if ($this->isArray($_loop'.$cnt.'_data, true, true) === true)'."\n{";
		// iterates over keys
		$out .= "\n\t".'foreach ($_loop'.$cnt.'_data as $tmp_key => $this->scope["-loop-"])'."\n\t{";
		// updates properties
		if ($usesFirst) {
			$out .= "\n\t\t".'$_loop'.$cnt.'_glob["first"] = (string) ($_loop'.$cnt.'_glob["index"] === 0);';
		}
		if ($usesLast) {
			$out .= "\n\t\t".'$_loop'.$cnt.'_glob["last"] = (string) ($_loop'.$cnt.'_glob["iteration"] === $_loop'.$cnt.'_glob["total"]);';
		}
		$out .= "\n\t\t".'$_loop'.$cnt.'_scope = $this->setScope(array("-loop-"));' . "\n/* -- loop start output */\n".Dwoo_Compiler::PHP_CLOSE;

		// build post processing output and cache it
		$postOut = Dwoo_Compiler::PHP_OPEN . "\n".'/* -- loop end output */'."\n\t\t".'$this->setScope($_loop'.$cnt.'_scope, true);';
		// update properties
		if ($usesIndex) {
			$postOut.="\n\t\t".'$_loop'.$cnt.'_glob["index"]+=1;';
		}
		if ($usesIteration) {
			$postOut.="\n\t\t".'$_loop'.$cnt.'_glob["iteration"]+=1;';
		}
		// end loop
		$postOut .= "\n\t}\n}\n";

		// get block params and save the post-processing output already
		$currentBlock =& $compiler->getCurrentBlock();
		$currentBlock['params']['postOutput'] = $postOut . Dwoo_Compiler::PHP_CLOSE;

		return $out;
	}

	public static function postProcessing(Dwoo_Compiler $compiler, array $params, $prepend, $append, $content)
	{
		return $content . $params['postOutput'];
	}
}
