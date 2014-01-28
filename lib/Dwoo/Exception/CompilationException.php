<?php
namespace Dwoo\Exception;

use Dwoo\Compiler;

/**
 * dwoo compilation exception class
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE   Modified BSD License
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-08
 * @package    Dwoo
 */
class CompilationException extends \Dwoo\Exception {
	protected $compiler;
	protected $template;

	/**
	 * @param Compiler $compiler
	 * @param int      $message
	 */
	public function __construct(Compiler $compiler, $message) {
		$this->compiler = $compiler;
		$this->template = $compiler->getDwoo()->getTemplate();
		parent::__construct('Compilation error at line ' . $compiler->getLine() . ' in "' . $this->template->getResourceName() . ':' . $this->template->getResourceIdentifier() . '" : ' . $message);
	}

	/**
	 * @return Compiler
	 */
	public function getCompiler() {
		return $this->compiler;
	}

	/**
	 * @return \Dwoo\ITemplate|null
	 */
	public function getTemplate() {
		return $this->template;
	}
}