<?php
/**
 * Copyright (c) 2013-2016
 *
 * @category  Library
 * @package   Dwoo\Smarty\Processor
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2016 David Sanchez
 * @license   http://dwoo.org/LICENSE Modified BSD License
 * @version   1.2.4
 * @date      2016-09-17
 * @link      http://dwoo.org/
 */

namespace Dwoo\Smarty\Processor;

use Dwoo\Processor;

/**
 * Class Adapter
 */
class Adapter extends Processor
{
    public $callback;

    public function process($input)
    {
        return call_user_func($this->callback, $input);
    }

    public function registerCallback($callback)
    {
        $this->callback = $callback;
    }
}