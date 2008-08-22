<?php

/**
 * PluginProxy class for Zend View
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the
 * use of this software.
 *
 * This file is released under the LGPL
 * "GNU Lesser General Public License"
 * More information can be found here:
 * {@link http://www.gnu.org/copyleft/lesser.html}
 *
 * @author	   Denis Arh <denis@arh.cc>
 * @copyright  Copyright (c) 2008, Denis Arh
 * @license    http://www.gnu.org/copyleft/lesser.html  GNU Lesser General Public License
 * @link       http://dwoo.org/
 * @version    1.0.0
 * @date       2008-08-17
 * @package    Dwoo
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