<?php
namespace Dwoo\Smarty\Filter;

use Dwoo\Filter;

/**
 * Class Adapter
 *
 * @category  Library
 * @package   Dwoo\Smarty\Filter
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2016 David Sanchez
 * @license   http://dwoo.org/LICENSE Modified BSD License
 * @version   Release: 1.2.4
 * @date      2016-10-16
 * @link      http://dwoo.org/
 */
class Adapter extends Filter
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