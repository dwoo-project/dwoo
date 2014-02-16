<?php
namespace Dwoo;

/**
 * interface for dwoo plugin loaders
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-01
 * @package    Dwoo
 */
interface ILoader {
	/**
	 * loads a plugin file
	 * the second parameter is used to avoid permanent rehashing when using php functions,
	 * however this means that if you have add a plugin that overrides a php function you have
	 * to delete the classpath.cache file(s) by hand to force a rehash of the plugins
	 * @param string $class       the plugin name, without the Plugin prefix
	 * @param bool   $forceRehash if true, the class path caches will be rebuilt if the plugin is not found, in case it has just been added, defaults to true
	 */
	public function loadPlugin($class, $forceRehash = true);
}
