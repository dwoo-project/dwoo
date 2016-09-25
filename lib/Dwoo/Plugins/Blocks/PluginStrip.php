<?php
/**
 * Copyright (c) 2013-2016
 *
 * @category  Library
 * @package   Dwoo\Plugins\Blocks
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2016 David Sanchez
 * @license   http://dwoo.org/LICENSE Modified BSD License
 * @version   1.3.0
 * @date      2016-09-19
 * @link      http://dwoo.org/
 */

namespace Dwoo\Plugins\Blocks;

use Dwoo\Compiler;
use Dwoo\Block\Plugin as BlockPlugin;
use Dwoo\ICompilable\Block as ICompilableBlock;

/**
 * Strips the spaces at the beginning and end of each line and also the line breaks
 * <pre>
 *  * mode : sets the content being stripped, available mode are 'default' or 'js'
 *    for javascript, which strips the comments to prevent syntax errors
 * </pre>.
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginStrip extends BlockPlugin implements ICompilableBlock
{
    /**
     * @param string $mode
     */
    public function init($mode = 'default')
    {
    }

    /**
     * @param Compiler $compiler
     * @param array    $params
     * @param string   $prepend
     * @param string   $append
     * @param string   $type
     *
     * @return string
     */
    public static function preProcessing(Compiler $compiler, array $params, $prepend, $append, $type)
    {
        return '';
    }

    /**
     * @param Compiler $compiler
     * @param array    $params
     * @param string   $prepend
     * @param string   $append
     * @param string   $content
     *
     * @return mixed|string
     */
    public static function postProcessing(Compiler $compiler, array $params, $prepend, $append, $content)
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
        $content = preg_replace(array(
            "/\n/",
            "/\r/",
            '/(<\?(?:php)?|<%)\s*/'
        ), array(
            '',
            '',
            '$1 '
        ), preg_replace('#^\s*(.+?)\s*$#m', '$1', $content));

        return $content;
    }
}
