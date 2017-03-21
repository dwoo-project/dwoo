<?php
/**
 * Copyright (c) 2013-2017
 *
 * @category  Library
 * @package   Dwoo\Plugins\Blocks
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2017 David Sanchez
 * @license   http://dwoo.org/LICENSE LGPLv3
 * @version   1.3.6
 * @date      2017-03-21
 * @link      http://dwoo.org/
 */

namespace Dwoo\Plugins\Blocks;

use Dwoo\Core;
use Dwoo\Compiler;
use Dwoo\Block\Plugin as BlockPlugin;
use Dwoo\ICompilable\Block as ICompilableBlock;

/**
 * Smarty compatibility layer for block plugins, this is used internally and you should not call it.
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginSmartyinterface extends BlockPlugin implements ICompilableBlock
{
    /**
     * @param       $__funcname
     * @param       $__functype
     * @param array $rest
     */
    public function init($__funcname, $__functype, array $rest = array())
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
        $params     = $compiler->getCompiledParams($params);
        $func       = $params['__funcname'];
        $pluginType = $params['__functype'];
        $params     = $params['*'];

        if ($pluginType & Core::CUSTOM_PLUGIN) {
            $customPlugins = $compiler->getCore()->getCustomPlugins();
            $callback      = $customPlugins[$func]['callback'];
            if (is_array($callback)) {
                if (is_object($callback[0])) {
                    $callback = '$this->customPlugins[\'' . $func . '\'][0]->' . $callback[1] . '(';
                } else {
                    $callback = '' . $callback[0] . '::' . $callback[1] . '(';
                }
            } else {
                $callback = $callback . '(';
            }
        } else {
            $callback = 'smarty_block_' . $func . '(';
        }

        $paramsOut = '';
        foreach ($params as $i => $p) {
            $paramsOut .= var_export($i, true) . ' => ' . $p . ',';
        }

        $curBlock                      = &$compiler->getCurrentBlock();
        $curBlock['params']['postOut'] = Compiler::PHP_OPEN . ' $_block_content = ob_get_clean(); $_block_repeat=false; echo ' . $callback . '$_tag_stack[count($_tag_stack)-1], $_block_content, $this, $_block_repeat); } array_pop($_tag_stack);' . Compiler::PHP_CLOSE;

        return Compiler::PHP_OPEN . $prepend . ' if (!isset($_tag_stack)){ $_tag_stack = array(); } $_tag_stack[] = array(' . $paramsOut . '); $_block_repeat=true; ' . $callback . '$_tag_stack[count($_tag_stack)-1], null, $this, $_block_repeat); while ($_block_repeat) { ob_start();' . Compiler::PHP_CLOSE;
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
        return $content . $params['postOut'];
    }
}
