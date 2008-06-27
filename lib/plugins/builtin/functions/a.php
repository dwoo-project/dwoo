<?php

/**
 * Outputs a html &lt;a&gt; tag
 * <pre>
 *  * href : the target URI where the link must point
 *  * text : text to display, if not provided the href parameter will be used
 *  * rest : any other attributes you want to add to the tag can be added as named parameters
 * </pre>
 *
 * Example :
 *
 * <code>
 * {* Create a simple link out of an url variable and add a special class attribute: *}
 *
 * {$url|a:class="external"}
 *
 * {* Mark a link as active depending on some other variable : *}
 *
 * {a $link.url $link.title class=tif($link.active "active")} {* This is similar to: <a href="{$link.url}" class="{if $link.active}active{/if}">{$link.title}</a> *}
 * </code>
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * This file is released under the LGPL
 * "GNU Lesser General Public License"
 * More information can be found here:
 * {@link http://www.gnu.org/copyleft/lesser.html}
 *
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @copyright  Copyright (c) 2008, Jordi Boggiano
 * @license    http://www.gnu.org/copyleft/lesser.html  GNU Lesser General Public License
 * @link       http://dwoo.org/
 * @version    0.9.1
 * @date       2008-05-30
 * @package    Dwoo
 */
function Dwoo_Plugin_a_compile(Dwoo_Compiler $compiler, $href, $text=null, array $rest=array())
{
    if ($text=='null') {
        $text = $href;
    }
    $out = '\'<a href="\'.'.$href.'.\'"';
    foreach ($rest as $attr=>$val) {
        if (trim($val, '"\'')=='' || $val=='null') {
            continue;
        }
        $out .= ' '.$attr.'="\'.'.$val.'.\'"';
    }
    return $out . '>\'.'.$text.'.\'</a>\'';
}
