<?php
namespace Dwoo;
/**
 * interface that represents a dwoo data object
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
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
