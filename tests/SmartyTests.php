<?php

require_once dirname(dirname(__FILE__)).'/DwooCompiler.php';
require DWOO_DIRECTORY.'DwooSmartyAdapter.php';

class SmartyTests extends PHPUnit_Framework_TestCase
{
	protected $dwoo;

	public function __construct()
	{
		// extend this class and override this in your constructor to test a modded compiler
		$this->dwoo = new DwooSmartyAdapter();
		$this->dwoo->template_dir = DWOO_DIRECTORY.'tests/resources/';
		$this->dwoo->compile_dir = DWOO_DIRECTORY.'tests/temp/compiled/';
		$this->dwoo->cache_dir = DWOO_DIRECTORY.'tests/temp/cache/';
		$this->dwoo->config_dir = DWOO_DIRECTORY.'tests/resources/configs/';
		$this->compiler = new DwooCompiler();
		$this->compiler->addPreProcessor('smarty_compat', true);
	}

    public function testSmartyCompat()
    {
        $this->assertEquals('{'.Dwoo::VERSION.'}', $this->dwoo->fetch('smartytest.html'));
    }
}

?>