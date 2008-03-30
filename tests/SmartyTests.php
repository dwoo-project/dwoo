<?php

require_once dirname(dirname(__FILE__)).'/DwooCompiler.php';
require DWOO_PATH.'DwooSmartyAdapter.php';

class SmartyTests extends PHPUnit_Framework_TestCase
{
	protected $dwoo;

	public function __construct()
	{
		// extend this class and override this in your constructor to test a modded compiler
		$this->dwoo = new DwooSmartyAdapter();
		$this->dwoo->template_dir = DWOO_PATH.'tests/resources/';
		$this->dwoo->compile_dir = DWOO_PATH.'compiled/';
		$this->dwoo->cache_dir = DWOO_PATH.'cache/';
		$this->dwoo->config_dir = DWOO_PATH.'configs/';
	}

    public function testSmartyCompat()
    {
        $this->assertEquals('{'.Dwoo::VERSION.'}', $this->dwoo->fetch('smartytest.html'));
    }
}

?>