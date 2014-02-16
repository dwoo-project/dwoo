<?php
namespace Dwoo;

require 'IAutoloader.php';

/**
 * SplClassLoader implementation that implements the technical interoperability
 * standards for PHP 5.3 namespaces and class names.
 *
 * https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
 *
 * Example usage:
 *
 *     $autoloader = new \Dwoo\Autoloader();
 *
 *     // Configure the SplClassLoader to act normally or silently
 *     $autoloader->setMode(\SplClassLoader::MODE_NORMAL);
 *
 *     // Add a prefix
 *     $autoloader->add('Dwoo', '/path/to/dwoo');
 *
 *     // Allow to PHP use the include_path for file path lookup
 *     $autoloader->setIncludePathLookup(true);
 *
 *     // Possibility to change the default php file extension
 *     $autoloader->setFileExtension('.php');
 *
 *     // Register the autoloader, prepending it in the stack
 *     $autoloader->register(true);
 *
 * @author     Guilherme Blanco <guilhermeblanco@php.net>
 * @author     Jonathan H. Wage <jonwage@gmail.com>
 * @author     Roman S. Borschel <roman@code-factory.org>
 * @author     Matthew Weier O'Phinney <matthew@zend.com>
 * @author     Kris Wallsmith <kris.wallsmith@gmail.com>
 * @author     Fabien Potencier <fabien.potencier@symfony-project.org>
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2013-2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2014-02-16
 * @package    Dwoo
 */
class Autoloader implements IAutoloader {
	/**
	 * @var string
	 */
	private $fileExtension = '.php';

	/**
	 * @var boolean
	 */
	private $includePathLookup = false;

	/**
	 * @var array
	 */
	private $resources = [];

	/**
	 * @var integer
	 */
	private $mode = self::MODE_NORMAL;

	/**
	 * Define the autoloader work mode.
	 * @param integer $mode Autoloader work mode.
	 * @throws \InvalidArgumentException
	 * @return $this
	 */
	public function setMode($mode) {
		if ($mode & self::MODE_SILENT && $mode & self::MODE_NORMAL) {
			throw new \InvalidArgumentException(sprintf('Cannot have %s working normally and silently at the same time!', __CLASS__));
		}
		$this->mode = $mode;

		return $this;
	}

	/**
	 * Define the file extension of resource files in the path of this class loader.
	 * @param string $fileExtension
	 * @return $this
	 */
	public function setFileExtension($fileExtension) {
		$this->fileExtension = $fileExtension;

		return $this;
	}

	/**
	 * Retrieve the file extension of resource files in the path of this class loader.
	 * @return string
	 */
	public function getFileExtension() {
		return $this->fileExtension;
	}

	/**
	 * Turns on searching the include for class files. Allows easy loading installed PEAR packages.
	 * @param boolean $includePathLookup
	 * @return $this
	 */
	public function setIncludePathLookup($includePathLookup) {
		$this->includePathLookup = $includePathLookup;

		return $this;
	}

	/**
	 * Gets the base include path for all class files in the namespace of this class loader.
	 * @return boolean
	 */
	public function getIncludePathLookup() {
		return $this->includePathLookup;
	}

	/**
	 * Register this as an autoloader instance.
	 * @param bool $prepend Whether to prepend the autoloader or not in autoloader's list.
	 */
	public function register($prepend = false) {
		spl_autoload_register([$this,
							  'load'
							  ], true, $prepend);
	}

	/**
	 * Unregister this autoloader instance.
	 */
	public function unregister() {
		spl_autoload_unregister([$this,
								'load'
								]);
	}

	/**
	 * Add a new resource lookup path.
	 * @param string $resource
	 * @param mixed  $resourcePath Resource single path or multiple paths (array).
	 * @internal param string $resourceName Resource name, namespace or prefix.
	 * @return $this
	 */
	public function add($resource, $resourcePath = null) {
		$this->resources[$resource] = (array)$resourcePath;

		return $this;
	}

	/**
	 * Load a resource through provided resource name.
	 * @param string $resourceName Resource name.
	 * @throws \RuntimeException
	 * @throws \Exception
	 */
	protected function load($resourceName) {
		$resourceAbsolutePath = $this->getResourceAbsolutePath($resourceName);

		// Check class exist
		if ($resourceAbsolutePath === false) {
			throw new \Exception('Class \'' . $resourceName . '\', does not exist');
		}

		// Require class
		require $resourceAbsolutePath;

		if ($this->mode & self::MODE_DEBUG && !$this->_isResourceDeclared($resourceName)) {
			throw new \RuntimeException(sprintf('Autoloader expected resource "%s" to be declared in file "%s".', $resourceName, $resourceAbsolutePath));
		}
	}

	/**
	 * Transform resource name into its absolute resource path representation.
	 * @param string $resourceName
	 * @return string|bool Resource absolute path.
	 */
	private function getResourceAbsolutePath($resourceName) {
		$resourceRelativePath = $this->getResourceRelativePath($resourceName);

		foreach ($this->resources as $resource => $resourcesPath) {
			if (strpos($resourceName, $resource . '\\') !== 0) {
				continue;
			}

			foreach ($resourcesPath as $resourcePath) {
				if (\Phar::running() !== '') {
					$resourceAbsolutePath = \Phar::running().substr($resourceRelativePath, strlen($resource));
				}
				else {
					$resourceAbsolutePath = $resourcePath . DIRECTORY_SEPARATOR . substr($resourceRelativePath, strlen($resource));
					$resourceAbsolutePath = str_replace(DIRECTORY_SEPARATOR . DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR, $resourceAbsolutePath);
				}

				if (is_file($resourceAbsolutePath)) {
					return $resourceAbsolutePath;
				}
			}
		}


		if ($this->includePathLookup && ($resourceAbsolutePath = stream_resolve_include_path($resourceRelativePath)) !== false) {
			return $resourceAbsolutePath;
		}

		return false;
	}

	/**
	 * Transform resource name into its relative resource path representation.
	 * @param string $resourceName
	 * @return string Resource relative path.
	 */
	private function getResourceRelativePath($resourceName) {
		// We always work with FQCN in this context
		$resourceName = ltrim($resourceName, '\\');
		$resourcePath = '';

		if (($lastNamespacePosition = strrpos($resourceName, '\\')) !== false) {
			// Namespaced resource name
			$resourceNamespace = substr($resourceName, 0, $lastNamespacePosition);
			$resourceName      = substr($resourceName, $lastNamespacePosition + 1);
			$resourcePath      = str_replace('\\', DIRECTORY_SEPARATOR, $resourceNamespace) . DIRECTORY_SEPARATOR;
		}

		return $resourcePath . str_replace('_', DIRECTORY_SEPARATOR, $resourceName) . $this->fileExtension;
	}

	/**
	 * Check if resource is declared in user space.
	 * @param string $resourceName
	 * @return boolean
	 */
	private function _isResourceDeclared($resourceName) {
		return class_exists($resourceName, false) || interface_exists($resourceName, false) || (function_exists('trait_exists') && trait_exists($resourceName, false));
	}
}