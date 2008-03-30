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
 * @version    0.3.3
 * @date       2008-03-19
 * @package    Dwoo
 */
function DwooPlugin_include(Dwoo $dwoo, $file, $cache_time = null, $cache_id = null, $compile_id = null, $assign = null, array $rest = array())
{
	if($file === '')
		return;

	if(preg_match('#^([a-z]{2,}):(.*)#i', $file, $m))
	{
		$include = $dwoo->getTemplate($m[1], $m[2], $cache_time, $cache_id, $compile_id);
	}
	else
	{
		$include = $dwoo->getTemplate('file', $file, $cache_time, $cache_id, $compile_id);
	}

	if($include === null)
		return;
	elseif($include === false)
		throw new Exception('Include not permitted.', E_USER_ERROR);

	if(count($rest))
	{
		$vars = $rest;
	}
	else
	{
		$vars = $dwoo->readVar('_parent');
	}

	$out = $dwoo->get($include, $vars);

	if($assign !== null)
		$dwoo->assignInScope($out, $assign);
	else
		return $out;
}

?>