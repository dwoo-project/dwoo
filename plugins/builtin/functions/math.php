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
function DwooPlugin_math_compile(DwooCompiler $compiler, $equation, $format='', $assign='', array $rest=array())
{
	/**
	 * Holds the allowed function, characters, operators and constants
	 */
	$allowed = array
	(
		'0', '1', '2', '3', '4', '5', '6', '7', '8', '9',
		'+', '-', '/', '*', '.', ' ', '<<', '>>', '%', '&', '^', '|', '~',
		'abs(', 'ceil(', 'floor(', 'exp(', 'log10(',
		'cos(', 'sin(', 'sqrt(', 'tan(',
		'M_PI', 'INF', 'M_E',
	);

	/**
	 * Holds the functions that can accept multiple arguments
	 */
	$funcs = array
	(
 		'round(', 'log(', 'pow(',
		'max(', 'min(', 'rand(',
 	);

	$equation = $equationSrc = str_ireplace(array('pi', 'M_PI()', 'inf', ' e '), array('M_PI', 'M_PI', 'INF', ' M_E '), $equation);

	$delim = $equation[0];
	$open = $delim.'.';
	$close = '.'.$delim;
	$equation = substr($equation, 1, -1);
	$out = '';
	$ptr = 1;
	$allowcomma = 0;
	while(strlen($equation) > 0)
	{
		$substr = substr($equation, 0, $ptr);
		// allowed string
		if (array_search($substr, $allowed) !== false) {
			$out.=$substr;
			$equation = substr($equation, $ptr);
			$ptr = 0;
		}
		// allowed func
		elseif (array_search($substr, $funcs) !== false) {
			$out.=$substr;
			$equation = substr($equation, $ptr);
			$ptr = 0;
			$allowcomma++;
			if($allowcomma === 1) {
				$allowed[] = ',';
			}
		}
		// variable
		elseif(isset($rest[$substr]))
		{
			$out.=$rest[$substr];
			$equation = substr($equation, $ptr);
			$ptr = 0;
		}
		// pre-replaced variable
		elseif($substr === $open)
		{
			preg_match('#.*\((?:[^()]*?|(?R))\)'.str_replace('.', '\\.', $close).'#', substr($equation, 2), $m);
			if(empty($m))
				preg_match('#.*?'.str_replace('.', '\\.', $close).'#', substr($equation, 2), $m);
			$out.=substr($m[0], 0, -2);
			$equation = substr($equation, strlen($m[0])+2);
			$ptr = 0;
		}
		// opening parenthesis
		elseif ($substr==='(') {
			if($allowcomma>0)
				$allowcomma++;

			$out.=$substr;
			$equation = substr($equation, $ptr);
			$ptr = 0;
		}
		// closing parenthesis
		elseif ($substr===')') {
			if($allowcomma>0) {
				$allowcomma--;
				if($allowcomma===0) {
					array_pop($allowed);
				}
			}

			$out.=$substr;
			$equation = substr($equation, $ptr);
			$ptr = 0;
		}
		// parse error if we've consumed the entire equation without finding anything valid
		elseif ($ptr >= strlen($equation)) {
			$compiler->triggerError('Math : Syntax error or variable undefined in equation '.$equationSrc.' at '.$substr, E_USER_ERROR);
			return;
		}
		else
		{
			$ptr++;
		}
	}
	if($format !== '\'\'')
		$out = 'sprintf('.$format.', '.$out.')';
	if($assign !== '\'\'')
		return '($this->assignInScope('.$out.', '.$assign.'))';
	return '('.$out.')';
}

?>