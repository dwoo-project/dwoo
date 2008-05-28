<?php

/**
 * Reads a file
 * <pre>
 *  * file : path or URI of the file to read (however reading from another website is not recommended for performance reasons)
 *  * assign : if set, the file will be saved in this variable instead of being output
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
 * @version    0.9.0
 * @date       2008-05-10
 * @package    Dwoo
 */
function Dwoo_Plugin_fetch(Dwoo $dwoo, $file, $assign = null)
{
	if($file === '')
		return;

	if($policy = $dwoo->getSecurityPolicy())
	{
		while(true)
		{
			if(preg_match('{^([a-z]+?)://}i', $file))
				return $dwoo->triggerError('The security policy prevents you to read files from external sources.', E_USER_WARNING);

			$file = realpath($file);
			$dirs = $policy->getAllowedDirectories();
			foreach($dirs as $dir=>$dummy)
			{
				if(strpos($file, $dir) === 0)
					break 2;
			}
			return $dwoo->triggerError('The security policy prevents you to read <em>'.$file.'</em>', E_USER_WARNING);
		}
	}
	$file = str_replace(array("\t", "\n", "\r"), array('\\t', '\\n', '\\r'), $file);

	$out = file_get_contents($file);

	if($assign !== null)
		$dwoo->assignInScope($out, $assign);
	else
		return $out;
}
