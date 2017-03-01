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
 * @version   1.3.4
 * @date      2017-03-01
 * @link      http://dwoo.org/
 */

namespace Dwoo\Plugins\Functions;

use Dwoo\Plugin;

/**
 * Capitalizes the first letter of each word
 * <pre>
 *  * value : the string to capitalize
 *  * numwords : if true, the words containing numbers are capitalized as well
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginCapitalize extends Plugin
{
    /**
     * @param string $value
     * @param bool   $numwords
     *
     * @return string
     */
    public function process($value, $numwords = false)
    {
        if ($numwords || preg_match('#^[^0-9]+$#', $value)) {
            return mb_convert_case((string)$value, MB_CASE_TITLE, $this->core->getCharset());
        } else {
            $bits = explode(' ', (string)$value);
            $out  = '';
            foreach ($bits as $k => $v){
                if (preg_match('#^[^0-9]+$#', $v)) {
                    $out .= ' ' . mb_convert_case($v, MB_CASE_TITLE, $this->core->getCharset());
                } else {
                    $out .= ' ' . $v;
                }
            }

            return substr($out, 1);
        }
    }
}
