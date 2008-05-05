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
function Dwoo_Plugin_eval(Dwoo $dwoo, $var, $assign = null)
{
	// TOCOM eval is bad, warn people that they should not use it as a full template for DB-templates, they better extend Dwoo_ITemplate for that
	if($var == '')
		return;

	$tpl = new Dwoo_Template_String($var);
	$out = $dwoo->get($var, $dwoo->readVar('_parent'));

	if($assign !== null)
		$dwoo->assignInScope($out, $assign);
	else
		return $out;
}
