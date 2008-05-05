<?php

/**
 * base plugin class
 *
 * you have to implement the <em>process()</em> method, it will receive the parameters that
 * are in the template code
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
 * @version    0.3.4
 * @date       2008-04-09
 * @package    Dwoo
 */
abstract class Dwoo_Plugin
{
	/**
	 * the dwoo instance that runs this plugin
	 *
	 * @var Dwoo
	 */
	protected $dwoo;

	/**
	 * constructor, if you override it, call parent::__construct($dwoo); or assign
	 * the dwoo instance yourself if you need it
	 *
	 * @param Dwoo $dwoo the dwoo instance that runs this plugin
	 */
	public function __construct(Dwoo $dwoo)
	{
		$this->dwoo = $dwoo;
	}

	// plugins that have arguments should always implement :
	// public function process($arg, $arg, ...)
	// or for block plugins :
	// public function init($arg, $arg, ...)

	// this could be enforced with :
	// public function process(...)
	// if my bug entry gets enough interest one day..
	// see => http://bugs.php.net/bug.php?id=44043
}

/**
 * base class for block plugins
 *
 * you have to implement the <em>init()</em> method, it will receive the parameters that
 * are in the template code and is called when the block starts
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
 * @version    0.3.4
 * @date       2008-04-09
 * @package    Dwoo
 */
abstract class Dwoo_Block_Plugin extends Dwoo_Plugin
{
	/**
	 * stores the contents of the block while it runs
	 *
	 * @var string
	 */
	protected $buffer = '';

	/**
	 * buffers input, override only if necessary
	 *
	 * @var string $input the content that must be buffered
	 */
	public function buffer($input)
	{
		$this->buffer .= $input;
	}

	// initialization code, receives the parameters from {block param1, param2}
	// public function init($arg, $arg, ...);

	/**
	 * called when the block ends, this is most of the time followed right away by a call
	 * of <em>process()</em> but not always, so this should be used to do any shutdown operations on the
	 * block object, if required.
	 */
	public function end()
	{
	}

	/**
	 * called when the block output is required by a parent block
	 *
	 * this must read $this->buffer and return it processed
	 *
	 * @return string
	 */
	public function process()
	{
		return $this->buffer;
	}

	/**
	 * called at compile time to define what the block should output in the compiled template code, happens when the block is declared
	 *
	 * basically this will replace the {block arg arg arg} tag in the template
	 *
	 * @param Dwoo_Compiler $compiler the compiler instance that calls this function
	 * @param array $params an array containing original and compiled parameters
	 * @param string $prepend that is just meant to allow a child class to call
	 * 						  parent::postProcessing($compiler, $params, "foo();") to add a command before the
	 * 						  default commands are executed
	 * @param string $append that is just meant to allow a child class to call
	 * 						 parent::postProcessing($compiler, $params, null, "foo();") to add a command after the
	 * 						 default commands are executed
	 * @param string $type the type is the plugin class name used
	 */
	public static function preProcessing(Dwoo_Compiler $compiler, array $params, $prepend='', $append='', $type)
	{
		return Dwoo_Compiler::PHP_OPEN.$prepend.'$this->addStack("'.$type.'", array('.implode(', ', $compiler->getCompiledParams($params)).'));'.$append.Dwoo_Compiler::PHP_CLOSE;
	}

	/**
	 * called at compile time to define what the block should output in the compiled template code, happens when the block is ended
	 *
	 * basically this will replace the {/block} tag in the template
	 *
	 * @see preProcessing
	 * @param Dwoo_Compiler $compiler the compiler instance that calls this function
	 * @param array $params an array containing original and compiled parameters, see preProcessing() for more details
	 * @param string $prepend that is just meant to allow a child class to call
	 * 						  parent::postProcessing($compiler, $params, "foo();") to add a command before the
	 * 						  default commands are executed
	 * @param string $append that is just meant to allow a child class to call
	 * 						 parent::postProcessing($compiler, $params, null, "foo();") to add a command after the
	 * 						 default commands are executed
	 */
	public static function postProcessing(Dwoo_Compiler $compiler, array $params, $prepend='', $append='')
	{
		return Dwoo_Compiler::PHP_OPEN.$prepend.'$this->delStack();'.$append.Dwoo_Compiler::PHP_CLOSE;
	}
}

/**
 * base class for filters
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
 * @version    0.3.4
 * @date       2008-04-09
 * @package    Dwoo
 */
abstract class Dwoo_Filter
{
	/**
	 * the dwoo instance that runs this filter
	 *
	 * @var Dwoo
	 */
	protected $dwoo;

	/**
	 * constructor, if you override it, call parent::__construct($dwoo); or assign
	 * the dwoo instance yourself if you need it
	 *
	 * @param Dwoo $dwoo the dwoo instance that runs this plugin
	 */
	public function __construct(Dwoo $dwoo)
	{
		$this->dwoo = $dwoo;
	}

	/**
	 * processes the input and returns it filtered
	 *
	 * @param string $input the template to process
	 * @return string
	 */
	abstract public function process($input);
}

/**
 * base class for processors
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
 * @version    0.3.4
 * @date       2008-04-09
 * @package    Dwoo
 */
abstract class Dwoo_Processor
{
	/**
	 * the compiler instance that runs this processor
	 *
	 * @var Dwoo
	 */
	protected $compiler;

	/**
	 * constructor, if you override it, call parent::__construct($dwoo); or assign
	 * the dwoo instance yourself if you need it
	 *
	 * @param Dwoo $dwoo the dwoo instance that runs this plugin
	 */
	public function __construct(Dwoo_Compiler $compiler)
	{
		$this->compiler = $compiler;
	}

	/**
	 * processes the input and returns it filtered
	 *
	 * @param string $input the template to process
	 * @return string
	 */
	abstract public function process($input);
}

/**
 * interface that represents a compilable plugin
 *
 * implement this to notify the compiler that this plugin does not need to be loaded at runtime.
 *
 * to implement it right, you must implement <em>public static function compile(Dwoo_Compiler $compiler, $arg, $arg, ...)</em>,
 * which replaces the <em>process()</em> method (that means <em>compile()</em> should have all arguments it requires).
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
 * @version    0.3.4
 * @date       2008-04-09
 * @package    Dwoo
 */
interface Dwoo_ICompilable
{
	// this replaces the process function
	//public static function compile(Dwoo_Compiler $compiler, $arg, $arg, ...);
}

/**
 * interface that represents a compilable block plugin
 *
 * implement this to notify the compiler that this plugin does not need to be loaded at runtime.
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
 * @version    0.3.4
 * @date       2008-04-09
 * @package    Dwoo
 */
interface Dwoo_ICompilable_Block
{
}
