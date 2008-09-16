<?php

/**
 * <strong>Agavi specific plugin</strong>
 *
 * uses AgaviRouting to create an url
 *
 * <pre>
 *  * route : the route name, optional (by default the current url is returned)
 *  * params : an array with variables to build the route, optional
 *  * options : an array of options to pass to the routing object, optional
 *  * rest : for convenience, you can just pass named parameters that will be used as
 *           the params array, but you must not provide the params array in this case
 * </pre>
 *
 * Examples:
 * <code>
 * {a url("route.name" array(param="Value", param2=$otherVal))}Here is a link{/a}
 * <form action="{url}"> {* without any parameter it just returns the current url *}
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
 * @version    1.0.0
 * @date       2008-09-08
 * @package    Dwoo
 */
function Dwoo_Plugin_url_compile(Dwoo_Compiler $compiler, $route = null, $params = null, $options = null, array $rest = array())
{
	if ($params == 'null') {
		if (count($rest)) {
			$params = array();
			foreach ($rest as $k=>$v) {
				if (is_numeric($k)) {
					$out[] = $k.'=>'.$v;
				} else {
					$out[] = '"'.$k.'"=>'.$v;
				}
			}
			$params = 'array('.implode(', ', $params).')';
		} else {
			$params = 'array()';
		}
	}
	if ($options == 'null') {
		$options = 'array()';
	}
	return '$this->data[\'ro\']->gen('.$route.', '.$params.', '.$options.')';
}