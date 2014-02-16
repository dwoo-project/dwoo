<?php
namespace Dwoo;

/**
 * interface that represents a block plugin that supports the else functionality
 *
 * the else block will enter an "hasElse" parameter inside the parameters array
 * of the closest parent implementing this interface, the hasElse parameter contains
 * the else output that should be appended to the block's content (see foreach or other
 * block for examples)
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-01
 * @package    Dwoo
 */
interface IElseable {
	
}
