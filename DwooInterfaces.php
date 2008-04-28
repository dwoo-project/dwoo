<?php

/**
 * interface that represents a dwoo data object
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
 * @version    0.3.4
 * @date       2008-04-09
 * @package    Dwoo
 */
interface DwooIDataProvider
{
	/**
	 * returns the data as an associative array that will be used in the template
	 *
	 * @return array
	 */
	public function getData();
}

/**
 * interface that represents a dwoo template
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
 * @version    0.3.4
 * @date       2008-04-09
 * @package    Dwoo
 */
interface DwooITemplate
{
	/**
	 * returns the cache duration for this template
	 *
	 * defaults to null if it was not provided
	 *
	 * @return int|null
	 */
	public function getCacheTime();

	/**
	 * returns the cached template output file name, true if it's cache-able but not cached
	 * or false if it's not cached
	 *
	 * @param Dwoo $dwoo the dwoo instance that requests it
	 * @return string|bool
	 */
	public function getCachedTemplate(Dwoo $dwoo);

	/**
	 * caches the provided output into the cache file
	 *
	 * @param Dwoo $dwoo the dwoo instance that requests it
	 * @param string $output the template output
	 */
	public function cache(Dwoo $dwoo, $output);

	/**
	 * clears the cached template if it's older than the given time
	 *
	 * @param Dwoo $dwoo the dwoo instance that was used to cache that template
	 * @param int $olderThan minimum time (in seconds) required for the cache to be cleared
	 * @return bool true if the cache was not present or if it was deleted, false if it remains there
	 */
	public function clearCache(Dwoo $dwoo, $olderThan = -1);

	/**
	 * returns the compiled template file name
	 *
	 * @param Dwoo $dwoo the dwoo instance that requests it
	 * @param DwooICompiler $compiler the compiler that must be used
	 * @return string
	 */
	public function getCompiledTemplate(Dwoo $dwoo, DwooICompiler $compiler = null);

	/**
	 * returns the template name
	 *
	 * @return string
	 */
	public function getName();

	/**
	 * returns the resource name for this template class
	 *
	 * @return string
	 */
	public function getResourceName();

	/**
	 * returns the resource identifier for this template or false if it has no identifier
	 *
	 * @return string|false
	 */
	public function getResourceIdentifier();

	/**
	 * returns the template source of this template
	 *
	 * @return string
	 */
	public function getSource();

	/**
	 * returns an unique string identifying the current version of this template,
	 * for example a timestamp of the last modified date or a hash of the template source
	 *
	 * @return string
	 */
	public function getUid();

	/**
	 * returns the compiler used by this template, if it was just compiled, or null
	 *
	 * @return DwooICompiler
	 */
	public function getCompiler();

	/**
	 * returns a new template object from the given include name, null if no include is
	 * possible (resource not found), or false if include is not permitted by this resource type
	 *
	 * @param mixed $resourceId the resource identifier
	 * @param int $cacheTime duration of the cache validity for this template,
	 * 						 if null it defaults to the Dwoo instance that will
	 * 						 render this template
	 * @param string $cacheId the unique cache identifier of this page or anything else that
	 * 						  makes this template's content unique, if null it defaults
	 * 						  to the current url
	 * @param string $compileId the unique compiled identifier, which is used to distinguish this
	 * 							template from others, if null it defaults to the filename+bits of the path
	 * @return DwooITemplate|null|false
	 */
	public static function templateFactory(Dwoo $dwoo, $resourceId, $cacheTime = null, $cacheId = null, $compileId = null);
}

/**
 * interface that represents a dwoo compiler
 *
 * while implementing this is enough to interact with Dwoo/DwooTemplates, it is not
 * sufficient to interact with DwooPlugins, however the main purpose of creating a
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
 * @version    0.3.4
 * @date       2008-04-09
 * @package    Dwoo
 */
interface DwooICompiler
{
	/**
	 * compiles the provided string down to php code
	 *
	 * @param string $templateStr the template to compile
	 * @return string a compiled php code string
	 */
	public function compile(Dwoo $dwoo, DwooITemplate $template);

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
	 * @param DwooSecurityPolicy $policy the security policy object
	 */
	public function setSecurityPolicy(DwooSecurityPolicy $policy = null);
}

?>
