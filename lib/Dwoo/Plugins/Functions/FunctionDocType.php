<?php
namespace Dwoo\Plugins\Functions;

use Dwoo\Plugin;

/**
 * Outputs a valid (X)HTML DOCTYPE
 * <pre>
 *  * docType : the name of the doctype, falls back to HTML5 if not recognized or given
 * </pre>
 *
 * Available DOCTYPES:
 * <pre>
 *  * HTML5
 *  * XHTML11
 *  * XHTML1_STRICT
 *  * XHTML1_TRANSITIONAL
 *  * XHTML1_FRAMESET
 *  * XHTML_BASIC1
 *  * HTML4_STRICT
 *  * HTML4_LOOSE
 *  * HTML4_FRAMESET
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
class FunctionDocType extends Plugin {

	const HTML5               = 'HTML5';
	const XHTML11             = 'XHTML11';
	const XHTML1_STRICT       = 'XHTML1_STRICT';
	const XHTML1_TRANSITIONAL = 'XHTML1_TRANSITIONAL';
	const XHTML1_FRAMESET     = 'XHTML1_FRAMESET';
	const XHTML_BASIC1        = 'XHTML_BASIC1';
	const HTML4_STRICT        = 'HTML4_STRICT';
	const HTML4_LOOSE         = 'HTML4_LOOSE';
	const HTML4_FRAMESET      = 'HTML4_FRAMESET';

	/**
	 * Process
	 * @param string $docType
	 * @return string
	 */
	public function process($docType = '') {
		switch ($docType) {
			case self::HTML5:
				$result = '<!DOCTYPE html>';
				break;
			case self::XHTML11:
				$result = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.1//EN" "http://www.w3.org/TR/xhtml11/DTD/xhtml11.dtd">';
				break;

			case self::XHTML1_STRICT:
				$result = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">';
				break;

			case self::XHTML1_TRANSITIONAL:
				$result = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">';
				break;

			case self::XHTML1_FRAMESET:
				$result = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Frameset//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-frameset.dtd">';
				break;

			case self::XHTML_BASIC1:
				$result = '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML Basic 1.0//EN" "http://www.w3.org/TR/xhtml-basic/xhtml-basic10.dtd">';
				break;

			case self::HTML4_STRICT:
				$result = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">';
				break;

			case self::HTML4_LOOSE:
				$result = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">';
				break;

			case self::HTML4_FRAMESET:
				$result = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Frameset//EN" "http://www.w3.org/TR/html4/frameset.dtd">';
				break;

			default:
				$result = '<!DOCTYPE html>';
		}

		return $result;
	}
}