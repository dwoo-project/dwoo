<?php
namespace Dwoo;

/**
 * SplAutoloader defines the contract that any OO based autoloader must follow.
 *
 * @author     Guilherme Blanco <guilhermeblanco@php.net>
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2013-2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2014-02-16
 * @package    Dwoo
 */
interface IAutoloader {
	/**
	 * Defines autoloader to work silently if resource is not found.
	 * @const
	 */
	const MODE_SILENT = 0;

	/**
	 * Defines autoloader to work normally (requiring an un-existent resource).
	 * @const
	 */
	const MODE_NORMAL = 1;

	/**
	 * Defines autoloader to work in debug mode, loading file and validating requested resource.
	 * @const
	 */
	const MODE_DEBUG = 2;

	/**
	 * Define the autoloader work mode.
	 * @param integer $mode Autoloader work mode.
	 * @throws \InvalidArgumentException
	 * @return $this
	 */
	public function setMode($mode);

	/**
	 * Add a new resource lookup path.
	 * @param string $resource
	 * @param mixed  $resourcePath Resource single path or multiple paths (array).
	 * @internal param string $resource
	 * @internal param string $resourceName Resource name, namespace or prefix.
	 * @return $this
	 */
	public function add($resource, $resourcePath = null);

	/**
	 * Register this as an autoloader instance.
	 * @param bool $prepend Whether to prepend the autoloader or not in autoloader's list.
	 */
	public function register($prepend = false);

	/**
	 * Unregister this autoloader instance.
	 */
	public function unregister();
}