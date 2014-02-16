<?php
namespace Dwoo\Block;
use Dwoo\Compiler;

/**
 * base class for block plugins
 * you have to implement the <em>init()</em> method, it will receive the parameters that
 * are in the template code and is called when the block starts
 * This software is provided 'as-is', without any express or implied warranty.
 * In no event will the authors be held liable for any damages arising from the use of this software.
 * @author     David Sanchez <david38sanchez@gmail.com>
 * @copyright  Copyright (c) 2014, David Sanchez
 * @license    http://dwoo.org/LICENSE GNU Lesser General Public License v3.0
 * @link       http://dwoo.org/
 * @version    2.0
 * @date       2013-09-03
 * @package    Dwoo
 */
abstract class Plugin extends \Dwoo\Plugin {
	/**
	 * stores the contents of the block while it runs
	 * @var string
	 */
	protected $buffer = '';

	/**
	 * buffers input, override only if necessary
	 * @var string $input the content that must be buffered
	 */
	public function buffer($input) {
		$this->buffer .= $input;
	}

	// initialization code, receives the parameters from {block param1 param2}
	// public function init($arg, $arg, ...);

	/**
	 * called when the block ends, this is most of the time followed right away by a call
	 * of <em>process()</em> but not always, so this should be used to do any shutdown operations on the
	 * block object, if required.
	 */
	public function end() {
	}

	/**
	 * called when the block output is required by a parent block
	 * this must read $this->buffer and return it processed
	 * @return string
	 */
	public function process() {
		return $this->buffer;
	}

	/**
	 * called at compile time to define what the block should output in the compiled template code, happens when the block is declared
	 * basically this will replace the {block arg arg arg} tag in the template
	 * @param Compiler $compiler the compiler instance that calls this function
	 * @param array    $params   an array containing original and compiled parameters
	 * @param string   $prepend  that is just meant to allow a child class to call
	 *                           parent::postProcessing($compiler, $params, "foo();") to add a command before the
	 *                           default commands are executed
	 * @param string   $append   that is just meant to allow a child class to call
	 *                           parent::postProcessing($compiler, $params, null, "foo();") to add a command after the
	 *                           default commands are executed
	 * @param string   $type     the type is the plugin class name used
	 *
	 * @return string
	 */
	public static function preProcessing(Compiler $compiler, array $params, $prepend, $append, $type) {
		return Compiler::PHP_OPEN . $prepend . '$this->addStack("' . $type . '", array(' . Compiler::implode_r($compiler->getCompiledParams($params)) . '));' . $append . Compiler::PHP_CLOSE;
	}

	/**
	 * called at compile time to define what the block should output in the compiled template code, happens when the block is ended
	 * basically this will replace the {/block} tag in the template
	 * @see preProcessing
	 * @param Compiler $compiler the compiler instance that calls this function
	 * @param array    $params   an array containing original and compiled parameters, see preProcessing() for more details
	 * @param string   $prepend  that is just meant to allow a child class to call
	 *                           parent::postProcessing($compiler, $params, "foo();") to add a command before the
	 *                           default commands are executed
	 * @param string   $append   that is just meant to allow a child class to call
	 *                           parent::postProcessing($compiler, $params, null, "foo();") to add a command after the
	 *                           default commands are executed
	 * @param string   $content  the entire content of the block being closed
	 * @return string
	 */
	public static function postProcessing(Compiler $compiler, array $params, $prepend, $append, $content) {
		return $content . Compiler::PHP_OPEN . $prepend . '$this->delStack();' . $append . Compiler::PHP_CLOSE;
	}
}
