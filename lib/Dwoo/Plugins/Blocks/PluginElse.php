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
use Dwoo\Compilation\Exception as CompilationException;

/**
 * Generic else block, it supports all builtin optional-display blocks which are if/for/foreach/loop/with.
 * If any of those block contains an else statement, the content between {else} and {/block} (you do not
 * need to close the else block) will be shown if the block's condition has no been met
 * Example :
 * <code>
 * {foreach $array val}
 *   $array is not empty so we display it's values : {$val}
 * {else}
 *   if this shows, it means that $array is empty or doesn't exist.
 * {/foreach}
 * </code>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginElse extends BlockPlugin implements ICompilableBlock
{
    public function init()
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
     * @throws CompilationException
     */
    public static function preProcessing(Compiler $compiler, array $params, $prepend, $append, $type)
    {
        $preContent = '';
        while (true) {
            $preContent .= $compiler->removeTopBlock();
            $block = &$compiler->getCurrentBlock();
            if (!$block) {
                throw new CompilationException($compiler, 'An else block was found but it was not preceded by an if or other else-able construct');
            }
            $interfaces = class_implements($block['class']);
            if (in_array('Dwoo\IElseable', $interfaces) !== false) {
                break;
            }
        }

        $params['initialized'] = true;
        $compiler->injectBlock($type, $params);

        return $preContent;
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
        if (!isset($params['initialized'])) {
            return '';
        }

        $block                      = &$compiler->getCurrentBlock();
        $block['params']['hasElse'] = Compiler::PHP_OPEN . "else {\n" . Compiler::PHP_CLOSE . $content . Compiler::PHP_OPEN . "\n}" . Compiler::PHP_CLOSE;

        return '';
    }
}
