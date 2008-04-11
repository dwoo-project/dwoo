<?php

include dirname(__FILE__).DIRECTORY_SEPARATOR . 'DwooInterfaces.php';
include dirname(__FILE__).DIRECTORY_SEPARATOR . 'DwooPlugin.php';
include dirname(__FILE__).DIRECTORY_SEPARATOR . 'DwooTemplateString.php';
include dirname(__FILE__).DIRECTORY_SEPARATOR . 'DwooTemplateFile.php';
include dirname(__FILE__).DIRECTORY_SEPARATOR . 'DwooData.php';

define('DWOO_DIRECTORY', dirname(__FILE__).DIRECTORY_SEPARATOR);
if(defined('DWOO_CACHE_DIRECTORY') === false)
	define('DWOO_CACHE_DIRECTORY', DWOO_DIRECTORY.'cache'.DIRECTORY_SEPARATOR);
if(defined('DWOO_COMPILE_DIRECTORY') === false)
	define('DWOO_COMPILE_DIRECTORY', DWOO_DIRECTORY.'compiled'.DIRECTORY_SEPARATOR);
if(is_writable(DWOO_CACHE_DIRECTORY) === false)
	throw new DwooException('Dwoo cache directory must be writable, either chmod "'.DWOO_CACHE_DIRECTORY.'" to make it writable or define DWOO_CACHE_DIRECTORY to a writable directory before including Dwoo.php');
if(is_writable(DWOO_COMPILE_DIRECTORY) === false)
	throw new DwooException('Dwoo compile directory must be writable, either chmod "'.DWOO_COMPILE_DIRECTORY.'" to make it writable or define DWOO_COMPILE_DIRECTORY to a writable directory before including Dwoo.php');

// include class paths or rebuild paths if the cache file isn't there
if((file_exists(DWOO_COMPILE_DIRECTORY.DIRECTORY_SEPARATOR.'classpath.cache.php') && include DWOO_COMPILE_DIRECTORY.DIRECTORY_SEPARATOR.'classpath.cache.php') === false)
	DwooLoader::rebuildClassPathCache(DWOO_DIRECTORY.'plugins', DWOO_COMPILE_DIRECTORY.DIRECTORY_SEPARATOR.'classpath.cache.php');

DwooLoader::loadPlugin('topLevelBlock');

/**
 * main dwoo class, allows communication between the compiler, template and data classes
 *
 * requirements :
 *  php 5.2.0 or above
 *  php's mbstring extension for some plugins
 *  php's hash extension to use DwooTemplateString class
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
class Dwoo
{
	/**
	 * current version number
	 *
	 * @var string
	 */
	const VERSION = "0.3.4";

	/**
	 * unique number of this dwoo release
	 *
	 * this can be used by templates classes to check whether the compiled template
	 * has been compiled before this release or not, so that old templates are
	 * recompiled automatically when Dwoo is updated
	 */
	const RELEASE_TAG = 8;

	/**#@+
	 * constants that represents all plugin types
	 *
	 * these are bitwise-operation-safe values to allow multiple types
	 * on a single plugin
	 *
	 * @var int
	 */
	const CLASS_PLUGIN = 1;
	const FUNC_PLUGIN = 2;
	const NATIVE_PLUGIN = 4;
	const BLOCK_PLUGIN = 8;
	const COMPILABLE_PLUGIN = 16;
	const CUSTOM_PLUGIN = 32;
	const SMARTY_MODIFIER = 64;
	const SMARTY_BLOCK = 128;
	const SMARTY_FUNCTION = 256;
	/**#@-*/

	/**
	 * character set of the template, used by string manipulation plugins
	 *
	 * it must be lowercase, but setCharset() will take care of that
	 *
	 * @see setCharset
	 * @see getCharset
	 * @var string
	 */
	protected $charset = 'utf-8';

	/**
	 * global variables that are accessible through $dwoo.* in the templates
	 *
	 * default values include:
	 *
	 * $dwoo.version - current version number
	 * $dwoo.ad - a Powered by Dwoo link pointing to dwoo.org
	 * $dwoo.now - the current time
	 * $dwoo.template - the current template filename
	 * $dwoo.charset - the character set used by the template
	 *
	 * on top of that, foreach and other plugins can store special values in there,
	 * see their documentation for more details.
	 *
	 * @var array
	 */
	protected $globals;

	/**
	 * directory where the compiled templates are stored
	 *
	 * defaults to DWOO_COMPILEDIR (= DWOO_DIRECTORY/compiled by default)
	 *
	 * @var string
	 */
	protected $compileDir;

	/**
	 * directory where the cached templates are stored
	 *
	 * defaults to DWOO_CACHEDIR (= DWOO_DIRECTORY/cache by default)
	 *
	 * @var string
	 */
	protected $cacheDir;

	/**
	 * defines how long (in seconds) the cached files must remain valid
	 *
	 * can be overriden on a per-template basis
	 *
	 * -1 = never delete
	 * 0 = disabled
	 * >0 = duration in seconds
	 *
	 * @var int
	 */
	protected $cacheTime = 0;

	/**
	 * security policy object
	 *
	 * @var DwooSecurityPolicy
	 */
	protected $securityPolicy = null;

	/**
	 * stores the custom plugins callbacks
	 *
	 * @see addPlugin
	 * @see removePlugin
	 * @var array
	 */
	protected $plugins = array();

	/**
	 * stores the filter callbacks
	 *
	 * @see addFilter
	 * @see removeFilter
	 * @var array
	 */
	protected $filters = array();

	/**
	 * stores the resource types and associated
	 * classes / compiler classes
	 *
	 * @var array
	 */
	protected $resources = array
	(
		'file'		=>	array
		(
			'class'		=>	'DwooTemplateFile',
			'compiler'	=>	null
		),
		'string'	=>	array
		(
			'class'		=>	'DwooTemplateString',
			'compiler'	=>	null
		)
	);

	/**
	 * currently rendered template, set to null when not-rendering
	 *
	 * @var DwooITemplate
	 */
	protected $template = null;

	/**
	 * stores the instances of the class plugins during template runtime
	 *
	 * @var array
	 */
	protected $runtimePlugins;

	/**
	 * stores the data during template runtime
	 *
	 * @var array
	 */
	protected $data;

	/**
	 * stores the current scope during template runtime
	 *
	 * @var mixed
	 */
	protected $scope;

	/**
	 * stores the scope tree during template runtime
	 *
	 * @var array
	 */
	protected $scopeTree;

	/**
	 * stores the block plugins stack during template runtime
	 *
	 * @var array
	 */
	protected $stack;

	/**
	 * stores the current block plugin at the top of the stack during template runtime
	 *
	 * @var DwooBlockPlugin
	 */
	protected $curBlock;

	/**
	 * stores the output buffer during template runtime
	 *
	 * @var string
	 */
	protected $buffer;

	/**
	 * constructor, sets the cache and compile dir to the default values
	 */
	public function __construct()
	{
		$this->cacheDir = DWOO_CACHE_DIRECTORY.DIRECTORY_SEPARATOR;
		$this->compileDir = DWOO_COMPILE_DIRECTORY.DIRECTORY_SEPARATOR;
	}

	/**
	 * resets some runtime variables to allow a cloned object to be used to render sub-templates
	 */
	public function __clone()
	{
		$this->template = null;
		unset($this->scope);
		unset($this->data);
	}

	/**
	 * outputs the template instead of returning it, this is basically a shortcut for get(*, *, *, true)
	 * @see get
	 */
	public function output($tpl, $data = array(), DwooICompiler $compiler = null)
	{
		return $this->get($tpl, $data, $compiler, true);
	}

	/**
	 * returns the given template rendered using the provided data and optional compiler
	 *
	 * @param mixed $tpl template, can either be a DwooITemplate object (i.e. DwooTemplateFile), a valid path to a template, or
	 * 					 a template as a string it is recommended to provide a DwooITemplate as it will probably make things faster,
	 * 					 especially if you render a template multiple times
	 * @param mixed $data the data to use, can either be a DwooIDataProvider object (i.e. DwooData) or an associative array. if you're
	 * 					  rendering the template from cache, it can be left null
	 * @param DwooICompiler $compiler the compiler that must be used to compile the template, if left empty a default
	 * 								  DwooCompiler will be used.
	 * @param bool $output flag that defines whether the function returns the output of the template (false, default) or echoes it directly (true)
	 * @return string nothing or the template output if $output is true
	 */
	public function get($tpl, $data = array(), $compiler = null, $output = false)
	{
		// a render call came from within a template, so we need a new dwoo instance in order to avoid breaking this one
		if($this->template instanceof DwooITemplate)
		{
			$proxy = clone $this;
			return $proxy->get($tpl, $data, $compiler, $output);
		}

		// auto-create template if required
		if($tpl instanceof DwooITemplate)
		{}
		elseif(is_string($tpl) && file_exists($tpl))
			$tpl = new DwooTemplateFile($tpl);
		elseif(is_string($tpl))
			$tpl = new DwooTemplateString($tpl);
		else
			throw new DwooException('Dwoo->get/Dwoo->output\'s first argument must be a DwooITemplate (i.e. DwooTemplateFile) or a valid path to a template file', E_USER_NOTICE);

		$this->template = $tpl;

		// load data
		if($data instanceof DwooIDataProvider)
			$this->data = $data->getData();
		elseif(is_array($data))
			$this->data = $data;
		else
			throw new DwooException('Dwoo->get/Dwoo->output\'s data argument must be a DwooIDataProvider object (i.e. DwooData) or an associative array', E_USER_NOTICE);

		$this->initGlobals($tpl);
		$this->initRuntimeVars($tpl);

		// try to get cached template
		$file = $tpl->getCachedTemplate($this);
		$doCache = $file === true;
		$cacheLoaded = $doCache === false && is_string($file);

		// cache is present, run it
		if($cacheLoaded === true)
		{
			if($output === true)
			{
				include $file;
				$this->template = null;
			}
			else
			{
				ob_start();
				include $file;
				$this->template = null;
				return ob_get_clean();
			}
		}
		// no cache present
		else
		{
			$file = $tpl->getCompiledTemplate($this, $compiler);

			// building cache
			if($doCache)
			{
				$out = include $file;

				foreach($this->filters as $filter)
				{
					if(is_array($filter) && $filter[0] instanceof DwooFilter)
						$out = call_user_func($filter, $out);
					else
						$out = call_user_func($filter, $this, $out);
				}

				$this->template = null;
				$tpl->cache($this, $out);
				if($output === true)
					echo $out;
				else
					return $out;
			}
			// no need to build cache
			else
			{
				$out = include $file;
				$this->template = null;

				foreach($this->filters as $filter)
				{
					if(is_array($filter) && $filter[0] instanceof DwooFilter)
						$out = call_user_func($filter, $out);
					else
						$out = call_user_func($filter, $this, $out);
				}

				if($output === true)
					echo $out;
				else
					return $out;
			}
		}
	}

	/**
	 * re-initializes the globals array before each template run
	 *
	 * @param DwooITemplate $tpl the template that is going to be rendered
	 */
	protected function initGlobals(DwooITemplate $tpl)
	{
		$this->globals = array
		(
			'version'	=>	self::VERSION,
			'ad'		=>	'<a href="http://dwoo.org/">Powered by Dwoo</a>',
			'now'		=>	$_SERVER['REQUEST_TIME'],
			'template'	=>	$tpl->getName(),
			'charset'	=>	$this->charset,
			//'config'	=>	$this->config,
		);
	}

	/**
	 * re-initializes the runtime variables before each template run
	 *
	 * @param DwooITemplate $tpl the template that is going to be rendered
	 */
	protected function initRuntimeVars(DwooITemplate $tpl)
	{
		$this->runtimePlugins = array();
		$this->scope =& $this->data;
		$this->scopeTree = array();
		$this->stack = array();
		$this->curBlock = null;
		$this->buffer = '';
	}

	/*
	 * --------- settings functions ---------
	 */

	/**
	 * adds a custom plugin that is not in one of the plugin directories
	 *
	 * @param string $name the plugin name to be used in the templates
	 * @param callback $callback the plugin callback, either a function name,
	 * 							 a class name or an array containing an object
	 * 							 or class name and a method name
	 */
	public function addPlugin($name, $callback)
	{
		if(is_array($callback))
		{
			if(is_subclass_of(is_object($callback[0]) ? get_class($callback[0]) : $callback[0], 'DwooBlockPlugin'))
				$this->plugins[$name] = array('type'=>self::BLOCK_PLUGIN, 'callback'=>$callback, 'class'=>(is_object($callback[0]) ? get_class($callback[0]) : $callback[0]));
			else
				$this->plugins[$name] = array('type'=>self::CLASS_PLUGIN, 'callback'=>$callback, 'class'=>(is_object($callback[0]) ? get_class($callback[0]) : $callback[0]), 'function'=>$callback[1]);
		}
		elseif(class_exists($callback, false))
		{
			 if(is_subclass_of($callback, 'DwooBlockPlugin'))
				$this->plugins[$name] = array('type'=>self::BLOCK_PLUGIN, 'callback'=>$callback, 'class'=>$callback);
			else
				$this->plugins[$name] = array('type'=>self::CLASS_PLUGIN, 'callback'=>$callback, 'class'=>$callback, 'function'=>'process');
		 }
		elseif(function_exists($callback))
		{
			$this->plugins[$name] = array('type'=>self::FUNC_PLUGIN, 'callback'=>$callback);
		}
		else
		{
			throw new DwooException('Callback could not be processed correctly, please check that the function/class you used exists');
		}
	}

	/**
	 * removes a custom plugin
	 *
	 * @param string $name the plugin name
	 */
	public function removePlugin($name)
	{
		if(isset($this->plugins[$name]))
			unset($this->plugins[$name]);
	}

	/**
	 * adds a filter to this Dwoo instance, it will be used to filter the output of all the templates rendered by this instance
	 *
	 * @param mixed $callback a callback or a filter name if it is autoloaded from a plugin directory
	 * @param bool $autoload if true, the first parameter must be a filter name from one of the plugin directories
	 */
	public function addFilter($callback, $autoload = false)
	{
		if($autoload)
		{
			$name = str_replace('DwooFilter_', '', $callback);
			$class = 'DwooFilter_'.$name;

			if(!class_exists($class, false) && !function_exists($class))
				DwooLoader::loadPlugin($name);

			if(class_exists($class, false))
				$callback = array(new $class($this), 'process');
			elseif(function_exists($class))
				$callback = $class;
			else
				$this->triggerError('Wrong filter name, when using autoload the filter must be in one of your plugin dir as "name.php" containg a class or function named "DwooFilter_name"', E_USER_ERROR);

			$this->filters[] = $callback;
		}
		else
		{
			$this->filters[] = $callback;
		}
	}

	/**
	 * removes a filter
	 *
	 * @param mixed $callback callback or filter name if it was autoloaded
	 */
	public function removeFilter($callback)
	{
		if(($index = array_search($callback, $this->filters, true)) !== false)
			unset($this->filters[$index]);
		elseif(($index = array_search('DwooFilter_'.str_replace('DwooFilter_', '', $callback), $this->filters, true)) !== false)
			unset($this->filters[$index]);
		else
		{
			$class = 'DwooFilter_' . str_replace('DwooFilter_', '', $callback);
			foreach($this->filters as $index=>$filter)
			{
				if(is_array($filter) && $filter[0] instanceof $class)
				{
					unset($this->filters[$index]);
					break;
				}
			}
		}
	}

	/**
	 * adds a resource or overrides a default one
	 *
	 * @param string $name the resource name
	 * @param string $class the resource class (which must implement DwooITemplate)
	 * @param callback $compilerFactory the compiler factory callback, a function that must return a compiler instance used to compile this resource, if none is provided. by default it will produce a DwooCompiler object
	 */
	public function addResource($name, $class, $compilerFactory = null)
	{
		if(strlen($name) < 2)
			throw new DwooException('Resource names must be at least two-character long to avoid conflicts with Windows paths');

		$interfaces = class_implements($class, false);
		if(in_array('DwooITemplate', $interfaces) === false)
			throw new DwooException('Resource class must implement DwooITemplate');

		$this->resources[$name] = array('class'=>$class, 'compiler'=>$compilerFactory);
	}

	/**
	 * removes a custom resource
	 *
	 * @param string $name the resource name
	 */
	public function removeResource($name)
	{
		unset($this->resources[$name]);
		if($name==='file')
			$this->resources['file'] = array('class'=>'DwooTemplateFile', 'compiler'=>null);
	}

/*    public function addConfig(array $config)
	{
		// TODO build
	}

	public function loadConfig($name, $section = null, $scope = 'global')
	{
		// TODO check
		$config = parse_ini_file($this->configDir . $name .'.conf', true);

		switch($scope)
		{
			case 'global':
				$this->config = array_merge($config, $this->config);
				if($this->template !== null)
					$this->globals['config'] = array_merge($config, $this->globals['config']);
				break;
			case 'local':
				$this->config = array_merge($config, $this->config);
				$this->globals['config'] = array_merge($config, $this->globals['config']);
				break;
			case 'parent':
				$this->config = array_merge($config, $this->config);
				if($this->parent !== null)
					$this->parent->addConfig($config);
				break;
			default:
				$this->triggerError('scope must be "global", "parent" or "local", '.$scope.' provided.', E_USER_ERROR);
		}
	}*/

	/*
	 * --------- getters and setters ---------
	 */

	/**
	 * returns the custom plugins loaded
	 *
	 * used by the DwooITemplate classes to pass the custom plugins to their DwooICompiler instance
	 *
	 * @return array
	 */
	public function getCustomPlugins()
	{
		return $this->plugins;
	}

	/**
	 * returns the cache directory with a trailing DIRECTORY_SEPARATOR
	 *
	 * @return string
	 */
	public function getCacheDir()
	{
		return $this->cacheDir;
	}

	/**
	 * sets the cache directory and automatically appends a DIRECTORY_SEPARATOR
	 *
	 * @param string $dir the cache directory
	 */
	public function setCacheDir($dir)
	{
		$this->cacheDir = rtrim($dir, '/\\').DIRECTORY_SEPARATOR;
	}

	/**
	 * returns the compile directory with a trailing DIRECTORY_SEPARATOR
	 *
	 * @return string
	 */
	public function getCompileDir()
	{
		return $this->compileDir;
	}

	/**
	 * sets the compile directory and automatically appends a DIRECTORY_SEPARATOR
	 *
	 * @param string $dir the compile directory
	 */
	public function setCompileDir($dir)
	{
		$this->compileDir = rtrim($dir, '/\\').DIRECTORY_SEPARATOR;
	}

	/**
	 * returns the default cache time that is used with templates that do not have a cache time set
	 *
	 * @return int the duration in seconds
	 */
	public function getCacheTime()
	{
		return $this->cacheTime;
	}

	/**
	 * sets the default cache time to use with templates that do not have a cache time set
	 *
	 * @param int $seconds the duration in seconds
	 */
	public function setCacheTime($seconds)
	{
		$this->cacheTime = (int) $seconds;
	}

	/**
	 * returns the character set used by the string manipulation plugins
	 *
	 * @return string
	 */
	public function getCharset()
	{
		return $this->charset;
	}

	/**
	 * sets the character set used by the string manipulation plugins
	 *
	 * the charset will be automatically lowercased
	 *
	 * @param string $charset the character set
	 */
	public function setCharset($charset)
	{
		$this->charset = strtolower((string) $charset);
	}

	/**
	 * returns the current template being rendered, if applicable, or null
	 *
	 * @return DwooITemplate|null
	 */
	public function getCurrentTemplate()
	{
		return $this->template;
	}

	/**
	 * sets the default compiler factory function for the given resource name
	 *
	 * a compiler factory must return a DwooICompiler object pre-configured to fit your needs
	 *
	 * @param string $resourceName the resource name (i.e. file, string)
	 * @param callback $compilerFactory the compiler factory callback
	 */
	public function setDefaultCompilerFactory($resourceName, $compilerFactory)
	{
		$this->resources[$resourceName]['compiler'] = $compilerFactory;
	}

	/**
	 * returns the default compiler factory function for the given resource name
	 *
	 * @param string $resourceName the resource name
	 * @return callback the compiler factory callback
	 */
	public function getDefaultCompilerFactory($resourceName)
	{
		return $this->resources[$resourceName]['compiler'];
	}

	/**
	 * sets the security policy object to enforce some php security settings
	 *
	 * use this if untrusted persons can modify templates
	 *
	 * @param DwooSecurityPolicy $policy the security policy object
	 */
	public function setSecurityPolicy(DwooSecurityPolicy $policy = null)
	{
		$this->securityPolicy = $policy;
	}

	/**
	 * returns the current security policy object or null by default
	 *
	 * @return DwooSecurityPolicy|null the security policy object if any
	 */
	public function getSecurityPolicy()
	{
		return $this->securityPolicy;
	}

	/*
	 * --------- util functions ---------
	 */

	/**
	 * [util function] checks whether the given template is cached or not
	 *
	 * @param DwooITemplate $tpl the template object
	 * @return bool
	 */
	public function isCached(DwooITemplate $tpl)
	{
		return is_string($tpl->getCachedTemplate($this));
	}

	/**
	 * [util function] clears the cached templates if they are older than the given time
	 *
	 * @param int $olderThan minimum time (in seconds) required for a cached template to be cleared
	 * @return int the amount of templates cleared
	 */
	public function clearCache($olderThan=0)
	   {
		   $cacheDirs = new RecursiveDirectoryIterator($this->cacheDir);
		   $cache = new RecursiveIteratorIterator($cacheDirs);
		   $expired = time() - $olderThan;
		   $count = 0;
		   foreach($cache as $file)
		   {
			   if($cache->isDot() || $cache->isDir())
				   continue;
			   if($cache->getCTime() < $expired)
				   $count += unlink((string) $file) ? 1 : 0;
		   }
		   return $count;
	   }

	   /**
		* [util function] fetches a template object of the given resource
		*
		* @param string $resourceName the resource name (i.e. file, string)
		* @param string $resourceId the resource identifier (i.e. file path)
		* @param int $cacheTime the cache time setting for this resource
		* @param string $cacheId the unique cache identifier
		* @param string $compileId the unique compiler identifier
		* @return DwooITemplate
		*/
	public function getTemplate($resourceName, $resourceId, $cacheTime = null, $cacheId = null, $compileId = null)
	{
		if(isset($this->resources[$resourceName]))
			return call_user_func(array($this->resources[$resourceName]['class'], 'templateFactory'), $this, $resourceId, $cacheTime, $cacheId, $compileId);
		else
			throw new DwooException('Unknown resource type : '.$resourceName);
	}

	/**
	 * [util function] checks if the input is an array or an iterator object, optionally it can also check if it's empty
	 *
	 * @param mixed $value the variable to check
	 * @param bool $checkIsEmpty if true, the function will also check if the array is empty
	 * @param bool $allowNonCountable if true, the function will return true if an object is not empty but does not
	 * 			   implement Countable, by default a non-countable object is considered empty
	 * @return bool true if it's an array (and not empty) or false if it's not an array (or if it's empty)
	 */
	public function isArray($value, $checkIsEmpty=false, $allowNonCountable=false)
	{
		if(is_array($value) === true)
		{
			if($checkIsEmpty)
				return count($value) > 0;
			else
				return true;
		}
		elseif($value instanceof Iterator)
		{
			if($checkIsEmpty)
			{
				if($allowNonCountable)
				{
					if($value instanceof Countable)
						return count($value) > 0;
					else
					{
						$value->rewind();
						return $value->valid();
					}
				}
				else
					return count($value) > 0;
			}
			else
				return true;
		}
		return false;
	}

	/**
	 * [util function] triggers a dwoo error
	 *
	 * @param string $message the error message
	 * @param int $level the error level, one of the PHP's E_* constants
	 */
	public function triggerError($message, $level=E_USER_NOTICE)
	{
		trigger_error('Dwoo error: '.$message, $level);
	}

	/*
	 * --------- runtime functions ---------
	 */

	/**
	 * [runtime function] adds a block to the block stack
	 *
	 * @param string $blockName the block name (without DwooPlugin_ prefix)
	 * @param array $args the arguments to be passed to the block's init() function
	 * @return DwooBlockPlugin the newly created block
	 */
	public function addStack($blockName, array $args=array())
	{
		if(isset($this->plugins[$blockName]))
			$class = $this->plugins[$blockName]['class'];
		else
			$class = 'DwooPlugin_'.$blockName;

		if($this->curBlock !== null)
		{
			$this->curBlock->buffer(ob_get_contents());
			ob_clean();
		}
		else
		{
			$this->buffer .= ob_get_contents();
			ob_clean();
		}

		$block = new $class($this);

		$cnt = count($args);
		if($cnt===0)
			$block->init();
		elseif($cnt===1)
			$block->init($args[0]);
		elseif($cnt===2)
			$block->init($args[0], $args[1]);
		elseif($cnt===3)
			$block->init($args[0], $args[1], $args[2]);
		elseif($cnt===4)
			$block->init($args[0], $args[1], $args[2], $args[3]);
		else
			call_user_func_array(array($block,'init'), $args);

		$this->stack[] = $this->curBlock = $block;
		return $block;
	}

	/**
	 * [runtime function] removes the plugin at the top of the block stack
	 *
	 * calls the block buffer() function, followed by a call to end()
	 * and finally a call to process()
	 */
	public function delStack()
	{
		$args = func_get_args();

		$this->curBlock->buffer(ob_get_contents());
		ob_clean();

		$cnt = count($args);
		if($cnt===0)
			$this->curBlock->end();
		elseif($cnt===1)
			$this->curBlock->end($args[0]);
		elseif($cnt===2)
			$this->curBlock->end($args[0], $args[1]);
		elseif($cnt===3)
			$this->curBlock->end($args[0], $args[1], $args[2]);
		elseif($cnt===4)
			$this->curBlock->end($args[0], $args[1], $args[2], $args[3]);
		else
			call_user_func_array(array($this->curBlock, 'end'), $args);

		$tmp = array_pop($this->stack);

		if(count($this->stack) > 0)
		{
			$this->curBlock = end($this->stack);
			$this->curBlock->buffer($tmp->process());
		}
		else
		{
			echo $tmp->process();
		}

		unset($tmp);
	}

	/**
	 * [runtime function] returns the parent block of the given block
	 *
	 * @param DwooBlockPlugin $block
	 * @return DwooBlockPlugin or false if the given block isn't in the stack
	 */
	public function getParentBlock(DwooBlockPlugin $block)
	{
		$index = array_search($block, $this->stack, true);
		if($index !== false && $index > 0)
		{
			return $this->stack[$index-1];
		}
		return false;
	}

	/**
	 * [runtime function] finds the closest block of the given type, starting at the top of the stack
	 *
	 * @param string $type the type of plugin you want to find
	 * @return DwooBlockPlugin or false if no plugin of such type is in the stack
	 */
	public function findBlock($type)
	{
		if(isset($this->plugins[$type]))
			$type = $this->plugins[$type]['class'];
		else
			$type = 'DwooPlugin_'.str_replace('DwooPlugin_','',$type);

		$keys = array_keys($this->stack);
		while(($key = array_pop($keys)) !== false)
			if($this->stack[$key] instanceof $type)
				return $this->stack[$key];
		return false;
	}

	/**
	 * [runtime function] returns a DwooPlugin of the given class
	 *
	 * this is so a single instance of every class plugin is created at each template run,
	 * allowing class plugins to have "per-template-run" static variables
	 *
	 * @param string $class the class name
	 * @return mixed an object of the given class
	 */
	protected function getObjectPlugin($class)
	{
		if(isset($this->runtimePlugins[$class]))
			return $this->runtimePlugins[$class];
		return $this->runtimePlugins[$class] = new $class($this);
	}

	/**
	 * [runtime function] calls the process() method of the given class-plugin name
	 *
	 * @param string $plugName the class plugin name (without DwooPlugin_ prefix)
	 * @param array $params an array of parameters to send to the process() method
	 * @return string the process() return value
	 */
	public function classCall($plugName, array $params = array())
	{
		$class = 'DwooPlugin_'.$plugName;

		$plugin = $this->getObjectPlugin($class);

		$cnt = count($params);
		if($cnt===0)
			return $plugin->process();
		elseif($cnt===1)
			return $plugin->process($params[0]);
		elseif($cnt===2)
			return $plugin->process($params[0], $params[1]);
		elseif($cnt===3)
			return $plugin->process($params[0], $params[1], $params[2]);
		elseif($cnt===4)
			return $plugin->process($params[0], $params[1], $params[2], $params[3]);
		else
			return call_user_func_array(array($plugin, 'process'), $params);
	}

	/**
	 * [runtime function] calls a php function
	 *
	 * @param string $callback the function to call
	 * @param array $params an array of parameters to send to the function
	 * @return mixed the return value of the called function
	 */
	public function arrayMap($callback, array $params)
	{
		if($params[0] === $this)
		{
			$addThis = true;
			array_shift($params);
		}
		if((is_array($params[0]) || ($params[0] instanceof Iterator && $params[0] instanceof ArrayAccess)))
		{
			if(empty($params[0]))
				return $params[0];

			// array map
			$out = array();
			$cnt = count($params);

			if(isset($addThis))
			{
				array_unshift($params, $this);
				$items = $params[1];
				$keys = array_keys($items);

				if(is_string($callback) === false)
					while(($i = array_shift($keys)) !== null)
						$out[] = call_user_func_array($callback, array(1=>$items[$i]) + $params);
				elseif($cnt===1)
					while(($i = array_shift($keys)) !== null)
						$out[] = $callback($this, $items[$i]);
				elseif($cnt===2)
					while(($i = array_shift($keys)) !== null)
						$out[] = $callback($this, $items[$i], $params[2]);
				elseif($cnt===3)
					while(($i = array_shift($keys)) !== null)
						$out[] = $callback($this, $items[$i], $params[2], $params[3]);
				else
					while(($i = array_shift($keys)) !== null)
						$out[] = call_user_func_array($callback, array(1=>$items[$i]) + $params);
			}
			else
			{
				$items = $params[0];
				$keys = array_keys($items);

				if(is_string($callback) === false)
					while(($i = array_shift($keys)) !== null)
						$out[] = call_user_func_array($callback, array($items[$i]) + $params);
				elseif($cnt===1)
					while(($i = array_shift($keys)) !== null)
						$out[] = $callback($items[$i]);
				elseif($cnt===2)
					while(($i = array_shift($keys)) !== null)
						$out[] = $callback($items[$i], $params[1]);
				elseif($cnt===3)
					while(($i = array_shift($keys)) !== null)
						$out[] = $callback($items[$i], $params[1], $params[2]);
				elseif($cnt===4)
					while(($i = array_shift($keys)) !== null)
						$out[] = $callback($items[$i], $params[1], $params[2], $params[3]);
				else
					while(($i = array_shift($keys)) !== null)
						$out[] = call_user_func_array($callback, array($items[$i]) + $params);
			}
			return $out;
		}
		else
		{
			return $params[0];
		}
	}

	/**
	 * [runtime function] reads a variable into the given data array
	 *
	 * @param string $varstr the variable string, using dwoo variable syntax (i.e. "var.subvar[subsubvar]->property")
	 * @param mixed $data the data array or object to read from
	 * @return mixed
	 */
	public function readVarInto($varstr, $data)
	{
		if($data === null)
			return null;

		if(is_array($varstr) === false)
			preg_match_all('#(\[|->|\.)?([a-z0-9_]+)\]?#i', $varstr, $m);
		else
			$m = $varstr;
		unset($varstr);

		while(list($k, $sep) = each($m[1]))
		{
			if($sep === '.' || $sep === '[' || $sep === '')
			{
				if((is_array($data) || ($data instanceof Iterator && $data instanceof ArrayAccess)) && isset($data[$m[2][$k]]))
					$data = $data[$m[2][$k]];
				else
					return null;
			}
			else
			{
				if(is_object($data) && property_exists($data, $m[2][$k]))
					$data = $data->$m[2][$k];
				else
					return null;
			}
		}

		return $data;
	}

	/**
	 * [runtime function] reads a variable into the parent scope
	 *
	 * @param int $parentLevels the amount of parent levels to go from the current scope
	 * @param string $varstr the variable string, using dwoo variable syntax (i.e. "var.subvar[subsubvar]->property")
	 * @return mixed
	 */
	public function readParentVar($parentLevels, $varstr = null)
	{
		$tree = $this->scopeTree;
		$cur = $this->data;

		while($parentLevels--!==0)
		{
			array_pop($tree);
		}

		while(($i = array_shift($tree)) !== null)
		{
			if(is_object($cur))
				$cur = $cur->$i;
			else
				$cur = $cur[$i];
		}

		if($varstr!==null)
			return $this->readVarInto($varstr, $cur);
		else
			return $cur;
	}

	/**
	 * [runtime function] reads a variable into the current scope
	 *
	 * @param string $varstr the variable string, using dwoo variable syntax (i.e. "var.subvar[subsubvar]->property")
	 * @return mixed
	 */
	public function readVar($varstr)
	{
		if(is_array($varstr)===true)
		{
			$m = $varstr;
			unset($varstr);
		}
		else
		{
			if(strstr($varstr, '.') === false && strstr($varstr, '[') === false && strstr($varstr, '->') === false)
			{
				if($varstr === 'dwoo')
				{
					return $this->globals;
				}
				elseif($varstr === '_root')
				{
					return $this->data;
					$varstr = substr($varstr, 6);
				}
				elseif($varstr === '_parent')
				{
					$varstr = '.'.$varstr;
					$tree = $this->scopeTree;
					$cur = $this->data;
					array_pop($tree);

					while(($i = array_shift($tree)) !== null)
					{
						if(is_object($cur))
							$cur = $cur->$i;
						else
							$cur = $cur[$i];
					}

					return $cur;
				}

				$cur = $this->scope;

				if(isset($cur[$varstr]))
					return $cur[$varstr];
				else
					return null;
			}

			preg_match_all('#(\[|->|\.)?([a-z0-9_]+)\]?#i', $varstr, $m);
		}

		$i = $m[2][0];
		if($i === 'dwoo')
		{
			$cur = $this->globals;
			array_shift($m[2]);
			array_shift($m[1]);
			switch($m[2][0])
			{
				case 'get':
					$cur = $_GET;
					break;
				case 'post':
					$cur = $_POST;
					break;
				case 'session':
					$cur = $_SESSION;
					break;
				case 'cookies':
				case 'cookie':
					$cur = $_COOKIE;
					break;
				case 'server':
					$cur = $_SERVER;
					break;
				case 'env':
					$cur = $_ENV;
					break;
				case 'request':
					$cur = $_REQUEST;
					break;
				case 'const':
					array_shift($m[2]);
					if(defined($m[2][0]))
						return constant($m[2][0]);
					else
						return null;
			}
			if($cur !== $this->globals)
			{
				array_shift($m[2]);
				array_shift($m[1]);
			}
		}
		elseif($i === '_root')
		{
			$cur = $this->data;
			array_shift($m[2]);
			array_shift($m[1]);
		}
		elseif($i === '_parent')
		{
			$tree = $this->scopeTree;
			$cur = $this->data;

			while(true)
			{
				array_pop($tree);
				array_shift($m[2]);
				array_shift($m[1]);
				if(current($m[2]) === '_parent')
					continue;

				while(($i = array_shift($tree)) !== null)
				{
					if(is_object($cur))
						$cur = $cur->$i;
					else
						$cur = $cur[$i];
				}
				break;
			}
		}
		else
			$cur = $this->scope;

		while(list($k, $sep) = each($m[1]))
		{
			if($sep === '.' || $sep === '[' || $sep === '')
			{
				if((is_array($cur) || ($cur instanceof Iterator && $cur instanceof ArrayAccess)) && isset($cur[$m[2][$k]]))
					$cur = $cur[$m[2][$k]];
				else
					return null;
			}
			elseif($sep === '->')
			{
				if(is_object($cur) && property_exists($cur, $m[2][$k]))
					$cur = $cur->$m[2][$k];
				else
					return null;
			}
			else
				return null;
		}

		return $cur;
	}

	/**
	 * [runtime function] assign the value to the given variable
	 *
	 * @param mixed $value the value to assign
	 * @param string $scope the variable string, using dwoo variable syntax (i.e. "var.subvar[subsubvar]->property")
	 * @return bool true if assigned correctly or false if a problem occured while parsing the var string
	 */
	public function assignInScope($value, $scope)
	{
		$tree =& $this->scopeTree;
		$data =& $this->data;

		if(strstr($scope, '.') === false && strstr($scope, '->') === false)
		{
			$this->scope[$scope] = $value;
		}
		else
		{
			// TODO handle _root/_parent scopes ?
			preg_match_all('#(\[|->|\.)?([a-z0-9_]+)\]?#i', $scope, $m);

			$cur =& $this->scope;
			$last = array(array_pop($m[1]), array_pop($m[2]));

			while(list($k, $sep) = each($m[1]))
			{
				if($sep === '.' || $sep === '[' || $sep === '')
				{
					if(is_array($cur) === false)
						$cur = array();
					$cur =& $cur[$m[2][$k]];
				}
				elseif($sep === '->')
				{
					if(is_object($cur) === false)
						$cur = new stdClass;
					$cur =& $cur->$m[2][$k];
				}
				else
					return false;
			}

			if($last[0] === '.' || $last[0] === '[' || $last[0] === '')
			{
				if(is_array($cur) === false)
					$cur = array();
				$cur[$last[1]] = $value;
			}
			elseif($last[0] === '->')
			{
				if(is_object($cur) === false)
					$cur = new stdClass;
				$cur->$last[1] = $value;
			}
			else
				return false;
		}
	}

	/**
	 * [runtime function] sets the scope to the given scope string
	 *
	 * @param mixed $scope a string i.e. "level1.level2" or an array i.e. array("level1", "level2")
	 * @return array the current scope
	 */
	public function setScope($scope)
	{
		$old = $this->scopeTree;

		if(empty($scope))
			return $old;

		if(is_array($scope)===false)
			$scope = explode('.', $scope);

		while($bit = array_shift($scope))
		{
			if($bit === '_parent')
			{
				array_pop($this->scopeTree);
				reset($this->scopeTree);
				$this->scope =& $this->data;
				$cnt = count($this->scopeTree);
				for($i=0;$i<$cnt;$i++)
					$this->scope =& $this->scope[$this->scopeTree[$i]];
			}
			elseif($bit === '_root')
			{
				$this->scope =& $this->data;
				$this->scopeTree = array();
			}
			elseif(isset($this->scope[$bit]))
			{
				$this->scope =& $this->scope[$bit];
				$this->scopeTree[] = $bit;
			}
			else
			{
				unset($this->scope);
				$this->scope = null;
			}
		}

		return $old;
	}

	/**
	 * [runtime function] returns the entire data array
	 *
	 * @return array
	 */
	public function getData()
	{
		return $this->data;
	}

	/**
	 * [runtime function] returns a reference to the current scope
	 *
	 * @return &mixed
	 */
	public function &getScope()
	{
		return $this->scope;
	}

	/**
	 * [runtime function] forces an absolute scope
	 *
	 * @see setScope
	 * @param mixed $scope a scope as a string or array
	 * @return array the current scope tree
	 */
	public function forceScope($scope)
	{
		$prev = $this->setScope(array('_root'));
		$this->setScope($scope);
		return $prev;
	}
}

/**
 * handles plugin loading and caching of plugins names/paths relationships
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
class DwooLoader
{
	/**
	 * stores the plugin directories
	 *
	 * @see addDirectory
	 * @var array
	 */
	protected static $paths = array();

	/**
	 * stores the plugins names/paths relationships
	 * don't edit this on your own, use addDirectory
	 *
	 * @see addDirectory
	 * @var array
	 * @access protected
	 */
	public static $classpath = array();

	/**
	 * rebuilds class paths, scans the given directory recursively and saves all paths in the given file
	 *
	 * @param string $path the plugin path to scan
	 * @param string $cacheFile the file where to store the plugin paths cache, it will be overwritten
	 * @access protected
	 */
	public static function rebuildClassPathCache($path, $cacheFile)
	{
		if($cacheFile!==false)
		{
			$tmp = self::$classpath;
			self::$classpath = array();
		}

		// iterates over all files/folders
		$list = glob($path.DIRECTORY_SEPARATOR.'*');
		if(is_array($list))
			foreach($list as $f)
			{
				if(is_dir($f))
					self::rebuildClassPathCache($f, false);
				elseif(basename($f) !== 'classpath.cache')
					self::$classpath[str_replace(array('function.','block.','modifier.','outputfilter.','filter.','prefilter.','postfilter.','pre.','post.','output.','shared.','helper.'), '', basename($f,'.php'))] = $f;
			}

		// save in file if it's the first call (not recursed)
		if($cacheFile!==false)
		{
			if(!file_put_contents($cacheFile, '<?php DwooLoader::$classpath = '.var_export(self::$classpath, true).' + DwooLoader::$classpath; ?>'))
				throw new DwooException('Could not write into '.$cacheFile.', either because the folder is not there (create it) or because of the chmod configuration (please ensure this directory is writable by php)');
			self::$classpath += $tmp;
		}
	}

	/**
	 * loads a plugin file
	 *
	 * @param string $class the plugin name, without the DwooPlugin_ prefix
	 */
	public static function loadPlugin($class)
	{
		// a new class was added or the include failed so we rebuild the cache
		if(!isset(self::$classpath[$class]) || !include self::$classpath[$class])
		{
			self::rebuildClassPathCache(DWOO_DIRECTORY . 'plugins', DWOO_COMPILE_DIRECTORY . DIRECTORY_SEPARATOR . 'classpath.cache.php');
			foreach(self::$paths as $path)
				self::rebuildClassPathCache($path[0], $path[1]);
			if(isset(self::$classpath[$class]))
				include self::$classpath[$class];
			else
				throw new DwooException('Plugin <em>'.$class.'</em> can not be found, maybe you forgot to bind it if it\'s a custom plugin ?', E_USER_NOTICE);
		}
	}

	/**
	 * adds a plugin directory
	 *
	 * @param string $pluginDir the plugin path to scan
	 */
	public static function addDirectory($pluginDir)
	{
		$cacheFile = DWOO_COMPILE_DIRECTORY . DIRECTORY_SEPARATOR . 'classpath-'.substr(strtr($pluginDir, ':/\\.', '----'), strlen($pluginDir) > 80 ? -80 : 0).'.cache.php';
		self::$paths[] = array($pluginDir, $cacheFile);
		if(file_exists($cacheFile))
			include $cacheFile;
		else
			DwooLoader::rebuildClassPathCache($pluginDir, $cacheFile);
	}
}

/**
 * main dwoo exception class
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
class DwooException extends Exception
{
}

/**
 * represents the security settings of a dwoo instance, it can be passed around to different dwoo instances
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
class DwooSecurityPolicy
{
	/**#@+
	 * php handling constants, defaults to PHP_REMOVE
	 *
	 * PHP_REMOVE : remove all <?php ?> (+ short tags if your short tags option is on) from the input template
	 * PHP_ALLOW : leave them as they are
	 * PHP_ENCODE : run htmlentities over them
	 *
	 * @var int
	 */
	const PHP_ENCODE = 1;
	const PHP_REMOVE = 2;
	const PHP_ALLOW = 3;
	/**#@-*/

	/**#@+
	 * constant handling constants, defaults to CONST_DISALLOW
	 *
	 * CONST_DISALLOW : throw an error if {$dwoo.const.*} is used in the template
	 * CONST_ALLOW : allow {$dwoo.const.*} calls
	 */
	const CONST_DISALLOW = false;
	const CONST_ALLOW = true;
	/**#@-*/

	/**
	 * php functions that are allowed to be used within the template
	 *
	 * @var array
	 */
	protected $allowedPhpFunctions = array
	(
		'str_repeat', 'count', 'number_format', 'htmlentities', 'htmlspecialchars',
		'long2ip', 'strlen', 'list', 'empty', 'count', 'sizeof', 'in_array', 'is_array',
	);

	/**
	 * paths that are safe to use with include or other file-access plugins
	 *
	 * @var array
	 */
	protected $allowedDirectories = array();

	/**
	 * stores the php handling level
	 *
	 * defaults to DwooSecurityPolicy::PHP_REMOVE
	 *
	 * @var int
	 */
	protected $phpHandling = self::PHP_REMOVE;

	/**
	 * stores the constant handling level
	 *
	 * defaults to DwooSecurityPolicy::CONST_DISALLOW
	 *
	 * @var bool
	 */
	protected $constHandling = self::CONST_DISALLOW;

	/**
	 * adds a php function to the allowed list
	 *
	 * @param mixed $func function name or array of function names
	 */
	public function allowPhpFunction($func)
	{
		if(is_array($func))
			foreach($func as $fname)
				$this->allowedPhpFunctions[strtolower($fname)] = true;
		else
			$this->allowedPhpFunctions[strtolower($func)] = true;
	}

	/**
	 * removes a php function from the allowed list
	 *
	 * @param mixed $func function name or array of function names
	 */
	public function disallowPhpFunction($func)
	{
		if(is_array($func))
			foreach($func as $fname)
				unset($this->allowedPhpFunctions[strtolower($fname)]);
		else
			unset($this->allowedPhpFunctions[strtolower($func)]);
	}

	/**
	 * returns the list of php functions allowed to run, note that the function names
	 * are stored in the array keys and not values
	 *
	 * @return array
	 */
	public function getAllowedPhpFunctions()
	{
		return $this->allowedPhpFunctions;
	}

	/**
	 * adds a directory to the safelist for includes and other file-access plugins
	 *
	 * @param mixed $path a path name or an array of paths
	 */
	public function allowDirectory($path)
	{
		if(is_array($path))
			foreach($path as $dir)
				$this->allowedDirectories[realpath($dir)] = true;
		else
			$this->allowedDirectories[realpath($path)] = true;
	}

	/**
	 * removes a directory from the safelist
	 *
	 * @param mixed $path a path name or an array of paths
	 */
	public function disallowDirectory($path)
	{
		if(is_array($path))
			foreach($path as $dir)
				unset($this->allowedDirectories[realpath($dir)]);
		else
			unset($this->allowedDirectories[realpath($path)]);
	}

	/**
	 * returns the list of safe paths, note that the paths are stored in the array
	 * keys and not values
	 *
	 * @return array
	 */
	public function getAllowedDirectories()
	{
		return $this->allowedPHPFunc;
	}

	/**
	 * sets the php handling level, defaults to REMOVE
	 *
	 * @param int $level one of the DwooSecurityPolicy::PHP_* constants
	 */
	public function setPhpHandling($level = self::PHP_REMOVE)
	{
		$this->phpHandling = $level;
	}

	/**
	 * returns the php handling level
	 *
	 * @return int the current level, one of the DwooSecurityPolicy::PHP_* constants
	 */
	public function getPhpHandling()
	{
		return $this->phpHandling;
	}

	/**
	 * sets the constant handling level, defaults to CONST_DISALLOW
	 *
	 * @param bool $level one of the DwooSecurityPolicy::CONST_* constants
	 */
	public function setConstantHandling($level = self::CONST_DISALLOW)
	{
		$this->constHandling = $level;
	}

	/**
	 * returns the constant handling level
	 *
	 * @return bool the current level, one of the DwooSecurityPolicy::CONST_* constants
	 */
	public function getConstantHandling()
	{
		return $this->constHandling;
	}
}

?>