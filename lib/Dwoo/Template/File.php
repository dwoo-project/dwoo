<?php

/**
 * represents a Dwoo template contained in a file
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
class Dwoo_Template_File extends Dwoo_Template_String
{
	/**
	 * template filename
	 *
	 * @var string
	 */
	protected $file;

	/**
	 * include path(s) to look into to find this template
	 *
	 * @var array
	 */
	protected $includePath = null;

	/**
	 * resolved path cache when looking for a file in multiple include paths
	 *
	 * this is reset when the include path is changed
	 *
	 * @var string
	 */
	protected $resolvedPath = null;

	/**
	 * creates a template from a file
	 *
	 * @param string $file the path to the template file, make sure it exists
	 * @param int $cacheTime duration of the cache validity for this template,
	 * 						 if null it defaults to the Dwoo instance that will
	 * 						 render this template
	 * @param string $cacheId the unique cache identifier of this page or anything else that
	 * 						  makes this template's content unique, if null it defaults
	 * 						  to the current url
	 * @param string $compileId the unique compiled identifier, which is used to distinguish this
	 * 							template from others, if null it defaults to the filename+bits of the path
	 * @param mixed $includePath a string for a single path to look into for the given file, or an array of paths
	 */
	public function __construct($file, $cacheTime = null, $cacheId = null, $compileId = null, $includePath = null)
	{
		$this->file = $file;
		$this->name = basename($file);
		$this->cacheTime = $cacheTime;

		if ($compileId !== null) {
			$this->compileId = strtr($compileId, '\\%?=!:;'.PATH_SEPARATOR, '/-------');
		}

		if ($cacheId !== null) {
			$this->cacheId = strtr($cacheId, '\\%?=!:;'.PATH_SEPARATOR, '/-------');
		}

		if (is_string($includePath)) {
			$this->includePath = array($includePath);
		} elseif (is_array($includePath)) {
			$this->includePath = $includePath;
		}
	}

	/**
	 * sets the include path(s) to where the given template filename must be looked up
	 *
	 * @param mixed $paths the path to look into, can be string for a single path or an array of paths
	 */
	public function setIncludePath($paths)
	{
		if (is_array($paths) === false) {
			$paths = array($paths);
		}

		$this->includePath = $paths;
		$this->resolvedPath = null;
	}

	/**
	 * return the current include path(s)
	 *
	 * @return array
	 */
	public function getIncludePath()
	{
		return $this->includePath;
	}

	/**
	 * returns the compiled template file name
	 *
	 * @param Dwoo $dwoo the dwoo instance that requests it
	 * @param Dwoo_ICompiler $compiler the compiler that must be used
	 * @return string
	 */
	public function getCompiledTemplate(Dwoo $dwoo, Dwoo_ICompiler $compiler = null)
	{
		$compiledFile = $this->getCompiledFilename($dwoo);

		if ($this->compilationEnforced !== true && isset(self::$cache['compiled'][$this->compileId]) === true) {
			// already checked, return compiled file
		} elseif ($this->compilationEnforced !== true && file_exists($compiledFile)===true && (int)$this->getUid() <= filemtime($compiledFile)) {
			// template is compiled and has not been modified since the compilation
			self::$cache['compiled'][$this->compileId] = true;
		} else {
			// compiles the template
			$this->compilationEnforced = false;

			if ($compiler === null) {
				$compiler = $dwoo->getDefaultCompilerFactory('string');

				if ($compiler === null || $compiler === array('Dwoo_Compiler', 'compilerFactory')) {
					if (class_exists('Dwoo_Compiler', false) === false) {
						include 'Dwoo/Compiler.php';
					}
					$compiler = Dwoo_Compiler::compilerFactory();
				} else {
					$compiler = call_user_func($compiler);
				}
			}

			$this->compiler = $compiler;

			$compiler->setCustomPlugins($dwoo->getCustomPlugins());
			$compiler->setSecurityPolicy($dwoo->getSecurityPolicy());
			$this->makeDirectory(dirname($compiledFile));
			file_put_contents($compiledFile, $compiler->compile($dwoo, $this));
			chmod($compiledFile, DWOO_CHMOD);

			self::$cache['compiled'][$this->compileId] = true;
		}

		return $compiledFile;
	}

	/**
	 * returns the template source of this template
	 *
	 * @return string
	 */
	public function getSource()
	{
		return file_get_contents($this->getResourceIdentifier());
	}

	/**
	 * returns the resource name for this template class
	 *
	 * @return string
	 */
	public function getResourceName()
	{
		return 'file';
	}

	/**
	 * returns this template's source filename
	 *
	 * @return string
	 */
	public function getResourceIdentifier()
	{
		if ($this->resolvedPath !== null) {
			return $this->resolvedPath;
		} elseif ($this->includePath === null) {
			return $this->file;
		} else {
			foreach ($this->includePath as $path) {
				if (file_exists($path.DIRECTORY_SEPARATOR.$this->file) === true) {
					$this->resolvedPath = $path . DIRECTORY_SEPARATOR . $this->file;
					return $this->resolvedPath;
				}
			}

			throw new Dwoo_Exception('Template "'.$this->file.'" could not be found in any of your include path(s)');
		}
	}

	/**
	 * returns an unique value identifying the current version of this template,
	 * in this case it's the unix timestamp of the last modification
	 *
	 * @return string
	 */
	public function getUid()
	{
		return (string) filemtime($this->getResourceIdentifier());
	}

	/**
	 * returns a new template object from the given include name, null if no include is
	 * possible (resource not found), or false if include is not permitted by this resource type
	 *
	 * @param Dwoo $dwoo the dwoo instance requiring it
	 * @param mixed $resourceId the filename (relative to this template's dir) of the template to include
	 * @param int $cacheTime duration of the cache validity for this template,
	 * 						 if null it defaults to the Dwoo instance that will
	 * 						 render this template
	 * @param string $cacheId the unique cache identifier of this page or anything else that
	 * 						  makes this template's content unique, if null it defaults
	 * 						  to the current url
	 * @param string $compileId the unique compiled identifier, which is used to distinguish this
	 * 							template from others, if null it defaults to the filename+bits of the path
	 * @return Dwoo_Template_File|null
	 */
	public static function templateFactory(Dwoo $dwoo, $resourceId, $cacheTime = null, $cacheId = null, $compileId = null)
	{
		$resourceId = str_replace(array("\t", "\n", "\r"), array('\\t', '\\n', '\\r'), $resourceId);
		if (file_exists($resourceId) === false) {
			$tpl = $dwoo->getTemplate();
			if ($tpl instanceof Dwoo_Template_File) {
				$resourceId = dirname($tpl->getResourceIdentifier()).DIRECTORY_SEPARATOR.$resourceId;
				if (file_exists($resourceId) === false) {
					return null;
				}
			} else {
				return null;
			}
		}

		// prevent template recursion if security is in effect
		if ($policy = $dwoo->getSecurityPolicy()) {
			$tpl = $dwoo->getTemplate();
			if ($tpl instanceof Dwoo_Template_File && $resourceId === $tpl->getResourceIdentifier()) {
				return $dwoo->triggerError('You can not include a template into itself', E_USER_WARNING);
			}
		}

		return new Dwoo_Template_File($resourceId, $cacheTime, $cacheId, $compileId);
	}

	/**
	 * returns the full compiled file name and assigns a default value to it if
	 * required
	 *
	 * @param Dwoo $dwoo the dwoo instance that requests the file name
	 * @return string the full path to the compiled file
	 */
	protected function getCompiledFilename(Dwoo $dwoo)
	{
		// no compile id was provided, set default
		if ($this->compileId===null) {
			$this->compileId = implode('/', array_slice(explode('/', strtr($this->getResourceIdentifier(), '\\', '/')), -3));
		}
		return $dwoo->getCompileDir() . $this->compileId.'.d'.Dwoo::RELEASE_TAG.'.php';
	}
}
