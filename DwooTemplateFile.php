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
 * @version    0.3.3
 * @date       2008-03-19
 * @package    Dwoo
 */
class DwooTemplateFile extends DwooTemplateString
{
	/**
	 * template filename
	 *
	 * @var string
	 */
	protected $file;

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
	 */
	public function __construct($file, $cacheTime = null, $cacheId = null, $compileId = null)
	{
		$this->file = realpath($file);
		$this->name = basename($file);
		$this->cacheTime = $cacheTime;

		// no compile id provided, generate a kind of unique kind of readable one from the filename
		if($compileId === null)
		{
			$parts = explode('/', strtr($file, '\\.', '/_'));
			$compileId = array_pop($parts);
			$compileId = substr(array_pop($parts), 0, 5) .'_'. $compileId;
			$compileId = substr(array_pop($parts), 0, 5) .'_'. $compileId;
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
	 * returns the compiled template file name
	 *
	 * @param Dwoo $dwoo the dwoo instance that requests it
	 * @param DwooICompiler $compiler the compiler that must be used
	 * @return string
	 */
	public function getCompiledTemplate(Dwoo $dwoo, DwooICompiler $compiler)
	{
		$compiledFile = $dwoo->getCompileDir() . $this->compileId.'.'.Dwoo::RELEASE_TAG.'.dwoo';

		// already checked, return compiled file
		if($this->compilationEnforced !== true && isset(self::$cache['compiled'][$this->compileId]) === true)
		{
		}
		// template is compiled and has not been modified since the compilation
		elseif($this->compilationEnforced !== true && file_exists($compiledFile)===true && filemtime($this->file) <= filemtime($compiledFile))
		{
			self::$cache['compiled'][$this->compileId] = true;
		}
		// compiles the template
		else
		{
			$this->compilationEnforced = false;

			$this->compiler = $compiler;

			$compiler->setCustomPlugins($dwoo->getCustomPlugins());
			$compiler->setSecurityPolicy($dwoo->getSecurityPolicy());
			file_put_contents($compiledFile, $compiler->compile(file_get_contents($this->file)));
			touch($compiledFile, max($_SERVER['REQUEST_TIME'], filemtime($this->file)));

			self::$cache['compiled'][$this->compileId] = true;
		}

		return $compiledFile;
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
	public function getFilename()
	{
		return $this->file;
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
	 * @return DwooTemplateFile|null
	 */
	public static function templateFactory(Dwoo $dwoo, $resourceId, $cacheTime = null, $cacheId = null, $compileId = null)
	{
		$resourceId = str_replace(array("\t", "\n", "\r"), array('\\t', '\\n', '\\r'), $resourceId);

		if(file_exists($resourceId) === false)
		{
			$tpl = $dwoo->getCurrentTemplate();
			if($tpl instanceof DwooTemplateFile)
			{
				$resourceId = dirname($tpl->getFilename()).DIRECTORY_SEPARATOR.$resourceId;
				if(file_exists($resourceId) === false)
					return null;
			}
		}

		if($policy = $dwoo->getSecurityPolicy())
		{
			$resourceId = realpath($resourceId);
			if($resourceId === $this->file)
				return $dwoo->triggerError('You can not include a template into itself', E_USER_WARNING);
		}

		return new self($resourceId, $cacheTime, $cacheId, $compileId);
	}
}

?>