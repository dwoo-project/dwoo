<?php

require_once 'Dwoo/Compiler.php';
require 'Dwoo/Smarty/Adapter.php';

class SmartyTests extends PHPUnit_Framework_TestCase
{
	protected $dwoo;

	public function __construct()
	{
		// extend this class and override this in your constructor to test a modded compiler
		$this->dwoo = new Dwoo_Smarty_Adapter();
		$this->dwoo->template_dir = DWOO_DIRECTORY.'tests/resources/';
		$this->dwoo->compile_dir = DWOO_DIRECTORY.'tests/temp/compiled/';
		$this->dwoo->cache_dir = DWOO_DIRECTORY.'tests/temp/cache/';
		$this->dwoo->config_dir = DWOO_DIRECTORY.'tests/resources/configs/';
		$this->compiler = new Dwoo_Compiler();
		$this->compiler->addPreProcessor('smarty_compat', true);
	}

	public function testSmartyCompat()
	{
		$this->assertEquals('{'.Dwoo::VERSION.'}', $this->dwoo->fetch('smartytest.html'));
	}
}

?>