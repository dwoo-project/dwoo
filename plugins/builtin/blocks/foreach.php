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
class DwooPlugin_foreach extends DwooBlockPlugin implements DwooICompilableBlock
{
	public static $cnt=0;

	public function init($from, $key=null, $item=null, $name='default')
	{
	}

	public static function preProcessing(DwooCompiler $compiler, array $params, $prepend='', $append='', $type)
	{
		$params = $compiler->getCompiledParams($params);
		$tpl = $compiler->getTemplateSource(true);

		// assigns params
		$src = $params['from'];

		if($params['item'] !== 'null')
		{
			if($params['key'] !== 'null')
				$key = $params['key'];
			$val = $params['item'];
		}
		elseif($params['key'] !== 'null')
		{
			$val = $params['key'];
		}
		else
			$compiler->triggerError('Foreach <em>item</em> parameter missing', E_USER_ERROR);
		$name = $params['name'];

		if(substr($val,0,1) !== '"' && substr($val,0,1) !== '\'')
			$compiler->triggerError('Foreach <em>item</em> parameter must be of type string', E_USER_ERROR);
		if(isset($key) && substr($val,0,1) !== '"' && substr($val,0,1) !== '\'')
			$compiler->triggerError('Foreach <em>key</em> parameter must be of type string', E_USER_ERROR);

		// evaluates which global variables have to be computed
		$varName = '$dwoo.foreach.'.trim($name, '"\'').'.';
		$shortVarName = '$.foreach.'.trim($name, '"\'').'.';
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
		$out = DwooCompiler::PHP_OPEN . "\n".'$_fh'.$cnt.'_data = '.$src.';';
		// adds foreach properties
		if($usesAny)
		{
			$out .= "\n".'$this->globals["foreach"]['.$name.'] = array'."\n(";
			if($usesIndex) $out .="\n\t".'"index"		=> 0,';
			if($usesIteration) $out .="\n\t".'"iteration"		=> 1,';
			if($usesFirst) $out .="\n\t".'"first"		=> null,';
			if($usesLast) $out .="\n\t".'"last"		=> null,';
			if($usesShow) $out .="\n\t".'"show"		=> $this->isArray($_fh'.$cnt.'_data, true, true),';
			if($usesTotal) $out .="\n\t".'"total"		=> $this->isArray($_fh'.$cnt.'_data) ? count($_fh'.$cnt.'_data) : 0,';
			$out.="\n);\n".'$_fh'.$cnt.'_glob =& $this->globals["foreach"]['.$name.'];';
		}
		// checks if foreach must be looped
		$out .= "\n".'if($this->isArray($_fh'.$cnt.'_data, true, true) === true)'."\n{";
		// iterates over keys
		$out .= "\n\t".'foreach($_fh'.$cnt.'_data as '.(isset($key)?'$this->scope['.$key.']=>':'').'$this->scope['.$val.'])'."\n\t{";
		// updates properties
		if($usesFirst)
			$out .= "\n\t\t".'$_fh'.$cnt.'_glob["first"] = (string) ($_fh'.$cnt.'_glob["index"] === 0);';
		if($usesLast)
			$out .= "\n\t\t".'$_fh'.$cnt.'_glob["last"] = (string) ($_fh'.$cnt.'_glob["iteration"] === $_fh'.$cnt.'_glob["total"]);';
		$out .= "\n// -- foreach start output\n".DwooCompiler::PHP_CLOSE;

		// build post processing output and cache it
		$postOut = DwooCompiler::PHP_OPEN . "\n".'// -- foreach end output';
		// update properties
		if($usesIndex)
			$postOut.="\n\t\t".'$_fh'.$cnt.'_glob["index"]+=1;';
		if($usesIteration)
			$postOut.="\n\t\t".'$_fh'.$cnt.'_glob["iteration"]+=1;';
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