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
use Dwoo\IElseable;
use Dwoo\ICompilable\Block as ICompilableBlock;

/**
 * Acts as a php elseif block, allowing you to add one more condition
 * if the previous one(s) didn't match. See the {if} plugin for syntax details.
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginElseif extends PluginIf implements ICompilableBlock, IElseable
{
    /**
     * @param array $rest
     */
    public function init(array $rest)
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
        $preContent = '';
        while (true) {
            $preContent .= $compiler->removeTopBlock();
            $block      = &$compiler->getCurrentBlock();
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

        $tokens = $compiler->getParamTokens($params);
        $params = $compiler->getCompiledParams($params);

        $pre  = Compiler::PHP_OPEN . 'elseif (' . implode(' ', self::replaceKeywords($params['*'], $tokens['*'], $compiler)) . ") {\n" . Compiler::PHP_CLOSE;
        $post = Compiler::PHP_OPEN . "\n}" . Compiler::PHP_CLOSE;

        if (isset($params['hasElse'])) {
            $post .= $params['hasElse'];
        }

        $block                      = &$compiler->getCurrentBlock();
        $block['params']['hasElse'] = $pre . $content . $post;

        return '';
    }
}
