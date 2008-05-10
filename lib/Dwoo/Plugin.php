<?php

/**
 * base plugin class
 *
 * you have to implement the <em>process()</em> method, it will receive the parameters that
 * are in the template code
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
abstract class Dwoo_Plugin
{
	/**
	 * the dwoo instance that runs this plugin
	 *
	 * @var Dwoo
	 */
	protected $dwoo;

	/**
	 * constructor, if you override it, call parent::__construct($dwoo); or assign
	 * the dwoo instance yourself if you need it
	 *
	 * @param Dwoo $dwoo the dwoo instance that runs this plugin
	 */
	public function __construct(Dwoo $dwoo)
	{
		$this->dwoo = $dwoo;
	}

	// plugins that have arguments should always implement :
	// public function process($arg, $arg, ...)
	// or for block plugins :
	// public function init($arg, $arg, ...)

	// this could be enforced with :
	// public function process(...)
	// if my bug entry gets enough interest one day..
	// see => http://bugs.php.net/bug.php?id=44043
}
