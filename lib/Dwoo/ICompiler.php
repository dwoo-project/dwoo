<?php

/**
 * interface that represents a dwoo compiler
 *
 * while implementing this is enough to interact with Dwoo/Dwoo_Templates, it is not
 * sufficient to interact with Dwoo_Plugins, however the main purpose of creating a
 * new compiler would be to interact with other/different plugins, that is why this
 * interface has been left with the minimum requirements.
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
interface Dwoo_ICompiler
{
	/**
	 * compiles the provided string down to php code
	 *
	 * @param string $templateStr the template to compile
	 * @return string a compiled php code string
	 */
	public function compile(Dwoo $dwoo, Dwoo_ITemplate $template);

	/**
	 * adds the custom plugins loaded into Dwoo to the compiler so it can load them
	 *
	 * @see Dwoo::addPlugin
	 * @param array $customPlugins an array of custom plugins
	 */
	public function setCustomPlugins(array $customPlugins);

	/**
	 * sets the security policy object to enforce some php security settings
	 *
	 * use this if untrusted persons can modify templates,
	 * set it on the Dwoo object as it will be passed onto the compiler automatically
	 *
	 * @param Dwoo_Security_Policy $policy the security policy object
	 */
	public function setSecurityPolicy(Dwoo_Security_Policy $policy = null);
}
