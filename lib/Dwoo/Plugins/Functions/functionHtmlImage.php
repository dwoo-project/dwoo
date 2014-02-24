<?php
namespace Dwoo\Plugins\Functions;

use Dwoo\Exception;
use Dwoo\Plugin;

/**
 * Generate HTML <img> tag.
 * The height and width are automatically calculated from the image
 * file if they are not supplied.
 *
 * Params:
 * <pre>
 * - file        - (required) - file (and path) of image
 * - alt         - (optional) - Alternative description of the image
 * - width       - (optional) - image width (default actual width)
 * - height      - (optional) - image height (default actual height)
 * - id          - (optional) - id tag
 * - class       - (optional) - class tag
 * - style       - (optional) - possibility to add custom css styles
 * </pre>
 *
 * This file is released under the LGPL "GNU Lesser General Public License"
 * More information can be found at:
 * {@link http://www.gnu.org/copyleft/lesser.html}
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from
 * the use of this software.
 *
 * @author     David Sanchez <david38.sanchez@gmail.com>
 * @copyright  Copyright (c) 2013, David Sanchez
 * @license    http://www.gnu.org/copyleft/lesser.html  GNU Lesser General Public License
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2014-02-24
 * @package    Dwoo
 */
class FunctionHtmlImage extends Plugin {

	public function process($file, $alt = null, $width = null, $height = null, $id = null, $class = null, $style = null) {
		if ($file == '' /* || !@file_get_contents($file)*/) {
			throw new Exception('You need to specify a valid url to your image', E_USER_ERROR);
		}

		$out = ' src="' . $file . '"';

		// Alt
		if ($alt != null) {
			$out .= ' alt="' . $alt . '"';
		}

		if ($width != null) {
			$out .= ' width="' . $width . '"';
		}
		if ($height != null) {
			$out .= ' height="' . $height . '"';
		}

		if ($width == null && $height == null) {
			$size = getimagesize($file);
			$out .= $size[3];
		}

		if ($id != null) {
			$out .= ' id="' . $id . '"';
		}

		if ($class != null) {
			$out .= ' class="' . $class . '"';
		}

		if ($style != null) {
			$out .= ' style="' . $style . '"';
		}

		return '<img' . $out . ' />';
	}
}
