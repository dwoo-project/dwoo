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
 * This plugin serves as a {else} block specifically for the {with} plugin.
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginWithelse extends BlockPlugin implements ICompilableBlock
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
     */
    public static function preProcessing(Compiler $compiler, array $params, $prepend, $append, $type)
    {
        $with = &$compiler->findBlock('with', true);

        $params['initialized'] = true;
        $compiler->injectBlock($type, $params);

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
        if (!isset($params['initialized'])) {
            return '';
        }

        $block                      = &$compiler->getCurrentBlock();
        $block['params']['hasElse'] = Compiler::PHP_OPEN . "else {\n" . Compiler::PHP_CLOSE . $content . Compiler::PHP_OPEN . "\n}" . Compiler::PHP_CLOSE;

        return '';
    }
}
