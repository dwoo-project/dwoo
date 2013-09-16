<?php
namespace Dwoo;
/**
 * interface that represents a dwoo data object
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @copyright  Copyright (c) 2008, Jordi Boggiano
 * @license    http://dwoo.org/LICENSE   Modified BSD License
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-08
 * @package    Dwoo
 */
interface IDataProvider {
	/**
	 * returns the data as an associative array that will be used in the template
	 *
	 * @return array
	 */
	public function getData();
}
