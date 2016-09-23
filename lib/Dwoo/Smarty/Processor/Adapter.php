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
 * @version   1.3.0
 * @date      2016-09-23
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

    /**
     * @param string $input
     *
     * @return mixed
     */
    public function process($input)
    {
        return call_user_func($this->callback, $input);
    }

    /**
     * @param $callback
     */
    public function registerCallback($callback)
    {
        $this->callback = $callback;
    }
}