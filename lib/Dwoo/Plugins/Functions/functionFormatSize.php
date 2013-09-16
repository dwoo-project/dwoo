<?php
namespace Dwoo\Plugins\Functions;
use Dwoo\Core;

/**
 * Formats a given filesize in bytes into a human readable filesize
 * <pre>
 *  * size : the filesize in bytes
 * </pre>
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * This file is released under the LGPL
 * "GNU Lesser General Public License"
 * More information can be found here:
 * {@link http://www.gnu.org/copyleft/lesser.html}
 *
 * @author     Stephan Wentz <swentz@brainbits.net>
 * @copyright  Copyright (c) 2008, brainbits GmbH
 * @license    http://www.gnu.org/copyleft/lesser.html  GNU Lesser General Public License
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-08
 * @package    Dwoo
 */
function functionFormatSize(Core $dwoo, $size) {
	if (!$size) {
		$size = 0;
	}

	$size     = (int)$size;
	$prefixes = array("KB", "MB", "GB");
	$prefix   = 'Byte';

	while (sizeof($prefixes) && $size / 1024 > 1) {
		$size /= 1024;
		$prefix = array_shift($prefixes);
	}

	$result = round($size) . " " . $prefix;

	return $result;
}