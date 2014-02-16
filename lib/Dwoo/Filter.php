<?php
namespace Dwoo;

/**
 * base class for filters
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-03
 * @package    Dwoo
 */
abstract class Filter {
	/**
	 * the dwoo instance that runs this filter
	 * @var Core
	 */
	protected $dwoo;

	/**
	 * constructor, if you override it, call parent::__construct($dwoo); or assign
	 * the dwoo instance yourself if you need it
	 * @param Core $dwoo the dwoo instance that runs this plugin
	 */
	public function __construct(Core $dwoo) {
		$this->dwoo = $dwoo;
	}

	/**
	 * processes the input and returns it filtered
	 * @param string $input the template to process
	 * @return string
	 */
	abstract public function process($input);
}
