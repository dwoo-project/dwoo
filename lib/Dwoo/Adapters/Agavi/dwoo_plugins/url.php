<?php

/**
 * Copyright (c) 2013-2016
 *
 * @category  Library
 * @package   Dwoo\Adapters\Agavi\dwoo_plugins
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2016 David Sanchez
 * @license   http://dwoo.org/LICENSE Modified BSD License
 * @version   1.3.0
 * @date      2016-09-19
 * @link      http://dwoo.org/
 */
function Dwoo_Plugin_url_compile(Dwoo_Compiler $compiler, $route = null, $params = null, $options = null, array $rest = array())
{
    if ($params == 'null') {
        if (count($rest)) {
            $params = array();
            foreach ($rest as $k => $v) {
                if (is_numeric($k)) {
                    $params[] = $k.'=>'.$v;
                } else {
                    $params[] = '"'.$k.'"=>'.$v;
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
