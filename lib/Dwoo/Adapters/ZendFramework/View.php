<?php

/**
 * View interface for Dwoo template engine based on
 * Zend_View_Abstract
 *
 * @package		 Dwoo
 * @author		 Denis Arh <denis@arh.cc>
 * @author       Stephan Wentz <stephan@wentz.it>
 * @author       Jordi Boggiano <j.boggiano@seld.be>
 */
class Dwoo_Adapters_ZendFramework_View extends Zend_View_Abstract
{
	/**
	 * @var Dwoo
	 */
	protected $_engine = null;

	/**
	 *
	 * @var Dwoo_Data
	 */
	protected $_data = null;

	/**
	 *
	 * @var Dwoo_IPluginProxy
	 */
	protected $_pluginProxy = null;

	protected $_scriptPaths;

	public function __construct(array $opt = array())
	{
		if (!isset($opt['compile_dir'])) {
			$opt['compile_dir'] = null;
		}
		if (!isset($opt['cache_dir'])) {
			$opt['cache_dir'] = null;
		}

		$this->_engine = new Dwoo($opt['compile_dir'], $opt['cache_dir']);
		$this->_data = new Dwoo_Data;
	}

	/**
	 * Called before template rendering
	 *
	 * Binds plugin proxy to the Dwoo.
	 *
	 * @see Dwoo_Adapters_ZendFramework_View::getPluginProxy();
	 * @see Dwoo::setPluginProxy();
	 */
	protected function preRender()
 	{
		$this->_engine->setPluginProxy($this->getPluginProxy());
	}

	/**
	 * Set the path to find the view script used by render()
	 *
	 * @param string|array The directory (-ies) to set as the path. Note that
	 * the concrete view implentation may not necessarily support multiple
	 * directories.
	 *
	 * @return void
	 */
	public function setScriptPath($path)
	{
		$this->_scriptPaths = (array) $path;
	}

	/**
	 * Retrieve all view script paths
	 *
	 * @return array
	 */
	public function getScriptPaths()
	{
		return $this->_scriptPaths;
	}

	/**
	 * Set a base path to all view resources
	 *
	 * @param  string $path        Base path
	 * @param  string $classPrefix Class prefix
	 *
	 * @return void
	 */
	public function setBasePath($path, $classPrefix = 'Zend_View')
	{
		// Not supported by Dwoo
	}

	/**
	 * Add an additional path to view resources
	 *
	 * @param  string $path        Base path
	 * @param  string $classPrefix Class prefix
	 * @return void
	 */
	public function addBasePath($path, $classPrefix = 'Zend_View')
	{
		// Not supported by Dwoo
	}

	/**
	 * Wraper for Dwoo_Data::__set()
 	 * allows to assign variables using the object syntax
	 *
	 * @param string $name the variable name
	 * @param string $value the value to assign to it
	 */
   	public function __set($name, $value)
   	{
   		$this->_data->__set($name, $value);
   	}

	public function __get($name)
	{
		return $this->_data->__get($name);
	}

   	/**
	 * Wraper for Dwoo_Data::__isset()
 	 * supports calls to isset($dwooData->var)
 	 *
	 * @param string $name the variable name
	 */
   	public function __isset($name)
   	{
   		return $this->_data->__isset($name);
   	}

	/**
	 * Wraper for Dwoo_Data::_unset()
	 * supports unsetting variables using the object syntax
	 *
	 * @see Dwoo_Data::_unset()
	 * @param string $name the variable name
	 */
	public function __unset($name)
	{
		$this->_data->__unset($name);
	}

	/**
	 * Returns plugin proxy interface
	 *
	 * @return Dwoo_IPluginProxy
	 */
	public function getPluginProxy()
	{
		if (!$this->_pluginProxy) {
			$this->_pluginProxy = new Dwoo_Adapters_ZendFramework_PluginProxy($this);
		}

		return $this->_pluginProxy;
	}

	/**
	 * Adds plugin proxy
	 *
	 * @param Dwoo_IPluginProxy
	 * @return Dwoo_Adapters_ZendFramework_View
	 */
	public function setPluginProxy(Dwoo_IPluginProxy $pluginProxy)
	{
		$this->_pluginProxy = $pluginProxy;
		return $this;
	}

	/**
	 * Returns data object
	 *
	 * @return Dwoo_Data
	 */
	public function getData()
	{
		return $this->_data;
	}

	/**
	 * Passes data to Dwoo_Data object
	 *
	 * @see Dwoo_Data::assign()
	 * @param array|string $name
	 * @param mixed $val
	 * @return Dwoo_Adapters_ZendFramework_View
	 */
	public function assign($name, $val = null)
	{
		$this->_data->assign($name, $val);
		return $this;
	}

	/**
	 * Return the Dwoo template engine object
	 *
	 * @return Dwoo
	 */
	public function getEngine()
	{
		return $this->_engine;
	}

	/**
	 * Clear all assigned variables
	 *
	 * Clears all variables assigned to Zend_View either via {@link assign()} or
	 * property overloading ({@link __get()}/{@link __set()}).
	 *
	 * @return void
	 */
	public function clearVars()
	{
		$this->_data->clear();
	}

	public function render($name) {
		$this->preRender();

		return $this->_filter($this->_run($name));
	}

	/**
	 * Processes a view script and returns the output.
	 *
	 * @param string $name The script script name to process.
	 * @return string The script output.
	 */
	public function _run($name)
	{
		$tpl = new Dwoo_Template_File($name, null, null, null, $this->_scriptPaths);
		return $this->_engine->get($tpl, $this->_data);
	}

	/**
	 * Add plugin path
	 *
	 * @param string $dir Directory
	 */
	public function addPluginDir($dir)
	{
		$this->_engine->getLoader()->addDirectory($dir);
	}

	/**
	 * Set compile path
	 *
	 * @param string $dir Directory
	 */
	public function setCompileDir($dir)
	{
		$this->_engine->setCompileDir($dir);
	}

	/**
	 * Set cache path
	 *
	 * @param string $dir Directory
	 */
	public function setCacheDir($dir)
	{
		$this->_engine->setCacheDir($dir);
	}

	/**
	 * Set cache lifetime
	 *
	 * @param string $seconds Lifetime in seconds
	 */
	public function setCacheLifetime($seconds)
	{
		$this->_engine->setCacheTime($seconds);
	}

	/**
	 * Set charset
	 *
	 * @param string $charset
	 */
	public function setCharset($charset)
	{
		$this->_engine->setCharset($charset);
	}
}
