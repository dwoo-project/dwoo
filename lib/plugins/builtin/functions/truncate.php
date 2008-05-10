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
 * @version    0.9.0
 * @date       2008-05-10
 * @package    Dwoo
 */
function Dwoo_Plugin_truncate(Dwoo $dwoo, $value, $length=80, $etc='...', $break=false, $middle=false)
{
	if($length == 0)
		return '';

	$value = (string) $value;
	$etc = (string) $etc;
	$length = (int) $length;

	if(strlen($value) < $length)
		return $value;

	$length = max($length - strlen($etc), 0);
	if($break === false && $middle === false)
		$value = preg_replace('#\s*(\S*)?$#', '', substr($value, 0, $length+1));
	if($middle === false)
		return substr($value, 0, $length) . $etc;
	return substr($value, 0, ceil($length/2)) . $etc . substr($value, -floor($length/2));
}
