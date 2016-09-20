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
 * Compatibility plugin for smarty templates, do not use otherwise, this is deprecated.
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginSection extends BlockPlugin implements ICompilableBlock, IElseable
{
    public static $cnt = 0;

    /**
     * @param      $name
     * @param      $loop
     * @param null $start
     * @param null $step
     * @param null $max
     * @param bool $show
     */
    public function init($name, $loop, $start = null, $step = null, $max = null, $show = true)
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
        $output = Compiler::PHP_OPEN;
        $params = $compiler->getCompiledParams($params);

        // assigns params
        $loop  = $params['loop'];
        $start = $params['start'];
        $max   = $params['max'];
        $name  = $params['name'];
        $step  = $params['step'];
        $show  = $params['show'];

        // gets unique id
        $cnt = self::$cnt ++;

        $output .= '$this->globals[\'section\'][' . $name . '] = array();' . "\n" . '$_section' . $cnt . ' =& $this->globals[\'section\'][' . $name . '];' . "\n";

        if ($loop !== 'null') {
            $output .= '$_section' . $cnt . '[\'loop\'] = is_array($tmp = ' . $loop . ') ? count($tmp) : max(0, (int) $tmp);' . "\n";
        } else {
            $output .= '$_section' . $cnt . '[\'loop\'] = 1;' . "\n";
        }

        if ($show !== 'null') {
            $output .= '$_section' . $cnt . '[\'show\'] = ' . $show . ";\n";
        } else {
            $output .= '$_section' . $cnt . '[\'show\'] = true;' . "\n";
        }

        if ($name !== 'null') {
            $output .= '$_section' . $cnt . '[\'name\'] = ' . $name . ";\n";
        } else {
            $output .= '$_section' . $cnt . '[\'name\'] = true;' . "\n";
        }

        if ($max !== 'null') {
            $output .= '$_section' . $cnt . '[\'max\'] = (int)' . $max . ";\n" . 'if($_section' . $cnt . '[\'max\'] < 0) { $_section' . $cnt . '[\'max\'] = $_section' . $cnt . '[\'loop\']; }' . "\n";
        } else {
            $output .= '$_section' . $cnt . '[\'max\'] = $_section' . $cnt . '[\'loop\'];' . "\n";
        }

        if ($step !== 'null') {
            $output .= '$_section' . $cnt . '[\'step\'] = (int)' . $step . ' == 0 ? 1 : (int) ' . $step . ";\n";
        } else {
            $output .= '$_section' . $cnt . '[\'step\'] = 1;' . "\n";
        }

        if ($start !== 'null') {
            $output .= '$_section' . $cnt . '[\'start\'] = (int)' . $start . ";\n";
        } else {
            $output .= '$_section' . $cnt . '[\'start\'] = $_section' . $cnt . '[\'step\'] > 0 ? 0 : $_section' . $cnt . '[\'loop\'] - 1;' . "\n" . 'if ($_section' . $cnt . '[\'start\'] < 0) { $_section' . $cnt . '[\'start\'] = max($_section' . $cnt . '[\'step\'] > 0 ? 0 : -1, $_section' . $cnt . '[\'loop\'] + $_section' . $cnt . '[\'start\']); } ' . "\n" . 'else { $_section' . $cnt . '[\'start\'] = min($_section' . $cnt . '[\'start\'], $_section' . $cnt . '[\'step\'] > 0 ? $_section' . $cnt . '[\'loop\'] : $_section' . $cnt . '[\'loop\'] -1); }' . "\n";
        }

        /*		if ($usesAny) {
                    $output .= "\n".'$this->globals["section"]['.$name.'] = array'."\n(";
                    if ($usesIndex) $output .="\n\t".'"index"		=> 0,';
                    if ($usesIteration) $output .="\n\t".'"iteration"		=> 1,';
                    if ($usesFirst) $output .="\n\t".'"first"		=> null,';
                    if ($usesLast) $output .="\n\t".'"last"		=> null,';
                    if ($usesShow) $output .="\n\t".'"show"		=> ($this->isArray($_for'.$cnt.'_from, true)) || (is_numeric($_for'.$cnt.'_from) && $_for'.$cnt.'_from != $_for'.$cnt.'_to),';
                    if ($usesTotal) $output .="\n\t".'"total"		=> $this->isArray($_for'.$cnt.'_from) ? $this->count($_for'.$cnt.'_from) - $_for'.$cnt.'_skip : (is_numeric($_for'.$cnt.'_from) ? abs(($_for'.$cnt.'_to + 1 - $_for'.$cnt.'_from)/$_for'.$cnt.'_step) : 0),';
                    $out.="\n);\n".'$_section'.$cnt.'[\'glob\'] =& $this->globals["section"]['.$name.'];'."\n\n";
                }
        */

        $output .= 'if ($_section' . $cnt . '[\'show\']) {' . "\n";
        if ($start === 'null' && $step === 'null' && $max === 'null') {
            $output .= '	$_section' . $cnt . '[\'total\'] = $_section' . $cnt . '[\'loop\'];' . "\n";
        } else {
            $output .= '	$_section' . $cnt . '[\'total\'] = min(ceil(($_section' . $cnt . '[\'step\'] > 0 ? $_section' . $cnt . '[\'loop\'] - $_section' . $cnt . '[\'start\'] : $_section' . $cnt . '[\'start\'] + 1) / abs($_section' . $cnt . '[\'step\'])), $_section' . $cnt . '[\'max\']);' . "\n";
        }
        $output .= '	if ($_section' . $cnt . '[\'total\'] == 0) {' . "\n" . '		$_section' . $cnt . '[\'show\'] = false;' . "\n" . '	}' . "\n" . '} else {' . "\n" . '	$_section' . $cnt . '[\'total\'] = 0;' . "\n}\n";
        $output .= 'if ($_section' . $cnt . '[\'show\']) {' . "\n";
        $output .= "\t" . 'for ($this->scope[' . $name . '] = $_section' . $cnt . '[\'start\'], $_section' . $cnt . '[\'iteration\'] = 1; ' . '$_section' . $cnt . '[\'iteration\'] <= $_section' . $cnt . '[\'total\']; ' . '$this->scope[' . $name . '] += $_section' . $cnt . '[\'step\'], $_section' . $cnt . '[\'iteration\']++) {' . "\n";
        $output .= "\t\t" . '$_section' . $cnt . '[\'rownum\'] = $_section' . $cnt . '[\'iteration\'];' . "\n";
        $output .= "\t\t" . '$_section' . $cnt . '[\'index_prev\'] = $this->scope[' . $name . '] - $_section' . $cnt . '[\'step\'];' . "\n";
        $output .= "\t\t" . '$_section' . $cnt . '[\'index_next\'] = $this->scope[' . $name . '] + $_section' . $cnt . '[\'step\'];' . "\n";
        $output .= "\t\t" . '$_section' . $cnt . '[\'first\']      = ($_section' . $cnt . '[\'iteration\'] == 1);' . "\n";
        $output .= "\t\t" . '$_section' . $cnt . '[\'last\']       = ($_section' . $cnt . '[\'iteration\'] == $_section' . $cnt . '[\'total\']);' . "\n";

        $output .= Compiler::PHP_CLOSE . $content . Compiler::PHP_OPEN;

        $output .= "\n\t}\n} " . Compiler::PHP_CLOSE;

        if (isset($params['hasElse'])) {
            $output .= $params['hasElse'];
        }

        return $output;
    }
}
