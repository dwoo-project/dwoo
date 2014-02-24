<?php
namespace Dwoo\Plugins\Functions;
use Dwoo\Compiler;
use Dwoo\Exception;
use Dwoo\ICompilable;
use Dwoo\Plugin;

/**
 * Returns a link to the gravatar of someone based on his email address, see {@link http://en.gravatar.com/}.
 * <pre>
 *  * email : email address of the user for whom you want the gravatar
 *  * size : the size in pixels of the served image, defaults to 80
 *  * default : an url to the default image to display, or one of the three image
 *              generators: identicon, monsterid or wavatar, see {@link http://en.gravatar.com/site/implement/url}
 *              for more infos on those, by default this will be the gravatar logo
 *  * rating : the highest allowed rating for the images,
 *             it defaults to 'g' (general, the lowest/safest) and other allowed
 *             values (in order) are 'pg' (parental guidance), 'r' (restricted)
 *             and 'x' (boobies, crackwhores, etc.)
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * This file is released under the LGPL
 * "GNU Lesser General Public License"
 * More information can be found here:
 * {@link http://www.gnu.org/copyleft/lesser.html}
 *
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://www.gnu.org/copyleft/lesser.html  GNU Lesser General Public License
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2014-02-24
 * @package    Dwoo
 */
class FunctionGravatar extends Plugin implements ICompilable {

	public static function compile(Compiler $compiler, $email, $size = null, $default = null, $rating = null) {
		$email = trim($email, '"\'');
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			throw new Exception('gravatar: ' . $email . ' is not a valid email.', E_USER_ERROR);
		}

		$out = '\'http://www.gravatar.com/avatar/\'.md5(strtolower(trim("' . $email . '")))';

		$params = array();
		if ($size !== null) {
			if (is_numeric($size)) {
				$params[] = 's=' . ((int)$size);
			}
			else {
				$params[] = 's=\'.((int) ' . $size . ').\'';
			}
		}

		if ($default !== null) {
			if (filter_var($default, FILTER_VALIDATE_URL)) {
				$params[] = 'd=\'.urlencode(' . $default . ').\'';
			}
			else {
				$params[] = 'd=' . $default;
			}
		}

		if ($rating !== null) {
			$r = trim(strtolower($rating), '"\'');
			if (in_array($r, array('g', 'pg', 'r', 'x'))) {
				$params[] = 'r=' . $r;
			}
			else {
				$params[] = 'r=\'.' . $rating . '.\'';
			}
		}
		if (count($params)) {
			$out .= '.\'?' . implode('&amp;', $params) . '\'';
		}

		if (substr($out, -3) == ".''") {
			$out = substr($out, 0, -3);
		}

		return $out;
	}
}
