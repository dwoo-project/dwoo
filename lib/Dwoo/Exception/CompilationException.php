<?php
namespace Dwoo\Exception;

use Dwoo\Compiler;
use Dwoo\Exception;

/**
 * Dwoo compilation exception class
 *
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 *
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2013-2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2014-02-25
 * @package    Dwoo\Exception
 */
class CompilationException extends Exception {
	protected $compiler;
	protected $template;

	/**
	 * Constructor
	 * @param Compiler $compiler
	 * @param int      $message
	 */
	public function __construct(Compiler $compiler, $message) {
		$this->compiler = $compiler;
		$this->template = $compiler->getCore()->getTemplate();
		parent::__construct('Compilation error at line ' . $compiler->getLine() . ' in "' . $this->template->getResourceName() . ':' . $this->template->getResourceIdentifier() . '" : ' . $message);
	}

	/**
	 * Get compiler class
	 * @return Compiler
	 */
	public function getCompiler() {
		return $this->compiler;
	}

	/**
	 * Get class implementing ITemplate interface
	 * @return \Dwoo\ITemplate|null
	 */
	public function getTemplate() {
		return $this->template;
	}
}