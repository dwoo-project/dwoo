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
 * Outputs a html &lt;a&gt; tag
 * <pre>
 *  * href : the target URI where the link must point
 *  * rest : any other attributes you want to add to the tag can be added as named parameters
 * </pre>.
 * Example :
 * <code>
 * {* Create a simple link out of an url variable and add a special class attribute: *}
 * {a $url class="external" /}
 * {* Mark a link as active depending on some other variable : *}
 * {a $link.url class=tif($link.active "active"); $link.title /}
 * {* This is similar to: <a href="{$link.url}" class="{if $link.active}active{/if}">{$link.title}</a> *}
 * </code>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginA extends BlockPlugin implements ICompilableBlock
{
    /**
     * @param       $href
     * @param array $rest
     */
    public function init($href, array $rest = array())
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
        $p = $compiler->getCompiledParams($params);

        $out = Compiler::PHP_OPEN . 'echo \'<a ' . self::paramsToAttributes($p, "'", $compiler);

        return $out . '>\';' . Compiler::PHP_CLOSE;
    }

    /**
     * @param Compiler $compiler
     * @param array    $params
     * @param string   $prepend
     * @param string   $append
     * @param string   $content
     *
     * @return string
     */
    public static function postProcessing(Compiler $compiler, array $params, $prepend, $append, $content)
    {
        $p = $compiler->getCompiledParams($params);

        // no content was provided so use the url as display text
        if ($content == '') {
            // merge </a> into the href if href is a string
            if (substr($p['href'], - 1) === '"' || substr($p['href'], - 1) === '\'') {
                return Compiler::PHP_OPEN . 'echo ' . substr($p['href'], 0, - 1) . '</a>' . substr($p['href'], - 1) . ';' . Compiler::PHP_CLOSE;
            }

            // otherwise append
            return Compiler::PHP_OPEN . 'echo ' . $p['href'] . '.\'</a>\';' . Compiler::PHP_CLOSE;
        }

        // return content
        return $content . '</a>';
    }
}
