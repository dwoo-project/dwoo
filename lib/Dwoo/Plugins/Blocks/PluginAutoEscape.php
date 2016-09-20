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
 * Overrides the compiler auto-escape setting within the block
 * <pre>
 *  * enabled : if set to "on", "enable", true or 1 then the compiler autoescaping is enabled inside this block. set to
 *  "off", "disable", false or 0 to disable it
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginAutoEscape extends BlockPlugin implements ICompilableBlock
{
    protected static $stack = array();

    /**
     * @param $enabled
     */
    public function init($enabled)
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
        $params = $compiler->getCompiledParams($params);
        switch (strtolower(trim((string)$params['enabled'], '"\''))) {

            case 'on':
            case 'true':
            case 'enabled':
            case 'enable':
            case '1':
                $enable = true;
                break;
            case 'off':
            case 'false':
            case 'disabled':
            case 'disable':
            case '0':
                $enable = false;
                break;
            default:
                throw new CompilationException($compiler, 'Auto_Escape : Invalid parameter (' . $params['enabled'] . '), valid parameters are "enable"/true or "disable"/false');
        }

        self::$stack[] = $compiler->getAutoEscape();
        $compiler->setAutoEscape($enable);

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
        $compiler->setAutoEscape(array_pop(self::$stack));

        return $content;
    }
}
