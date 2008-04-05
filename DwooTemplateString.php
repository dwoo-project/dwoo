<?php

/**
 * represents a Dwoo template contained in a string
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
 * @version    0.3.3
 * @date       2008-03-19
 * @package    Dwoo
 */
class DwooTemplateString implements DwooITemplate
{
	/**
	 * template name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * template compilation id
	 *
	 * @var string
	 */
	protected $compileId;

	/**
	 * template cache id, if not provided in the constructor, it is set to
	 * the md5 hash of the request_uri. it is however highly recommended to
	 * provide one that will fit your needs.
	 *
	 * in all cases, the compilation id is prepended to the cache id to separate
	 * templates with similar cache ids from one another
	 *
	 * @var string
	 */
	protected $cacheId;

	/**
	 * validity duration of the generated cache file (in seconds)
	 *
	 * set to -1 for infinite cache, 0 to disable and null to inherit the Dwoo instance's cache time
	 *
	 * @var int
	 */
	protected $cacheTime;

	/**
	 * boolean flag that defines whether the compilation should be enforced (once) or
	 * not use this if you have issues with the compiled templates not being updated
	 * but if you do need this it's most likely that you should file a bug report
	 *
	 * @var bool
	 */
	protected $compilationEnforced;

	/**
	 * caches the results of the file checks to save some time when the same
	 * templates is rendered several times
	 *
	 * @var array
	 */
	protected static $cache = array('cached'=>array(), 'compiled'=>array());

	/**
	 * holds the compiler that built this template
	 *
	 * @var DwooICompiler
	 */
	protected $compiler;

	/**
	 * creates a template from a string
	 *
	 * @param string $templateString the template to use
	 * @param int $cacheTime duration of the cache validity for this template,
	 * 						 if null it defaults to the Dwoo instance that will
	 * 						 render this template, set to -1 for infinite cache or 0 to disable
	 * @param string $cacheId the unique cache identifier of this page or anything else that
	 * 						  makes this template's content unique, if null it defaults
	 * 						  to the current url
	 * @param string $compileId the unique compiled identifier, which is used to distinguish this
	 * 							template from others, if null it defaults to the md5 hash of the template
	 */
	public function __construct($templateString, $cacheTime = null, $cacheId = null, $compileId = null)
	{
		$this->template = $templateString;
		$this->name = bin2hex(md5($templateString, true));
		$this->cacheTime = $cacheTime;

		// no compile id provided, set it to an md5 hash of the template
		if($compileId === null)
		{
			$compileId = $this->name;
		}
		$this->compileId = $compileId;

		// no cache id provided, use request_uri
		if($cacheId === null)
		{
			$cacheId = bin2hex(md5($_SERVER['REQUEST_URI'], true));
		}
		$this->cacheId = $this->compileId . $cacheId;
	}

	/**
	 * returns the cache duration for this template
	 *
	 * defaults to null if it was not provided
	 *
	 * @return int|null
	 */
	public function getCacheTime()
	{
		return $this->cacheTime;
	}

	/**
	 * returns the template name
	 *
	 * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * returns the resource name for this template class
	 *
	 * @return string
	 */
	public function getResourceName()
	{
		return 'string';
	}

	/**
	 * returns the compiler used by this template, if it was just compiled, or null
	 *
	 * @return DwooICompiler
	 */
	public function getCompiler()
	{
		return $this->compiler;
	}

	/**
	 * marks this template as compile-forced, which means it will be recompiled even if it
	 * was already saved and wasn't modified since the last compilation. do not use this in production,
	 * it's only meant to be used in development (and the development of dwoo particularly)
	 */
	public function forceCompilation()
	{
		$this->compilationEnforced = true;
	}

	/**
	 * returns the cached template output file name, true if it's cache-able but not cached
	 * or false if it's not cached
	 *
	 * @param Dwoo $dwoo the dwoo instance that requests it
	 * @return string|bool
	 */
	public function getCachedTemplate(Dwoo $dwoo)
	{
		$cachedFile = $dwoo->getCacheDir() . $this->cacheId.'.dwoo';
		if($this->cacheTime !== null)
			$cacheLength = $this->cacheTime;
		else
			$cacheLength = $dwoo->getCacheTime();

		// file is not cacheable
		if($cacheLength === 0)
		{
			return false;
		}
		// already checked, return cache file
		if(isset(self::$cache['cached'][$this->cacheId]) === true)
		{
			return $cachedFile;
		}
		// cache is still valid and can be loaded
		elseif($this->compilationEnforced !== true && file_exists($cachedFile) && ($cacheLength === -1 || filemtime($cachedFile) > ($_SERVER['REQUEST_TIME'] - $cacheLength)))
		{
			self::$cache['cached'][$this->cacheId] = true;
			return $cachedFile;
		}
		// file is cacheable
		else
		{
			return true;
		}
	}

	/**
	 * caches the provided output into the cache file
	 *
	 * @param Dwoo $dwoo the dwoo instance that requests it
	 * @param string $output the template output
	 */
	public function cache(Dwoo $dwoo, $output)
	{
		$cachedFile = $dwoo->getCacheDir() . $this->cacheId.'.dwoo';

		file_put_contents($cachedFile, $output);
		touch($cachedFile, $_SERVER['REQUEST_TIME']);

		self::$cache['cached'][$this->cacheId] = true;
	}

	/**
	 * clears the cached template if it's older than the given time
	 *
	 * @param int $olderThan minimum time (in seconds) required for the cache to be cleared
	 * @return bool true if the cache was not present or if it was deleted, false if it remains there
	 */
	public function clearCache($olderThan=0)
	{
		$cachedFile = $dwoo->getCacheDir() . $this->cacheId.'.dwoo';

		return !file_exists($cachedFile) || (filectime($cachedFile) < (time() - $olderThan) && unlink($cachedFile));
	}

	/**
	 * returns the compiled template file name
	 *
	 * @param Dwoo $dwoo the dwoo instance that requests it
	 * @param DwooICompiler $compiler the compiler that must be used
	 * @return string
	 */
	public function getCompiledTemplate(Dwoo $dwoo, DwooICompiler $compiler = null)
	{
		$compiledFile = $dwoo->getCompileDir() . $this->compileId.'.'.Dwoo::RELEASE_TAG.'.dwoo';

		// already checked, return compiled file
		if($this->compilationEnforced !== true && isset(self::$cache['compiled'][$this->compileId]) === true)
		{
		}
		// template is compiled
		elseif($this->compilationEnforced !== true && file_exists($compiledFile)===true)
		{
			self::$cache['compiled'][$this->compileId] = true;
		}
		// compiles the template
		else
		{
			$this->compilationEnforced = false;

			if($compiler === null)
			{
				$compiler = $dwoo->getDefaultCompilerFactory('string');

				if($compiler === null || $compiler === array('DwooCompiler', 'compilerFactory'))
				{
					if(class_exists('DwooCompiler', false) === false)
						include DWOO_DIR . 'DwooCompiler.php';
					$compiler = DwooCompiler::compilerFactory();
				}
				else
					$compiler = call_user_func($compiler);
			}

			$this->compiler = $compiler;

			$compiler->setCustomPlugins($dwoo->getCustomPlugins());
			$compiler->setSecurityPolicy($dwoo->getSecurityPolicy());
			file_put_contents($compiledFile, $compiler->compile($this->template));
			touch($compiledFile, $_SERVER['REQUEST_TIME']);

			self::$cache['compiled'][$this->compileId] = true;
		}

		return $compiledFile;
	}

	/**
	 * returns false as this template type does not support inclusions
	 *
	 * @param mixed $resourceId the filename (relative to this template's dir) of the template to include
	 * @param int $cacheTime duration of the cache validity for this template,
	 * 						 if null it defaults to the Dwoo instance that will
	 * 						 render this template
	 * @param string $cacheId the unique cache identifier of this page or anything else that
	 * 						  makes this template's content unique, if null it defaults
	 * 						  to the current url
	 * @param string $compileId the unique compiled identifier, which is used to distinguish this
	 * 							template from others, if null it defaults to the filename+bits of the path
	 * @return false
	 */
	public static function templateFactory(Dwoo $dwoo, $resourceId, $cacheTime = null, $cacheId = null, $compileId = null)
	{
		return false;
	}
}

?>