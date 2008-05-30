<?php

/**
 * Similar to the php for block
 * <pre>
 *  * name : foreach name to access it's iterator variables through {$.foreach.name.var} see {@link http://wiki.dwoo.org/index.php/IteratorVariables} for details
 *  * from : array to iterate from (which equals 0) or a number as a start value
 *  * to : value to stop iterating at (equals count($array) by default if you set an array in from)
 *  * step : defines the incrementation of the pointer at each iteration
 *  * skip : allows you to skip some entries at the start, mostly useless excepted for smarty compatibility
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
class Dwoo_Plugin_for extends Dwoo_Block_Plugin implements Dwoo_ICompilable_Block
{
	public static $cnt=0;

	public function init($name, $from, $to=null, $step=1, $skip=0)
	{
	}

	public static function preProcessing(Dwoo_Compiler $compiler, array $params, $prepend='', $append='', $type)
	{
		$params = $compiler->getCompiledParams($params);
		$tpl = $compiler->getTemplateSource(true);

		// assigns params
		$from = $params['from'];
		$name = $params['name'];
		$step = $params['step'];
		$skip = $params['skip'];
		$to = $params['to'];

 		// evaluates which global variables have to be computed
		$varName = '$dwoo.for.'.trim($name, '"\'').'.';
		$shortVarName = '$.for.'.trim($name, '"\'').'.';
		$usesAny = strpos($tpl, $varName) !== false || strpos($tpl, $shortVarName) !== false;
		$usesFirst = strpos($tpl, $varName.'first') !== false || strpos($tpl, $shortVarName.'first') !== false;
		$usesLast = strpos($tpl, $varName.'last') !== false || strpos($tpl, $shortVarName.'last') !== false;
		$usesIndex = strpos($tpl, $varName.'index') !== false || strpos($tpl, $shortVarName.'index') !== false;
		$usesIteration = $usesFirst || $usesLast || strpos($tpl, $varName.'iteration') !== false || strpos($tpl, $shortVarName.'iteration') !== false;
		$usesShow = strpos($tpl, $varName.'show') !== false || strpos($tpl, $shortVarName.'show') !== false;
		$usesTotal = $usesLast || strpos($tpl, $varName.'total') !== false || strpos($tpl, $shortVarName.'total') !== false;

		// gets foreach id
		$cnt = self::$cnt++;

		// builds pre processing output for
		$out = Dwoo_Compiler::PHP_OPEN . "\n".'$_for'.$cnt.'_from = $_for'.$cnt.'_src = '.$from.';'.
										"\n".'$_for'.$cnt.'_to = '.$to.';'.
										"\n".'$_for'.$cnt.'_step = abs('.$step.');'.
										"\n".'$_for'.$cnt.'_skip = abs('.$skip.');'.
										"\n".'if(is_numeric($_for'.$cnt.'_from) && !is_numeric($_for'.$cnt.'_to)) { $this->triggerError(\'For requires the <em>to</em> parameter when using a numerical <em>from</em>\'); }';
		// adds foreach properties
		if($usesAny)
		{
			$out .= "\n".'$this->globals["for"]['.$name.'] = array'."\n(";
			if($usesIndex) $out .="\n\t".'"index"		=> 0,';
			if($usesIteration) $out .="\n\t".'"iteration"		=> 1,';
			if($usesFirst) $out .="\n\t".'"first"		=> null,';
			if($usesLast) $out .="\n\t".'"last"		=> null,';
			if($usesShow) $out .="\n\t".'"show"		=> ($this->isArray($_for'.$cnt.'_from, true)) || (is_numeric($_for'.$cnt.'_from) && $_for'.$cnt.'_from != $_for'.$cnt.'_to),';
			if($usesTotal) $out .="\n\t".'"total"		=> $this->isArray($_for'.$cnt.'_from) ? count($_for'.$cnt.'_from) - $_for'.$cnt.'_skip : (is_numeric($_for'.$cnt.'_from) ? abs(($_for'.$cnt.'_to + 1 - $_for'.$cnt.'_from)/$_for'.$cnt.'_step) : 0),';
			$out.="\n);\n".'$_for'.$cnt.'_glob =& $this->globals["for"]['.$name.'];';
		}
		// checks if foreach must be looped
		$out .= "\n".'if($this->isArray($_for'.$cnt.'_from, true) || (is_numeric($_for'.$cnt.'_from) && abs(($_for'.$cnt.'_from - $_for'.$cnt.'_to)/$_for'.$cnt.'_step) !== 0))'."\n{";
		// iterates over keys
		$out .= "\n\t".'if($this->isArray($_for'.$cnt.'_from, true)) {
		$_for'.$cnt.'_from = 0;
		$_for'.$cnt.'_to = is_numeric($_for'.$cnt.'_to) ? $_for'.$cnt.'_to - $_for'.$cnt.'_step : count($_for'.$cnt.'_src)-1;
	}
	$_for'.$cnt.'_keys = array();
	if(($_for'.$cnt.'_from + $_for'.$cnt.'_skip) <= $_for'.$cnt.'_to) {
		for($tmp=($_for'.$cnt.'_from + $_for'.$cnt.'_skip); $tmp <= $_for'.$cnt.'_to; $tmp += $_for'.$cnt.'_step)
			$_for'.$cnt.'_keys[] = $tmp;
	} else {
		for($tmp=($_for'.$cnt.'_from - $_for'.$cnt.'_skip); $tmp > $_for'.$cnt.'_to; $tmp -= $_for'.$cnt.'_step)
			$_for'.$cnt.'_keys[] = $tmp;
	}
	foreach($_for'.$cnt.'_keys as $this->scope['.$name.'])'."\n\t{";
		// updates properties
		if($usesIndex)
			$out.="\n\t\t".'$_for'.$cnt.'_glob["index"] = $this->scope['.$name.'];';
		if($usesFirst)
			$out .= "\n\t\t".'$_for'.$cnt.'_glob["first"] = (string) ($_for'.$cnt.'_glob["iteration"] === 1);';
		if($usesLast)
			$out .= "\n\t\t".'$_for'.$cnt.'_glob["last"] = (string) ($_for'.$cnt.'_glob["iteration"] === $_for'.$cnt.'_glob["total"]);';
		$out .= "\n// -- for start output\n".Dwoo_Compiler::PHP_CLOSE;


		// build post processing output and cache it
		$postOut = Dwoo_Compiler::PHP_OPEN . '// -- for end output';
		// update properties
		if($usesIteration)
			$postOut.="\n\t\t".'$_for'.$cnt.'_glob["iteration"]+=1;';
		// end loop
		$postOut .= "\n\t}\n}\n";

		// get block params and save the post-processing output already
		$currentBlock =& $compiler->getCurrentBlock();
		$currentBlock['params']['postOutput'] = $postOut . Dwoo_Compiler::PHP_CLOSE;

		return $out;
	}

	public static function postProcessing(Dwoo_Compiler $compiler, array $params, $prepend='', $append='')
	{
		return $params['postOutput'];
	}
}
