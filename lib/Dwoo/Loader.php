<?php
namespace Dwoo;
use Dwoo\Exception\PluginException;

/**
 * handles plugin loading and caching of plugins names/paths relationships
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2014-02-27
 * @package    Dwoo
 */
class Loader implements ILoader {

	/**
	 * stores the plugin directories
	 * @see addDirectory
	 * @var array
	 */
	protected $paths = array();

	/**
	 * stores the plugins names/paths relationships
	 * don't edit this on your own, use addDirectory
	 * @see addDirectory
	 * @var array
	 */
	protected $classPath = array();

	/**
	 * path where class paths cache files are written
	 * @var string
	 */
	protected $cacheDir;

	/**
	 * Plugin directory
	 * @var string
	 */
	protected $corePluginDir;

	/**
	 * Core object
	 * @var Core
	 */
	protected $core;

	/**
	 * Constructor, take Core object as param
	 * @param Core $core
	 */
	public function __construct(Core $core) {
		$this->core          = $core;
		$this->corePluginDir = Core::DWOO_DIRECTORY . DIRECTORY_SEPARATOR . 'Plugins';
		$this->cacheDir      = rtrim($core->getCompileDir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

		// include class paths or rebuild paths if the cache file isn't there
		$cacheFile = $this->cacheDir . 'classpath.cache.d' . Core::RELEASE_TAG . '.php';
		if (file_exists($cacheFile)) {
			$classpath       = file_get_contents($cacheFile);
			$this->classPath = unserialize($classpath) + $this->classPath;
		}
		else {
			$this->rebuildClassPathCache($this->corePluginDir, $cacheFile);
		}
	}

	/**
	 * Get class path values
	 * @return array|mixed
	 */
	public function getClassPath() {
		return $this->classPath;
	}

	/**
	 * loads a plugin file
	 * @param string $class       the plugin name, without the prefix (Block|block|Function|function)
	 * @param bool   $forceRehash if true, the class path caches will be rebuilt if the plugin is not found, in case it has just been added, defaults to true
	 * @throws Exception
	 * @return bool
	 */
	public function loadPlugin($class, $forceRehash = true) {
		// An unknown class was requested (maybe newly added) or the
		// include failed so we rebuild the cache. include() will fail
		// with an uncatchable error if the file doesn't exist, which
		// usually means that the cache is stale and must be rebuilt,
		// so we check for that before trying to include() the plugin.

		// Check entry exist in $this->classPath
		$match = preg_grep('/^(Block|Function)?(' . $class . '+)/i', array_keys($this->classPath));
		$index = array_values($match);

		// Entry doesn't exist, try to rebuild cache
		$included_files = get_included_files();
		if (!isset($index[0]) || !isset($this->classPath[$index[0]]) || !is_readable($this->classPath[$index[0]]) || !in_array($this->classPath[$index[0]], $included_files)) {
			if ($forceRehash) {

				// Rebuild cache
				$this->rebuildClassPathCache($this->corePluginDir, $this->cacheDir . 'classpath.cache.d' . Core::RELEASE_TAG . '.php');
				foreach ($this->paths as $path => $file) {
					$this->rebuildClassPathCache($path, $file);
				}

				// Check entry exist after rebuilding cache
				$match = preg_grep('/^(Block|Function)?(' . $class . '+)/i', array_keys($this->classPath));
				$index = array_values($match);
				if (isset($index[0])) {
					if (isset($this->classPath[$index[0]])) {
						include_once $this->classPath[$index[0]];
						return true;
					}
				}
			}

			throw new Exception(sprintf('Plugin <em>%s</em> can not be found, maybe you forgot to bind it if it\'s a custom plugin ?', $class), E_USER_NOTICE);
		}
		return false;
	}

	/**
	 * adds a plugin directory, the plugins found in the new plugin directory
	 * will take precedence over the other directories (including the default
	 * dwoo plugin directory), you can use this for example to override plugins
	 * in a specific directory for a specific application while keeping all your
	 * usual plugins in the same place for all applications.
	 * TOCOM don't forget that php functions overrides are not rehashed so you
	 * need to clear the classpath caches by hand when adding those
	 * @param string $pluginDirectory the plugin path to scan
	 * @return $this
	 * @throws Exception
	 */
	public function addDirectory($pluginDirectory) {
		if (! is_dir($pluginDirectory)) {
			throw new Exception('Plugin directory does not exist or can not be read : ' . $pluginDirectory);
		}
		$cacheFile               = $this->cacheDir . 'classpath-' . substr(strtr($pluginDirectory, '/\\:' . PATH_SEPARATOR, '----'), strlen($pluginDirectory) > 80 ? - 80 : 0) . '.d' . Core::RELEASE_TAG . '.php';
		$this->paths[$pluginDirectory] = $cacheFile;
		if (file_exists($cacheFile)) {
			$classpath       = file_get_contents($cacheFile);
			$this->classPath = unserialize($classpath) + $this->classPath;
		}
		else {
			$this->rebuildClassPathCache($pluginDirectory, $cacheFile);
		}

		return $this;
	}

	/**
	 * rebuilds class paths, scans the given directory recursively and saves all paths in the given file
	 *
	 * @param string $path      the plugin path to scan
	 * @param string $cacheFile the file where to store the plugin paths cache, it will be overwritten
	 *
	 * @return void
	 * @throws Exception
	 */
	protected function rebuildClassPathCache($path, $cacheFile) {
		if ($cacheFile !== false) {
			$tmp             = $this->classPath;
			$this->classPath = array();
		}

		$dir_iterator   = new \RecursiveDirectoryIterator($path);
		$files = new \RecursiveIteratorIterator($dir_iterator, \RecursiveIteratorIterator::SELF_FIRST);
		try {
			foreach ($files as $file) {
				if ($file->isFile() && preg_match('/\.(inc|php)$/', $file->getFilename())) {
					if ($classNames = $this->getFromFile($file->getPathname())) {
						foreach ($classNames as $className) {
							$this->classPath[$className] = $file->getPathname();
						}
					}
				}
			}
		}
		catch (\UnexpectedValueException $e) {
			printf("Directory [%s] contained a directory we can not recurse into", $path);
		}

		// save in file if it's the first call (not recursed)
		if ($cacheFile !== false) {
			if (! file_put_contents($cacheFile, serialize($this->classPath))) {
				throw new Exception('Could not write into ' . $cacheFile . ', either because the folder is not there (create it) or because of the chmod configuration (please ensure this directory is writable by php), alternatively you can change the directory used with $dwoo->setCompileDir() or provide a custom loader object with $dwoo->setLoader()');
			}
			$this->classPath += $tmp;
		}
	}

	/**
	 * Found all classes & functions inside PHP and INI file
	 * @param $file
	 * @return array
	 */
	protected function getFromFile($file) {
		$classes = array();
		$tokens = token_get_all(file_get_contents($file));
		$inClass = false;
		for ($i = 0 ; $i < count($tokens) ; $i++) {
			switch ($tokens[$i][0]) {
				case T_CLASS:
					$inClass = true;
				case T_INTERFACE:
				case T_TRAIT:
					$i+=2;
					$classes[] = $tokens[$i][1];
					break;
				case T_FUNCTION:
					if (!$inClass) {
						$i+=2;
						$tokens[$i][1] = str_replace(array('function_', 'smarty_'), array('', ''), $tokens[$i][1]);
						$classes[] = $tokens[$i][1];
					}
					break;
			}
		}

		return $classes;
	}
}
