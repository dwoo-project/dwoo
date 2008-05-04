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
 * @version    0.3.4
 * @date       2008-04-09
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

		// no cache id provided, use request_uri if available
		if($cacheId === null)
		{
			if(isset($_SERVER['REQUEST_URI']) === true)
				$cacheId = strtr($_SERVER['REQUEST_URI'], '\\/%?=!:;', '--------');
			elseif(isset($_SERVER['SCRIPT_FILENAME']) && isset($_SERVER['argv']))
				$cacheId = strtr($_SERVER['SCRIPT_FILENAME'].'-'.implode('-', $_SERVER['argv']), '\\/%?=!:;', '--------');
		}
		$this->cacheId = $this->compileId . $cacheId;
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
		$compiledFile = $dwoo->getCompileDir() . $this->compileId.'.dwoo'.Dwoo::RELEASE_TAG.'.php';

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

			if($compiler === null)
			{
				$compiler = $dwoo->getDefaultCompilerFactory('string');

				if($compiler === null || $compiler === array('Dwoo_Compiler', 'compilerFactory'))
				{
					if(class_exists('Dwoo_Compiler', false) === false)
						include 'Dwoo/Compiler.php';
					$compiler = Dwoo_Compiler::compilerFactory();
				}
				else
					$compiler = call_user_func($compiler);
			}

			$this->compiler = $compiler;

			$compiler->setCustomPlugins($dwoo->getCustomPlugins());
			$compiler->setSecurityPolicy($dwoo->getSecurityPolicy());
			file_put_contents($compiledFile, $compiler->compile($dwoo, $this));
			touch($compiledFile, max($_SERVER['REQUEST_TIME'], filemtime($this->file)));

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
		return file_get_contents($this->file);
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
		return $this->file;
	}

	/**
	 * returns an unique value identifying the current version of this template,
	 * in this case it's the unix timestamp of the last modification
	 *
	 * @return string
	 */
	public function getUid()
	{
		return (string) filemtime($this->file);
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
		if(file_exists($resourceId) === false)
		{
			$tpl = $dwoo->getTemplate();
			if($tpl instanceof Dwoo_Template_File)
			{
				$resourceId = dirname($tpl->getResourceIdentifier()).DIRECTORY_SEPARATOR.$resourceId;
				if(file_exists($resourceId) === false)
					return null;
			}
			else
				return null;
		}

		if($policy = $dwoo->getSecurityPolicy())
		{
			$resourceId = realpath($resourceId);
			// TODO check for getTemplate() not returning null
			if($resourceId === $dwoo->getTemplate()->getResourceIdentifier())
				return $dwoo->triggerError('You can not include a template into itself', E_USER_WARNING);
		}

		return new Dwoo_Template_File($resourceId, $cacheTime, $cacheId, $compileId);
	}
}

?>