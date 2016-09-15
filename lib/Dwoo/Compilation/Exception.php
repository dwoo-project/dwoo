<?php

/**
 * dwoo compilation exception class
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  2008-2013 Jordi Boggiano
 * @copyright  2013-2016 David Sanchez
 * @license    http://dwoo.org/LICENSE   Modified BSD License
 * @link       http://dwoo.org/
 * @version    1.2.3
 * @date       2016-10-15
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
