<?php

/**
 * Base class for filters.
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @category  Library
 * @package   Dwoo
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2016 David Sanchez
 * @license   http://dwoo.org/LICENSE   Modified BSD License
 * @version   Release: 1.2.4
 * @date      2016-10-15
 * @link      http://dwoo.org/
 */
abstract class Dwoo_Filter
{
    /**
     * The dwoo instance that runs this filter.
     *
     * @var Dwoo
     */
    protected $dwoo;

    /**
     * Constructor, if you override it, call parent::__construct($dwoo); or assign
     * the dwoo instance yourself if you need it.
     *
     * @param Dwoo_Core $dwoo the dwoo instance that runs this plugin
     */
    public function __construct(Dwoo_Core $dwoo)
    {
        $this->dwoo = $dwoo;
    }

    /**
     * Processes the input and returns it filtered.
     *
     * @param string $input the template to process
     *
     * @return string
     */
    abstract public function process($input);
}
