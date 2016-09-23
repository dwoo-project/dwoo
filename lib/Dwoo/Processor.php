<?php
/**
 * Copyright (c) 2013-2016
 *
 * @category  Library
 * @package   Dwoo
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2016 David Sanchez
 * @license   http://dwoo.org/LICENSE Modified BSD License
 * @version   1.3.0
 * @date      2016-09-23
 * @link      http://dwoo.org/
 */

namespace Dwoo;

/**
 * Base class for processors.
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
abstract class Processor
{
    /**
     * The compiler instance that runs this processor.
     *
     * @var Core
     */
    protected $compiler;

    /**
     * Constructor, if you override it, call parent::__construct($compiler); or assign
     * the dwoo instance yourself if you need it.
     *
     * @param Compiler $compiler the compiler class
     */
    public function __construct(Compiler $compiler)
    {
        $this->compiler = $compiler;
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
