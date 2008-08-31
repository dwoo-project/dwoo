<?php

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
 * This file is released under the LGPL
 * "GNU Lesser General Public License"
 * More information can be found here:
 * {@link http://www.gnu.org/copyleft/lesser.html}
 *
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @copyright  Copyright (c) 2008, Jordi Boggiano
 * @license    http://www.gnu.org/copyleft/lesser.html  GNU Lesser General Public License
 * @link       http://dwoo.org/
 * @version    0.9.1
 * @date       2008-05-30
 * @package    Dwoo
 */
interface Dwoo_IElseable
{
}
