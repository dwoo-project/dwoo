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
function DwooPlugin_fetch(Dwoo $dwoo, $file, $assign = null)
{
	if($file === '')
		return;

	// TODO check for security here on http/ftp + secure dirs
	$file = str_replace(array("\t", "\n", "\r"), array('\\t', '\\n', '\\r'), $file);

	$out = file_get_contents($file);

	if($assign !== null)
		$dwoo->assignInScope($out, $assign);
	else
		return $out;
}

?>