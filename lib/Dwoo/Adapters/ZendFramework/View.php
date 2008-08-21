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
	 * @var Dwoo_Data
	 */
	protected $_data = null;

	/**
	 * @var Dwoo_IPluginProxy
	 */
	protected $_pluginProxy = null;

	/**
	 * Constructor method. Opt array::
	 *  - compile_dir Where to store compiled template
	 *  - cache_dir   Cache files location
	 *
	 * @param array $opt
	 */
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

		$this->init();
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
	 * Wraper for Dwoo_Data::__set()
 	 * allows to assign variables using the object syntax
	 *
	 * @see Dwoo_Data::__set()
	 * @param string $name the variable name
	 * @param string $value the value to assign to it
	 */
 	public function __set($name, $value)
 	{
 		$this->_data->__set($name, $value);
 	}

 	/**
 	 * Sraper for Dwoo_Data::__get()
 	 * allows to read variables using the object syntax
 	 *
 	 * @see Dwoo_Data::__get()
 	 * @param string $name the variable name
 	 * @return mixed
 	 */
 	public function __get($name)
 	{
 	  return $this->_data->__get($name);
 	}

 	/**
	 * Wraper for Dwoo_Data::__isset()
 	 * supports calls to isset($dwooData->var)
 	 *
 	 * @see Dwoo_Data::__isset()
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
	 * @see Dwoo_Data::__unset()
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
	 * @return Dwoo_Adapters_ZendFramework_View
	 */
	public function clearVars()
	{
		$this->_data->clear();
		return $this;
	}

	public function render($name) {
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
		$tpl = new Dwoo_Template_File(func_get_arg(0));
		echo $this->_engine->get($tpl, $this->_data);
	}

	/**
	 * Add plugin path
	 *
	 * @param string $dir Directory
	 * @return Dwoo_Adapters_ZendFramework_View
	 */
	public function addPluginDir($dir)
	{
		$this->_engine->getLoader()->addDirectory($dir);
		return $this;
	}

	/**
	 * Set compile path
	 *
	 * @param string $dir Directory
	 * @return Dwoo_Adapters_ZendFramework_View
	 */
	public function setCompileDir($dir)
	{
		$this->_engine->setCompileDir($dir);
		return $this;
	}

	/**
	 * Set cache path
	 *
	 * @param string $dir Directory
	 * @return Dwoo_Adapters_ZendFramework_View
	 */
	public function setCacheDir($dir)
	{
		$this->_engine->setCacheDir($dir);
		return $this;
	}

	/**
	 * Set cache lifetime
	 *
	 * @param string $seconds Lifetime in seconds
	 * @return Dwoo_Adapters_ZendFramework_View
	 */
	public function setCacheLifetime($seconds)
	{
		$this->_engine->setCacheTime($seconds);
		return $this;
	}

	/**
	 * Set charset
	 *
	 * @param string $charset
	 * @return Dwoo_Adapters_ZendFramework_View
	 */
	public function setCharset($charset)
	{
		$this->_engine->setCharset($charset);
		return $this;
	}

   /**
     * Given a base path, add script, helper, and filter paths relative to it
     *
     * Assumes a directory structure of:
     * <code>
     * basePath/ (view scripts)
     *     helpers/
     *     filters/
     * </code>
     *
     * @param  string $path
     * @param  string $prefix Prefix to use for helper and filter paths
     * @return Zend_View_Abstract
     */
    public function addBasePath($path, $classPrefix = 'Zend_View')
    {
        $path        = rtrim($path, '/');
        $path        = rtrim($path, '\\');
        $path       .= DIRECTORY_SEPARATOR;
        $classPrefix = rtrim($classPrefix, '_') . '_';
        $this->addScriptPath($path);
        $this->addHelperPath($path . 'helpers', $classPrefix . 'Helper');
        $this->addFilterPath($path . 'filters', $classPrefix . 'Filter');
        return $this;
    }

    /**
     * Given a base path, sets the script, helper, and filter paths relative to it
     *
     * Assumes a directory structure of:
     * <code>
     * basePath/ (view scripts)
     *     helpers/
     *     filters/
     * </code>
     *
     * @param  string $path
     * @param  string $prefix Prefix to use for helper and filter paths
     * @return Zend_View_Abstract
     */
    public function setBasePath($path, $classPrefix = 'Zend_View')
	{
        $path        = rtrim($path, '/');
        $path        = rtrim($path, '\\');
        $path       .= DIRECTORY_SEPARATOR;
        $classPrefix = rtrim($classPrefix, '_') . '_';
        $this->setScriptPath($path);
        $this->setHelperPath($path . 'helpers', $classPrefix . 'Helper');
        $this->setFilterPath($path . 'filters', $classPrefix . 'Filter');
        return $this;
    }
}