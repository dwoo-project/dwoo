<?php

/**
 * Evaluates the given string as if it was a template
 *
 * Although this plugin is kind of optimized and will
 * not recompile your string each time, it is still not
 * a good practice to use it. If you want to have templates
 * stored in a database or something you should probably use
 * the Dwoo_Template_String class or make another class that
 * extends it
 * <pre>
 *  * var : the string to use as a template
 *  * assign : if set, the output of the template will be saved in this variable instead of being output
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
function Dwoo_Plugin_eval(Dwoo $dwoo, $var, $assign = null)
{
	if($var == '')
		return;

	$tpl = new Dwoo_Template_String($var);
	$out = $dwoo->get($var, $dwoo->readVar('_parent'));

	if($assign !== null)
		$dwoo->assignInScope($out, $assign);
	else
		return $out;
}
