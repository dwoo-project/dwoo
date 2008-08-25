<?php

/**
 * dwoo compilation exception class
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
class Dwoo_Compilation_Exception extends Dwoo_Exception
{
	protected $compiler;
	protected $template;

	public function __construct(Dwoo_Compiler $compiler, $message)
	{
		$this->compiler = $compiler;
		$this->template = $compiler->getDwoo()->getTemplate();
		parent::__construct('Compilation error at line '.$compiler->getLine().' in "'.$this->template->getResourceName().':'.$this->template->getResourceIdentifier().'" : '.$message);
	}

	public function getCompiler()
	{
		return $this->compiler;
	}

	public function getTemplate()
	{
		return $this->template;
	}
}
