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
function Dwoo_Plugin_include(Dwoo $dwoo, $file, $cache_time = null, $cache_id = null, $compile_id = null, $assign = null, array $rest = array())
{
	if($file === '')
		return;

	if(preg_match('#^([a-z]{2,}):(.*)#i', $file, $m))
	{
		$resource = $m[1];
		$identifier = $m[2];
	}
	else
	{
		// get the current template's resource
		$resource = $dwoo->getTemplate()->getResourceName();
		$identifier = $file;
	}

	if($resource === 'file' && $policy = $dwoo->getSecurityPolicy())
	{
		while(true)
		{
			if(preg_match('{^([a-z]+?)://}i', $identifier))
				return $dwoo->triggerError('The security policy prevents you to read files from external sources.', E_USER_WARNING);

			$identifier = realpath($identifier);
			$dirs = $policy->getAllowedDirectories();
			foreach($dirs as $dir=>$dummy)
			{
				if(strpos($identifier, $dir) === 0)
					break 2;
			}
			return $dwoo->triggerError('The security policy prevents you to read <em>'.$identifier.'</em>', E_USER_WARNING);
		}
	}

	try {
		$include = $dwoo->templateFactory($resource, $identifier, $cache_time, $cache_id, $compile_id);
	} catch (Dwoo_Exception $e) {
		$dwoo->triggerError('Include : Resource <em>'.$resource.'</em> was not added to Dwoo, can not include <em>'.$identifier.'</em>', E_USER_WARNING);
	}

	if($include === null)
		return;
	elseif($include === false)
		$dwoo->triggerError('Include : Including "'.$resource.':'.$identifier.'" was not allowed for an unknown reason.', E_USER_WARNING);

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
