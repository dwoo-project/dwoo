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
use Dwoo\Block\Plugin as BlockPlugin;
use Dwoo\ICompilable\Block as ICompilableBlock;

/**
 * Loops over an array and moves the scope into each value, allowing for shorter loop constructs.
 * Note that to access the array key within a loop block, you have to use the {$_key} variable,
 * you can not specify it yourself.
 * <pre>
 *  * from : the array that you want to iterate over
 *  * name : loop name to access it's iterator variables through {$.loop.name.var} see {@link
 *  http://wiki.dwoo.org/index.php/IteratorVariables} for details
 * </pre>
 * Example :
 * instead of a foreach block such as :
 * <code>
 * {foreach $variable value}
 *   {$value.foo} {$value.bar}
 * {/foreach}
 * </code>
 * you can do :
 * <code>
 * {loop $variable}
 *   {$foo} {$bar}
 * {/loop}
 * </code>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginLoop extends BlockPlugin implements ICompilableBlock, IElseable
{
    public static $cnt = 0;

    /**
     * @param        $from
     * @param string $name
     */
    public function init($from, $name = 'default')
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
        // get block params and save the current template pointer to use it in the postProcessing method
        $currentBlock                         = &$compiler->getCurrentBlock();
        $currentBlock['params']['tplPointer'] = $compiler->getPointer();

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
        $params = $compiler->getCompiledParams($params);
        $tpl    = $compiler->getTemplateSource($params['tplPointer']);

        // assigns params
        $src  = $params['from'];
        $name = $params['name'];

        // evaluates which global variables have to be computed
        $varName       = '$dwoo.loop.' . trim($name, '"\'') . '.';
        $shortVarName  = '$.loop.' . trim($name, '"\'') . '.';
        $usesAny       = strpos($tpl, $varName) !== false || strpos($tpl, $shortVarName) !== false;
        $usesFirst     = strpos($tpl, $varName . 'first') !== false || strpos($tpl, $shortVarName . 'first') !== false;
        $usesLast      = strpos($tpl, $varName . 'last') !== false || strpos($tpl, $shortVarName . 'last') !== false;
        $usesIndex     = $usesFirst || strpos($tpl, $varName . 'index') !== false || strpos($tpl, $shortVarName . 'index') !== false;
        $usesIteration = $usesLast || strpos($tpl, $varName . 'iteration') !== false || strpos($tpl, $shortVarName . 'iteration') !== false;
        $usesShow      = strpos($tpl, $varName . 'show') !== false || strpos($tpl, $shortVarName . 'show') !== false;
        $usesTotal     = $usesLast || strpos($tpl, $varName . 'total') !== false || strpos($tpl, $shortVarName . 'total') !== false;

        if (strpos($name, '$this->scope[') !== false) {
            $usesAny = $usesFirst = $usesLast = $usesIndex = $usesIteration = $usesShow = $usesTotal = true;
        }

        // gets foreach id
        $cnt = self::$cnt ++;

        // builds pre processing output
        $pre = Compiler::PHP_OPEN . "\n" . '$_loop' . $cnt . '_data = ' . $src . ';';
        // adds foreach properties
        if ($usesAny) {
            $pre .= "\n" . '$this->globals["loop"][' . $name . '] = array' . "\n(";
            if ($usesIndex) {
                $pre .= "\n\t" . '"index"		=> 0,';
            }
            if ($usesIteration) {
                $pre .= "\n\t" . '"iteration"		=> 1,';
            }
            if ($usesFirst) {
                $pre .= "\n\t" . '"first"		=> null,';
            }
            if ($usesLast) {
                $pre .= "\n\t" . '"last"		=> null,';
            }
            if ($usesShow) {
                $pre .= "\n\t" . '"show"		=> $this->isTraversable($_loop' . $cnt . '_data, true),';
            }
            if ($usesTotal) {
                $pre .= "\n\t" . '"total"		=> $this->count($_loop' . $cnt . '_data),';
            }
            $pre .= "\n);\n" . '$_loop' . $cnt . '_glob =& $this->globals["loop"][' . $name . '];';
        }
        // checks if the loop must be looped
        $pre .= "\n" . 'if ($this->isTraversable($_loop' . $cnt . '_data' . (isset($params['hasElse']) ? ', true' : '') . ') == true)' . "\n{";
        // iterates over keys
        $pre .= "\n\t" . 'foreach ($_loop' . $cnt . '_data as $tmp_key => $this->scope["-loop-"])' . "\n\t{";
        // updates properties
        if ($usesFirst) {
            $pre .= "\n\t\t" . '$_loop' . $cnt . '_glob["first"] = (string) ($_loop' . $cnt . '_glob["index"] === 0);';
        }
        if ($usesLast) {
            $pre .= "\n\t\t" . '$_loop' . $cnt . '_glob["last"] = (string) ($_loop' . $cnt . '_glob["iteration"] === $_loop' . $cnt . '_glob["total"]);';
        }
        $pre .= "\n\t\t" . '$_loop' . $cnt . '_scope = $this->setScope(array("-loop-"));' . "\n/* -- loop start output */\n" . Compiler::PHP_CLOSE;

        // build post processing output and cache it
        $post = Compiler::PHP_OPEN . "\n" . '/* -- loop end output */' . "\n\t\t" . '$this->setScope($_loop' . $cnt . '_scope, true);';
        // update properties
        if ($usesIndex) {
            $post .= "\n\t\t" . '$_loop' . $cnt . '_glob["index"]+=1;';
        }
        if ($usesIteration) {
            $post .= "\n\t\t" . '$_loop' . $cnt . '_glob["iteration"]+=1;';
        }
        // end loop
        $post .= "\n\t}\n}\n" . Compiler::PHP_CLOSE;
        if (isset($params['hasElse'])) {
            $post .= $params['hasElse'];
        }

        return $pre . $content . $post;
    }
}
