<?php

/**
 * Strips the spaces at the beginning and end of each line and also the line breaks
 * <pre>
 *  * mode : sets the content being stripped, available mode are 'default' or 'js'
 *    for javascript, which strips the comments to prevent syntax errors
 * </pre>.
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  2008-2013 Jordi Boggiano
 * @copyright  2013-2016 David Sanchez
 * @license    http://dwoo.org/LICENSE   Modified BSD License
 *
 * @link       http://dwoo.org/
 *
 * @version    1.2.3
 * @date       2016-10-15
 */
class Dwoo_Plugin_strip extends Dwoo_Block_Plugin implements Dwoo_ICompilable_Block
{
    public function init($mode = 'default')
    {
    }

    public static function preProcessing(Dwoo_Compiler $compiler, array $params, $prepend, $append, $type)
    {
        return '';
    }

    public static function postProcessing(Dwoo_Compiler $compiler, array $params, $prepend, $append, $content)
    {
        $params = $compiler->getCompiledParams($params);

        $mode = trim($params['mode'], '"\'');
        switch ($mode) {
            case 'js':
            case 'javascript':
                $content = preg_replace('#(?<!:)//\s[^\r\n]*|/\*.*?\*/#s', '', $content);

            case 'default':
            default:
        }
        $content = preg_replace(array("/\n/", "/\r/", '/(<\?(?:php)?|<%)\s*/'), array('', '', '$1 '), preg_replace('#^\s*(.+?)\s*$#m', '$1', $content));

        return $content;
    }
}
