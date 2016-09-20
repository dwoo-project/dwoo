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
 * Captures all the output within this block and saves it into {$.capture.default} by default,
 * or {$.capture.name} if you provide another name.
 * <pre>
 *  * name : capture name, used to read the value afterwards
 *  * assign : if set, the value is also saved in the given variable
 *  * cat : if true, the value is appended to the previous one (if any) instead of overwriting it
 * </pre>
 * If the cat parameter is true, the content
 * will be appended to the existing content.
 * Example :
 * <code>
 * {capture "foo"}
 *   Anything in here won't show, it will be saved for later use..
 * {/capture}
 * Output was : {$.capture.foo}
 * </code>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginCapture extends BlockPlugin implements ICompilableBlock
{
    /**
     * @param string $name
     * @param null   $assign
     * @param bool   $cat
     * @param bool   $trim
     */
    public function init($name = 'default', $assign = null, $cat = false, $trim = false)
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
        return Compiler::PHP_OPEN . $prepend . 'ob_start();' . $append . Compiler::PHP_CLOSE;
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
        $params = $compiler->getCompiledParams($params);

        $out = $content . Compiler::PHP_OPEN . $prepend . "\n" . '$tmp = ob_get_clean();';
        if ($params['trim'] !== 'false' && $params['trim'] !== 0) {
            $out .= "\n" . '$tmp = trim($tmp);';
        }
        if ($params['cat'] === 'true' || $params['cat'] === 1) {
            $out .= "\n" . '$tmp = $this->readVar(\'dwoo.capture.\'.' . $params['name'] . ') . $tmp;';
        }
        if ($params['assign'] !== 'null') {
            $out .= "\n" . '$this->scope[' . $params['assign'] . '] = $tmp;';
        }

        return $out . "\n" . '$this->globals[\'capture\'][' . $params['name'] . '] = $tmp;' . $append . Compiler::PHP_CLOSE;
    }
}
