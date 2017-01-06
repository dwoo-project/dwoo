<?php
/**
 * Copyright (c) 2013-2017
 *
 * @category  Library
 * @package   Dwoo\Plugins\Functions
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2017 David Sanchez
 * @license   http://dwoo.org/LICENSE Modified BSD License
 * @version   1.3.2
 * @date      2017-01-06
 * @link      http://dwoo.org/
 */

namespace Dwoo\Plugins\Functions;

use Dwoo\Core;
use Dwoo\Plugin;
use Dwoo\Template\Str as TemplateString;

/**
 * Evaluates the given string as if it was a template.
 * Although this plugin is kind of optimized and will
 * not recompile your string each time, it is still not
 * a good practice to use it. If you want to have templates
 * stored in a database or something you should probably use
 * the Dwoo\Template\Str class or make another class that
 * extends it
 * <pre>
 *  * var : the string to use as a template
 *  * assign : if set, the output of the template will be saved in this variable instead of being output
 * </pre>
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class PluginEval extends Plugin
{
    /**
     * @param string $var
     * @param null   $assign
     *
     * @return string
     */
    public function process($var, $assign = null)
    {
        if ($var == '') {
            return '';
        }

        $tpl   = new TemplateString($var);
        $clone = clone $this->core;
        $out   = $clone->get($tpl, $this->core->readVar('_parent'));

        if ($assign !== null) {
            $this->core->assignInScope($out, $assign);
        } else {
            return $out;
        }
    }
}