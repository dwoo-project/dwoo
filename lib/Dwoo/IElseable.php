<?php

/**
 * interface that represents a block plugin that supports the else functionality.
 *
 * the else block will enter an "hasElse" parameter inside the parameters array
 * of the closest parent implementing this interface, the hasElse parameter contains
 * the else output that should be appended to the block's content (see foreach or other
 * block for examples)
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  2008-2013 Jordi Boggiano
 * @copyright  2013-2016 David Sanchez
 * @license    http://dwoo.org/LICENSE   Modified BSD License
 *
 * @link       http://dwoo.org/
 *
 * @version    1.2.3
 * @date       2016-10-15
 */
interface Dwoo_IElseable
{
}
