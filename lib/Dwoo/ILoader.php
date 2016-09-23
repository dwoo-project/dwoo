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
 * Interface for dwoo plugin loaders.
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
interface ILoader
{
    /**
     * Loads a plugin file.
     * the second parameter is used to avoid permanent rehashing when using php functions,
     * however this means that if you have add a plugin that overrides a php function you have
     * to delete the classpath.cache file(s) by hand to force a rehash of the plugins
     *
     * @param string $class       the plugin name, without the `Plugin` prefix
     * @param bool   $forceRehash if true, the class path caches will be rebuilt if the plugin is not found, in case it
     *                            has just been added, defaults to true
     */
    public function loadPlugin($class, $forceRehash = true);
}
