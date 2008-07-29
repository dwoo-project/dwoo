<?php

/**
 * interface that represents a dwoo plugin proxy
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * This file is released under the LGPL
 * "GNU Lesser General Public License"
 * More information can be found here:
 * {@link http://www.gnu.org/copyleft/lesser.html}
 *
 * @author     Denis Arh <denis@arh.cc>
 * @copyright  Copyright (c) 2008, Denis Arh (this file + some patched bits here and there)
 * @license    http://www.gnu.org/copyleft/lesser.html  GNU Lesser General Public License
 * @link       http://dwoo.org/
 * @version    0.9.1
 * @date       2008-05-30
 * @package    Dwoo
 */
interface Dwoo_IPluginProxy
{
	/**
	 * loads a plugin or returns false on failure
	 *
	 * @param string $name the plugin name
	 * @return bool true if the plugin was successfully loaded
	 */
    public function loadPlugin($name);
}