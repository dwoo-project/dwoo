<?php

/**
 * PluginProxy class for Zend View
 *
 * @package		 Dwoo
 * @author		 Denis Arh <denis@arh.cc>
 */
class Dwoo_Adapters_ZendFramework_PluginProxy implements Dwoo_IPluginProxy
{
	/**
	 * reference to the zend view owning this proxy
	 *
	 * @var Zend_View_Interface
	 */
	public $view;

	/**
	 * Dwoo_Adapters_ZendFramework_PluginProxy's constructor.
	 *
	 * @param Zend_View_Interface $view
	 */
	public function __construct(Zend_View_Interface $view) {
		$this->view = $view;
	}

	/**
	 * Called from Dwoo_Compiler to check if the requested plugin is available
	 *
	 * @param string $name
	 * @return bool
	 */
	public function loadPlugin($name) {
		try {
			$this->view->getHelper($name);
		} catch (Zend_Loader_PluginLoader_Exception $e) {
			return false;
		}

		return true;
	}

	/**
	 * Catch-all method for Zend view helpers. It generates code for
	 * Dwoo templates.
	 *
	 * @param string $name Name of the view helper
	 * @param array  $args Helper's parameters
	 * @return string
	 */
	public function __call($name, $args) {
		return '$this->getPluginProxy()->view->'. $name .'('.Dwoo_Compiler::implode_r($args).')';
	}
}