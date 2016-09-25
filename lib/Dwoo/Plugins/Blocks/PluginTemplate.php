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
 * @date      2016-09-20
 * @link      http://dwoo.org/
 */

namespace Dwoo\Plugins\Blocks;

use Dwoo\Core;
use Dwoo\Compiler;
use Dwoo\Block\Plugin as BlockPlugin;
use Dwoo\ICompilable\Block as ICompilableBlock;
use Dwoo\Compilation\Exception as CompilationException;

/**
 * Defines a sub-template that can then be called (even recursively) with the defined arguments
 * <pre>
 *  * name : template name
 *  * rest : list of arguments and optional default values
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginTemplate extends BlockPlugin implements ICompilableBlock
{
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
        $params       = $compiler->getCompiledParams($params);
        $parsedParams = array();
        if (!isset($params['*'])) {
            $params['*'] = array();
        }
        foreach ($params['*'] as $param => $defValue) {
            if (is_numeric($param)) {
                $param    = $defValue;
                $defValue = null;
            }
            $param = trim($param, '\'"');
            if (!preg_match('#^[a-z0-9_]+$#i', $param)) {
                throw new CompilationException($compiler, 'Function : parameter names must contain only A-Z, 0-9 or _');
            }
            $parsedParams[$param] = $defValue;
        }
        $params['name'] = substr($params['name'], 1, - 1);
        $params['*']    = $parsedParams;
        $params['uuid'] = uniqid();
        $compiler->addTemplatePlugin($params['name'], $parsedParams, $params['uuid']);
        $currentBlock           = &$compiler->getCurrentBlock();
        $currentBlock['params'] = $params;

        return '';
    }

    /**
     * @param Compiler $compiler
     * @param array    $params
     * @param string   $prepend
     * @param string   $append
     * @param string   $content
     *
     * @return string|void
     */
    public static function postProcessing(Compiler $compiler, array $params, $prepend, $append, $content)
    {
        $paramstr = 'Dwoo\Core $dwoo';
        $init     = 'static $_callCnt = 0;' . "\n" . '$dwoo->scope[\' ' . $params['uuid'] . '\'.$_callCnt] = array();' . "\n" . '$_scope = $dwoo->setScope(array(\' ' . $params['uuid'] . '\'.($_callCnt++)));' . "\n";
        $cleanup  = '/* -- template end output */ $dwoo->setScope($_scope, true);';
        foreach ($params['*'] as $param => $defValue) {
            if ($defValue === null) {
                $paramstr .= ', $' . $param;
            } else {
                $paramstr .= ', $' . $param . ' = ' . $defValue;
            }
            $init .= '$dwoo->scope[\'' . $param . '\'] = $' . $param . ";\n";
        }
        $init .= '/* -- template start output */';

        $funcName = 'Plugin' . Core::toCamelCase($params['name']) . Core::toCamelCase($params['uuid']);

        $search      = array('$this->charset', '$this->', '$this,',);
        $replacement = array('$dwoo->getCharset()', '$dwoo->', '$dwoo,',);
        $content     = str_replace($search, $replacement, $content);

        $body = 'if (!function_exists(\'' . $funcName . "')) {\nfunction " . $funcName . '(' . $paramstr . ') {' . "\n$init" . Compiler::PHP_CLOSE . $prepend . $content . $append . Compiler::PHP_OPEN . $cleanup . "\n}\n}";
        $compiler->addTemplatePlugin($params['name'], $params['*'], $params['uuid'], $body);
    }

    /**
     * @param       $name
     * @param array $rest
     */
    public function init($name, array $rest = array())
    {
    }
}
