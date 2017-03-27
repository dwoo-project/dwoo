<?php
/**
 * Copyright (c) 2013-2017
 *
 * @category  Library
 * @package   Dwoo\Compilation
 * @author    Jordi Boggiano <j.boggiano@seld.be>
 * @author    David Sanchez <david38sanchez@gmail.com>
 * @copyright 2008-2013 Jordi Boggiano
 * @copyright 2013-2017 David Sanchez
 * @license   http://dwoo.org/LICENSE LGPLv3
 * @version   1.3.6
 * @date      2017-03-21
 * @link      http://dwoo.org/
 */

namespace Dwoo\Compilation;

use Dwoo\Exception as DwooException;
use Dwoo\Compiler as DwooCompiler;

/**
 * Dwoo compilation exception class.
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 */
class Exception extends DwooException
{
    protected $compiler;
    protected $template;

    /**
     * Exception constructor.
     *
     * @param DwooCompiler $compiler
     * @param int          $message
     */
    public function __construct(DwooCompiler $compiler, $message)
    {
        $this->compiler = $compiler;
        $this->template = $compiler->getCore()->getTemplate();
        parent::__construct('Compilation error at line ' . $compiler->getLine() . ' in "' . $this->template->getResourceName() . ':' . $this->template->getResourceIdentifier() . '" : ' . $message);
    }

    /**
     * @return DwooCompiler
     */
    public function getCompiler()
    {
        return $this->compiler;
    }

    /**
     * @return \Dwoo\ITemplate|null
     */
    public function getTemplate()
    {
        return $this->template;
    }
}
