<?php
/**
 * Copyright (c) 2013-2016
 *
 * @category  Library
 * @package   Dwoo
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2016 David Sanchez
 * @license   http://dwoo.org/LICENSE Modified BSD License
 * @version   1.3.0
 * @date      2016-09-23
 * @link      http://dwoo.org/
 */

namespace Dwoo;

/**
 * Base plugin class.
 * you have to implement the <em>process()</em> method, it will receive the parameters that
 * are in the template code
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
abstract class Plugin
{
    /**
     * The dwoo instance that runs this plugin.
     *
     * @var Core
     */
    protected $core;

    /**
     * Constructor, if you override it, call parent::__construct($core); or assign
     * the dwoo instance yourself if you need it.
     *
     * @param Core $core the dwoo instance that runs this plugin
     */
    public function __construct(Core $core)
    {
        $this->core = $core;
    }

    // plugins should always implement :
    // public function process($arg, $arg, ...)
    // or for block plugins :
    // public function init($arg, $arg, ...)

    // this could be enforced with :
    // abstract public function process(...);
    // if my feature request gets enough interest one day
    // see => http://bugs.php.net/bug.php?id=44043

    /**
     * Utility function that converts an array of compiled parameters (or rest array) to a string of xml/html tag
     * attributes. this is to be used in preProcessing or postProcessing functions, example :
     *  $p = $compiler->getCompiledParams($params);
     *  // get only the rest array as attributes
     *  $attributes = Plugin::paramsToAttributes($p['*']);
     *  // get all the parameters as attributes (if there is a rest array, it will be included)
     *  $attributes = Plugin::paramsToAttributes($p);
     *
     * @param array    $params   an array of attributeName=>value items that will be compiled to be ready for inclusion in a php string
     *                                inclusion in a php string
     * @param string   $delim    the string delimiter you want to use (defaults to ')
     * @param Compiler $compiler the compiler instance (optional for BC, but recommended to pass it for proper escaping behavior)
     *                                escaping behavior)
     *
     * @return string
     */
    public static function paramsToAttributes(array $params, $delim = '\'', Compiler $compiler = null)
    {
        if (isset($params['*'])) {
            $params = array_merge($params, $params['*']);
            unset($params['*']);
        }

        $out = '';
        foreach ($params as $attr => $val) {
            $out .= ' ' . $attr . '=';
            if (trim($val, '"\'') == '' || $val == 'null') {
                $out .= str_replace($delim, '\\' . $delim, '""');
            } elseif (substr($val, 0, 1) === $delim && substr($val, - 1) === $delim) {
                $out .= str_replace($delim, '\\' . $delim, '"' . substr($val, 1, - 1) . '"');
            } else {
                if (!$compiler) {
                    // disable double encoding since it can not be determined if it was encoded
                    $escapedVal = '.(is_string($tmp2=' . $val . ') ? htmlspecialchars($tmp2, ENT_QUOTES, $this->charset, false) : $tmp2).';
                } elseif (!$compiler->getAutoEscape() || false === strpos($val, 'isset($this->scope')) {
                    // escape if auto escaping is disabled, or there was no variable in the string
                    $escapedVal = '.(is_string($tmp2=' . $val . ') ? htmlspecialchars($tmp2, ENT_QUOTES, $this->charset) : $tmp2).';
                } else {
                    // print as is
                    $escapedVal = '.' . $val . '.';
                }

                $out .= str_replace($delim, '\\' . $delim, '"') . $delim . $escapedVal . $delim . str_replace($delim, '\\' . $delim, '"');
            }
        }

        return ltrim($out);
    }
}
