<?php
/**
 * Copyright (c) 2013-2017
 *
 * @category  Library
 * @package   Dwoo\Plugins\Functions
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2017 David Sanchez
 * @license   http://dwoo.org/LICENSE Modified BSD License
 * @version   1.3.2
 * @date      2017-01-06
 * @link      http://dwoo.org/
 */

namespace Dwoo\Plugins\Functions;

use Dwoo\Plugin;

/**
 * Applies various escaping schemes on the given string
 * <pre>
 *  * value : the string to process
 *  * format : escaping format to use, valid formats are : html, htmlall, url, urlpathinfo, quotes, hex, hexentity,
 *  javascript and mail
 *  * charset : character set to use for the conversion (applies to some formats only), defaults to the current Dwoo
 *  charset
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @return mixed|string
 */
class PluginEscape extends Plugin
{
    /**
     * @param string $value
     * @param string $format
     * @param null   $charset
     *
     * @return mixed|string
     */
    public function process($value = '', $format = 'html', $charset = null)
    {
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
                for ($i = 0; $i < $cnt; ++ $i) {
                    $out .= '%' . bin2hex((string)$value[$i]);
                }

                return $out;
            case 'hexentity':
                $out = '';
                $cnt = strlen((string)$value);
                for ($i = 0; $i < $cnt; ++ $i) {
                    $out .= '&#x' . bin2hex((string)$value[$i]) . ';';
                }

                return $out;
            case 'javascript':
            case 'js':
                return strtr((string)$value,
                    array(
                        '\\' => '\\\\',
                        "'"  => "\\'",
                        '"'  => '\\"',
                        "\r" => '\\r',
                        "\n" => '\\n',
                        '</' => '<\/'
                    ));
            case 'mail':
                return str_replace(array(
                    '@',
                    '.'
                ),
                    array(
                        '&nbsp;(AT)&nbsp;',
                        '&nbsp;(DOT)&nbsp;'
                    ),
                    (string)$value);
            default:
                $this->core->triggerError('Escape\'s format argument must be one of : html, htmlall, url, urlpathinfo, hex, hexentity, javascript, js or mail, "' . $format . '" given.',
                    E_USER_WARNING);
        }
    }
}