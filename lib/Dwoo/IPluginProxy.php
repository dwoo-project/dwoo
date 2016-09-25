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
 * Interface that represents a dwoo plugin proxy.
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
interface IPluginProxy
{
    /**
     * Returns true or false to say whether the given plugin is handled by this proxy or not.
     *
     * @param string $name the plugin name
     *
     * @return bool true if the plugin is known and usable, otherwise false
     */
    public function handles($name);

    /**
     * Returns the code (as a string) to call the plugin
     * (this will be executed at runtime inside the Dwoo class).
     *
     * @param string $name   the plugin name
     * @param array  $params a parameter array, array key "*" is the rest array
     *
     * @return string
     */
    public function getCode($name, $params);

    /**
     * Returns a callback to the plugin, this is used with the reflection API to
     * find out about the plugin's parameter names etc.
     * should you need a rest array without the possibility to edit the
     * plugin's code, you can provide a callback to some
     * other function with the correct parameter signature, i.e. :
     * <code>
     * return array($this, "callbackHelper");
     * // and callbackHelper would be as such:
     * public function callbackHelper(array $rest=array()){}
     * </code>
     *
     * @param string $name the plugin name
     *
     * @return callback
     */
    public function getCallback($name);

    /**
     * Returns some code that will check if the plugin is loaded and if not load it
     * this is optional, if your plugins are autoloaded or whatever, just return an
     * empty string.
     *
     * @param string $name the plugin name
     *
     * @return string
     */
    public function getLoader($name);
}
