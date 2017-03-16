<?php
/**
 * Copyright (c) 2013-2017
 *
 * @category  Library
 * @package   Dwoo
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2017 David Sanchez
 * @license   http://dwoo.org/LICENSE LGPLv3
 * @version   1.4.0
 * @date      2017-03-16
 * @link      http://dwoo.org/
 */

namespace Dwoo;

/**
 * Handles plugin loading and caching of plugins names/paths relationships.
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class Loader implements ILoader
{
    /**
     * Stores the plugin directories.
     *
     * @see addDirectory
     * @var array
     */
    protected $paths = array();

    /**
     * Stores the plugins names/paths relationships
     * don't edit this on your own, use addDirectory.
     *
     * @see addDirectory
     * @var array
     */
    protected $classPath = array();

    /**
     * Path where class paths cache files are written.
     *
     * @var string
     */
    protected $cacheDir;

    /**
     * Path where builtin plugins are stored.
     *
     * @var string
     */
    protected $corePluginDir;

    /**
     * Loader constructor.
     *
     * @param $cacheDir
     */
    public function __construct($cacheDir)
    {
        $this->corePluginDir = __DIR__ . DIRECTORY_SEPARATOR . 'Plugins';
        $this->cacheDir      = rtrim($cacheDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

        // include class paths or rebuild paths if the cache file isn't there
        $cacheFile = $this->cacheDir . 'classpath.cache.d' . Core::RELEASE_TAG . '.php';
        if (file_exists($cacheFile)) {
            $classpath       = file_get_contents($cacheFile);
            $this->classPath = unserialize($classpath) + $this->classPath;
        } else {
            $this->rebuildClassPathCache($this->corePluginDir, $cacheFile);
        }
    }

    /**
     * Rebuilds class paths, scans the given directory recursively and saves all paths in the given file.
     *
     * @param string         $path      the plugin path to scan
     * @param string|boolean $cacheFile the file where to store the plugin paths cache, it will be overwritten
     *
     * @throws Exception
     */
    protected function rebuildClassPathCache($path, $cacheFile)
    {
        $tmp = array();
        if ($cacheFile !== false) {
            $tmp             = $this->classPath;
            $this->classPath = array();
        }

        // iterates over all files/folders
        foreach (new \DirectoryIterator($path) as $fileInfo) {
            if (!$fileInfo->isDot()) {
                if ($fileInfo->isDir()) {
                    $this->rebuildClassPathCache($fileInfo->getPathname(), false);
                } else {
                    $this->classPath[$fileInfo->getBasename('.php')] = $fileInfo->getPathname();
                }
            }
        }

        // save in file if it's the first call (not recursed)
        if ($cacheFile !== false) {
            if (!file_put_contents($cacheFile, serialize($this->classPath))) {
                throw new Exception('Could not write into ' . $cacheFile . ', either because the folder is not there (create it) or because of the chmod configuration (please ensure this directory is writable by php), alternatively you can change the directory used with $dwoo->setCompileDir() or provide a custom loader object with $dwoo->setLoader()');
            }
            $this->classPath += $tmp;
        }
    }

    /**
     * Loads a plugin file.
     *
     * @param string $class       the plugin name, without the `Plugin` prefix
     * @param bool   $forceRehash if true, the class path caches will be rebuilt if the plugin is not found, in case it
     *                            has just been added, defaults to true
     *
     * @throws Exception
     */
    public function loadPlugin($class, $forceRehash = true)
    {
        /**
         * An unknown class was requested (maybe newly added) or the
         * include failed so we rebuild the cache. include() will fail
         * with an uncatchable error if the file doesn't exist, which
         * usually means that the cache is stale and must be rebuilt,
         * so we check for that before trying to include() the plugin.
         */
        if ((!isset($this->classPath[$class]) || !is_readable($this->classPath[$class])) || (!isset
                ($this->classPath[$class . 'Compile']) || !is_readable($this->classPath[$class . 'Compile']))) {
            if ($forceRehash) {
                $this->rebuildClassPathCache($this->corePluginDir, $this->cacheDir . 'classpath.cache.d' .
                    Core::RELEASE_TAG . '.php');
                foreach ($this->paths as $path => $file) {
                    $this->rebuildClassPathCache($path, $file);
                }
                if (isset($this->classPath[$class])) {
                    include_once $this->classPath[$class];
                } elseif (isset($this->classPath[$class . 'Compile'])) {
                    include_once $this->classPath[$class . 'Compile'];
                } else {
                    throw new Exception('Plugin "' . $class . '" can not be found, maybe you forgot to bind it if it\'s a custom plugin ?', E_USER_NOTICE);
                }
            } else {
                throw new Exception('Plugin "' . $class . '" can not be found, maybe you forgot to bind it if it\'s a custom plugin ?', E_USER_NOTICE);
            }
        }
    }

    /**
     * Adds a plugin directory, the plugins found in the new plugin directory
     * will take precedence over the other directories (including the default
     * dwoo plugin directory), you can use this for example to override plugins
     * in a specific directory for a specific application while keeping all your
     * usual plugins in the same place for all applications.
     * TOCOM don't forget that php functions overrides are not rehashed so you
     * need to clear the classpath caches by hand when adding those.
     *
     * @param string $pluginDirectory the plugin path to scan
     *
     * @throws Exception
     */
    public function addDirectory($pluginDirectory)
    {
        $pluginDir = realpath($pluginDirectory);
        if (!$pluginDir) {
            throw new Exception('Plugin directory does not exist or can not be read : ' . $pluginDirectory);
        }
        $cacheFile = $this->cacheDir . 'classpath-' . substr(strtr($pluginDir, '/\\:' . PATH_SEPARATOR, '----'),
                strlen($pluginDir) > 80 ? - 80 : 0) . '.d' . Core::RELEASE_TAG . '.php';
        $this->paths[$pluginDir] = $cacheFile;
        if (file_exists($cacheFile)) {
            $classpath       = file_get_contents($cacheFile);
            $this->classPath = unserialize($classpath) + $this->classPath;
        } else {
            $this->rebuildClassPathCache($pluginDir, $cacheFile);
        }
    }
}
