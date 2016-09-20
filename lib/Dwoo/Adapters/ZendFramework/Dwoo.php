<?php

/**
 * Copyright (c) 2013-2016
 *
 * @category  Library
 * @package   Dwoo\Adapters\ZendFramework
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2016 David Sanchez
 * @license   http://dwoo.org/LICENSE Modified BSD License
 * @version   1.3.0
 * @date      2016-09-19
 * @link      http://dwoo.org/
 */
class Dwoo_Adapters_ZendFramework_Dwoo extends Dwoo_Core
{
    /**
     * Redirects all unknown properties to plugin proxy
     * to support $this->viewVariable from within templates.
     *
     * @param string $name Property name
     *
     * @return mixed
     */
    public function __get($name)
    {
        if (isset($this->getPluginProxy()->view->$name)) {
            return $this->getPluginProxy()->view->$name;
        }
        $trace = debug_backtrace();
        trigger_error(
            'Undefined property via __get(): '.$name.
                      ' in '.$trace[0]['file'].
            ' on line '.$trace[0]['line'], E_USER_NOTICE
        );

        return null;
    }
}
