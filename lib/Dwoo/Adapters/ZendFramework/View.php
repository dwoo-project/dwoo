<?php

namespace Dwoo\Adapters\ZendFramework;

use Dwoo\Compiler;
use Dwoo\Core;
use Dwoo\Data;
use Dwoo\ICompiler;
use Dwoo\IDataProvider;
use Dwoo\IPluginProxy;
use Dwoo\ITemplate;

/**
 * Dwoo adapter for ZendFramework
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the
 * use of this software.
 *
 * @author	   Denis Arh <denis@arh.cc>
 * @author     Stephan Wentz <stephan@wentz.it>
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @copyright  Copyright (c) 2008, Jordi Boggiano
 * @license    http://dwoo.org/LICENSE   Modified BSD License
 * @link       http://dwoo.org/
 * @version    1.1.0
 * @date       2009-07-18
 * @package    Dwoo
 */
class View extends \Zend_View_Abstract
{
    /**
     * @var Core
     */
    protected $_engine = null;

    /**
     * @var Data
	 */
	protected $_dataProvider = null;

	/**
	 * @var Compiler
	 */
	protected $_compiler = null;

	/**
	 * Changing Filter's scope to play nicely
	 *
	 * @var array
	 */
	protected $_filter = array();


	/**
	 * @var string
	 */
	protected $_templateFileClass = 'Dwoo\Template\File';

	/**
	 * @var array
	 */
	protected $_templateFileSettings = array();

	/**
	 * @var IPluginProxy
	 */
	protected $_pluginProxy = null;

	/**
	 * Constructor method.
	 * See setOptions for $opt details
	 *
	 * @see setOptions
	 * @param array|\Zend_Config List of options or Zend_Config instance
	 */
	public function __construct($opt = array())
	{

		if (is_array($opt)) {
			$this->setOptions($opt);
		} elseif ($opt instanceof \Zend_Config) {
			$this->setConfig($opt);
		}

		$this->init();
	}

	/**
	 * Set object state from options array
	 *  - engine        = engine class name|engine object|array of options for engine
	 *  - dataProvider  = data provider class name|data provider object|array of options for data provider
	 *  - compiler      = compiler class name|compiler object|array of options for compiler
	 *  - templateFile  =
	 *
	 *  Array of options:
	 *  - type class name or object for engine, dataProvider or compiler
	 *  - any set* method (compileDir for setCompileDir ...)
	 *
	 * @param  array $options
	 * @return View
	 */
	public function setOptions(array $opt = array())
	{
		// Making sure that everything is loaded.
		$classes = array('engine', 'dataProvider', 'compiler');

		// Setting options to Dwoo objects...
		foreach ($opt as $type => $settings) {
			if (!method_exists($this, 'set' . $type)) {
				throw new \Dwoo\Exception("Unknown type $type");
			}

			if (is_string($settings) || is_object($settings)) {
				call_user_func(array($this, 'set' . $type), $settings);
			} elseif (is_array($settings)) {
				// Set requested class
				if (array_key_exists('type', $settings)) {
					call_user_func(array($this, 'set' . $type), $settings['type']);
				}

				if (in_array($type, $classes)) {
					// Call get so that the class is initialized
					$rel = call_user_func(array($this, 'get' . $type));

					// Call set*() methods so that all the settings are set.
					foreach ($settings as $method => $value) {
						if (method_exists($rel, 'set' . $method)) {
							call_user_func(array($rel, 'set' . $method), $value);
						}
					}
				} elseif ('templateFile' == $type) {
					// Remember the settings for the templateFile
					$this->_templateFileSettings = $settings;
				}
			}
		}
	}

	/**
	 * Set object state from Zend_Config object
	 *
	 * @param  \Zend_Config $config
	 * @return $this
	 */
	public function setConfig(\Zend_Config $config)
	{
		return $this->setOptions($config->toArray());
	}

	/**
	 * Called before template rendering
	 *
	 * Binds plugin proxy to the Dwoo.
	 *
	 * @see Dwoo_Adapters_ZendFramework_View::getPluginProxy();
	 * @see Dwoo_Core::setPluginProxy();
	 */
	protected function preRender()
	{
		$this->getEngine()->setPluginProxy($this->getPluginProxy());
	}

	/**
	 * Wraper for \Dwoo\Data::__set()
	 * allows to assign variables using the object syntax
	 *
	 * @see \Dwoo\Data::__set()
	 * @param string $name the variable name
	 * @param string $value the value to assign to it
	 */
	public function __set($name, $value)
	{
		$this->getDataProvider()->__set($name, $value);
	}

	/**
	 * Sraper for \Dwoo\Data::__get() allows to read variables using the object
	 * syntax
	 *
	 * @see \Dwoo\Data::__get()
	 * @param string $name the variable name
	 * @return mixed
	 */
	public function __get($name)
	{
		 return $this->getDataProvider()->__get($name);
	}

	/**
	 * Wraper for \Dwoo\Data::__isset()
	 * supports calls to isset($dwooData->var)
	 *
	 * @see \Dwoo\Data::__isset()
	 * @param string $name the variable name
	 */
	public function __isset($name)
	{
		return $this->getDataProvider()->__isset($name);
	}

	/**
	 * Wraper for \Dwoo\Data::_unset()
	 * supports unsetting variables using the object syntax
	 *
	 * @see \Dwoo\Data::__unset()
	 * @param string $name the variable name
	 */
	public function __unset($name)
	{
		$this->getDataProvider()->__unset($name);
	}

	/**
	 * Catches clone request and clones data provider
	 */
	public function __clone() {
		$this->setDataProvider(clone $this->getDataProvider());
	}

	/**
	 * Returns plugin proxy interface
	 *
	 * @return IPluginProxy
	 */
	public function getPluginProxy()
	{
		if (!$this->_pluginProxy) {
			$this->_pluginProxy = new PluginProxy($this);
		}

		return $this->_pluginProxy;
	}

	/**
	 * Sets plugin proxy
	 *
	 * @param IPluginProxy
	 * @return $this
	 */
	public function setPluginProxy(IPluginProxy $pluginProxy)
	{
		$this->_pluginProxy = $pluginProxy;
		return $this;
	}

	/**
	 * Sets template engine
	 *
	 * @param string|\Dwoo\Object or name of the class
	 */
	public function setEngine($engine)
	{
		// if param given as an object
		if ($engine instanceof Core) {
			$this->_engine = $engine;
		}
		//
		elseif (is_subclass_of($engine, '\Dwoo\Core') || '\Dwoo\Core' === $engine) {
			$this->_engine = new $engine();
		}
		else {
			throw new \Dwoo\Exception("Custom engine must be a subclass of \\Dwoo\\Core");
		}
	}

	/**
	 * Return the Dwoo template engine object
	 *
	 * @return Dwoo
	 */
	public function getEngine()
	{
		if (null === $this->_engine) {
			$this->_engine = new Dwoo();
		}

		return $this->_engine;
	}

	/**
	 * Sets Dwoo data object
	 *
	 * @param string|Data Object or name of the class
	 */
	public function setDataProvider($data)
	{
		if ($data instanceof IDataProvider) {
			$this->_dataProvider = $data;
		}
		elseif (is_subclass_of($data, '\Dwoo\Data') || '\Dwoo\Data' == $data) {
			$this->_dataProvider = new $data();
		}
		else {
			throw new \Dwoo\Exception("Custom data provider must be a subclass of \\Dwoo\\Data or instance of \\Dwoo\\IDataProvider");
		}
	}

	/**
	 * Return the Dwoo data object
	 *
	 * @return Data
	 */
	public function getDataProvider()
	{
		if (null === $this->_dataProvider) {
			$this->_dataProvider = new Data;

			// Satisfy Zend_View_Abstract wishes to access this unexisting property
			// by setting it to empty array (see Zend_View_Abstract::_filter)
			$this->_dataProvider->_filter = array();
		}

		return $this->_dataProvider;
	}


	/**
	 * Sets Dwoo compiler
	 *
	 * @param string|ICompiler Object or name of the class
	 */
	public function setCompiler($compiler)
	{

		// if param given as an object
		if ($compiler instanceof ICompiler) {
			$this->_compiler = $compiler;
		}
		// if param given as a string
		elseif (is_subclass_of($compiler, 'Dwoo_Compiler') || 'Dwoo_Compiler' == $compiler) {
			$this->_compiler = new $compiler;
		}
		else {
			throw new \Dwoo\Exception("Custom compiler must be a subclass of Dwoo_Compiler or instance of Dwoo_ICompiler");
		}
	}

	/**
	 * Return the Dwoo compiler object
	 *
	 * @return ICompiler
	 */
	public function getCompiler()
	{
		if (null === $this->_compiler) {
			$this->_compiler = Compiler::compilerFactory();
		}

		return $this->_compiler;
	}

	/**
	 * Initializes Dwoo_ITemplate type of class and sets properties from _templateFileSettings
	 *
	 * @param  string Template location
	 * @return ITemplate
	 */
	public function getTemplateFile($template) {
		$templateFileClass = $this->_templateFileClass;

		$dwooTemplateFile = new $templateFileClass($template);

		if (!($dwooTemplateFile instanceof ITemplate)) {
			throw new \Dwoo\Exception("Custom templateFile class must be a subclass of Dwoo_ITemplate");
		}

		foreach ($this->_templateFileSettings as $method => $value) {
			if (method_exists($dwooTemplateFile, 'set' . $method)) {
				call_user_func(array($dwooTemplateFile, 'set' . $method), $value);
			}
		}

		return $dwooTemplateFile;
	}

	/**
	 * Dwoo_ITemplate type of class
	 *
	 * @param string Name of the class
	 * @return void
	 */
	public function setTemplateFile($tempateFileClass) {
		$this->_templateFileClass = $tempateFileClass;
	}

	/**
	 * Passes data to Dwoo_Data object
	 *
	 * @see Dwoo_Data::assign()
	 * @param array|string $name
	 * @param mixed $val
	 * @return $this
	 */
	public function assign($name, $val = null)
	{
		$this->getDataProvider()->assign($name, $val);
		return $this;
	}

	/**
	 * Return list of all assigned variables
	 *
	 * @return array
	 */
	public function getVars()
	{
		return $this->getDataProvider()->getData();
	}

	/**
	 * Clear all assigned variables
	 *
	 * Clears all variables assigned to Zend_View either via {@link assign()} or
	 * property overloading ({@link __get()}/{@link __set()}).
	 *
	 * @return $this
	 */
	public function clearVars()
	{
		$this->getDataProvider()->clear();
		return $this;
	}

	/**
	 * Wraper for parent's render method so preRender method
	 * can be called (that will bind the plugin proxy to the
	 * engine.
	 *
	 * @see Zend_View_Abstract::render
	 * @return string The script output.
	 */
	public function render($name)
	{
		$this->preRender();
		return parent::render($name);
	}

	/**
	 * Processes a view script and outputs it. Output is then
	 * passed through filters.
	 *
	 * @param string $name The script script name to process.
	 * @return string The script output.
	 */
	public function _run()
	{
		echo $this->_engine->get(
			$this->getTemplateFile(func_get_arg(0)),
			$this->getDataProvider(),
			$this->getCompiler()
		);
	}

	/**
	 * Add plugin path
	 *
	 * @param string $dir Directory
	 * @return $this
	 */
	public function addPluginDir($dir)
	{
		$this->getEngine()->getLoader()->addDirectory($dir);
		return $this;
	}

	/**
	 * Set compile path
	 *
	 * @param string $dir Directory
	 * @return $this
	 */
	public function setCompileDir($dir)
	{
		$this->getEngine()->setCompileDir($dir);
		return $this;
	}

	/**
	 * Set cache path
	 *
	 * @param string $dir Directory
	 * @return $this
	 */
	public function setCacheDir($dir)
	{
		$this->getEngine()->setCacheDir($dir);
		return $this;
	}

	/**
	 * Set cache lifetime
	 *
	 * @param string $seconds Lifetime in seconds
	 * @return $this
	 */
	public function setCacheLifetime($seconds)
	{
		$this->getEngine()->setCacheTime($seconds);
		return $this;
	}

	/**
	 * Set charset
	 *
	 * @param string $charset
	 * @return $this
	 */
	public function setCharset($charset)
	{
		$this->_engine->setCharset($charset);
		return $this;
	}
}