<?php
/**
 * TOCOM
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
 * @version    0.3.4
 * @date       2008-04-09
 * @package    Dwoo
 */
class DwooPlugin_for extends DwooBlockPlugin implements DwooICompilableBlock
{
	public static $cnt=0;

	public function init($name, $from, $to=null, $step=1, $skip=0)
	{
	}

	public static function preProcessing(DwooCompiler $compiler, array $params, $prepend='', $append='', $type)
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
		$usesAny = strpos($tpl, $varName) !== false;
		$usesFirst = strpos($tpl, $varName.'first') !== false;
		$usesLast = strpos($tpl, $varName.'last') !== false;
		$usesIndex = strpos($tpl, $varName.'index') !== false;
		$usesIteration = $usesFirst || $usesLast || strpos($tpl, $varName.'iteration') !== false;
		$usesShow = strpos($tpl, $varName.'show') !== false;
		$usesTotal = $usesLast || strpos($tpl, $varName.'total') !== false;

		// gets foreach id
		$cnt = self::$cnt++;

		// builds pre processing output for
		$out = DwooCompiler::PHP_OPEN . "\n".'$_for'.$cnt.'_from = $_for'.$cnt.'_src = '.$from.';'.
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
		$out .= "\n// -- for start output\n".DwooCompiler::PHP_CLOSE;


		// build post processing output and cache it
		$postOut = DwooCompiler::PHP_OPEN . '// -- for end output';
		// update properties
		if($usesIteration)
			$postOut.="\n\t\t".'$_for'.$cnt.'_glob["iteration"]+=1;';
		// end loop
		$postOut .= "\n\t}\n}\n";

		// get block params and save the post-processing output already
		$currentBlock =& $compiler->getCurrentBlock();
		$currentBlock['params']['postOutput'] = $postOut . DwooCompiler::PHP_CLOSE;

		return $out;
	}

	public static function postProcessing(DwooCompiler $compiler, array $params, $prepend='', $append='')
	{
		return $params['postOutput'];
	}
}

?>