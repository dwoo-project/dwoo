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

use Dwoo\IElseable;
use Dwoo\Compiler;
use Dwoo\Block\Plugin as BlockPlugin;
use Dwoo\ICompilable\Block as ICompilableBlock;

/**
 * Moves the scope down into the provided variable, allowing you to use shorter
 * variable names if you repeatedly access values into a single array.
 * The with block won't display anything at all if the provided scope is empty,
 * so in effect it acts as {if $var}*content*{/if}
 * <pre>
 *  * var : the variable name to move into
 * </pre>
 * Example :
 * instead of the following :
 * <code>
 * {if $long.boring.prefix}
 *   {$long.boring.prefix.val} - {$long.boring.prefix.secondVal} - {$long.boring.prefix.thirdVal}
 * {/if}
 * </code>
 * you can use :
 * <code>
 * {with $long.boring.prefix}
 *   {$val} - {$secondVal} - {$thirdVal}
 * {/with}
 * </code>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginWith extends BlockPlugin implements ICompilableBlock, IElseable
{
    protected static $cnt = 0;

    /**
     * @param $var
     */
    public function init($var)
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
     * @return string
     */
    public static function postProcessing(Compiler $compiler, array $params, $prepend, $append, $content)
    {
        $rparams = $compiler->getRealParams($params);
        $cparams = $compiler->getCompiledParams($params);

        $compiler->setScope($rparams['var']);

        $pre = Compiler::PHP_OPEN . 'if (' . $cparams['var'] . ')' . "\n{\n" . '$_with' . (self::$cnt) . ' = $this->setScope("' . $rparams['var'] . '");' . "\n/* -- start with output */\n" . Compiler::PHP_CLOSE;

        $post = Compiler::PHP_OPEN . "\n/* -- end with output */\n" . '$this->setScope($_with' . (self::$cnt ++) . ', true);' . "\n}\n" . Compiler::PHP_CLOSE;

        if (isset($params['hasElse'])) {
            $post .= $params['hasElse'];
        }

        return $pre . $content . $post;
    }
}
