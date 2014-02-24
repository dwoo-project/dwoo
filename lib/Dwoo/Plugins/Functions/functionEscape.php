<?php
namespace Dwoo\Plugins\Functions;

use Dwoo\Plugin;

/**
 * Applies various escaping schemes on the given string
 * <pre>
 *  * value : the string to process
 *  * format : escaping format to use, valid formats are : html, htmlall, url, urlpathinfo, quotes, hex, hexentity, javascript and mail
 *  * charset : character set to use for the conversion (applies to some formats only), defaults to the current Dwoo charset
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2014-02-24
 * @package    Dwoo
 */
class FunctionEscape extends Plugin {

	public function process($value = '', $format = 'html', $charset = null) {
		if ($charset === null) {
			$charset = $this->core->getCharset();
		}

		switch ($format) {

			case 'html':
				return htmlspecialchars((string)$value, ENT_QUOTES, $charset);
			case 'htmlall':
				return htmlentities((string)$value, ENT_QUOTES, $charset);
			case 'url':
				return rawurlencode((string)$value);
			case 'urlpathinfo':
				return str_replace('%2F', '/', rawurlencode((string)$value));
			case 'quotes':
				return preg_replace("#(?<!\\\\)'#", "\\'", (string)$value);
			case 'hex':
				$out = '';
				$cnt = strlen((string)$value);
				for ($i = 0; $i < $cnt; $i ++) {
					$out .= '%' . bin2hex((string)$value[$i]);
				}

				return $out;
			case 'hexentity':
				$out = '';
				$cnt = strlen((string)$value);
				for ($i = 0; $i < $cnt; $i ++) {
					$out .= '&#x' . bin2hex((string)$value[$i]) . ';';
				}

				return $out;
			case 'javascript':
				return strtr((string)$value, array('\\' => '\\\\', "'" => "\\'", '"' => '\\"', "\r" => '\\r', "\n" => '\\n', '</' => '<\/'));
			case 'mail':
				return str_replace(array('@', '.'), array('&nbsp;(AT)&nbsp;', '&nbsp;(DOT)&nbsp;'), (string)$value);
			default:
				$this->core->triggerError('Escape\'s format argument must be one of : html, htmlall, url, urlpathinfo, hex, hexentity, javascript or mail, "' . $format . '" given.', E_USER_WARNING);
		}
	}
}