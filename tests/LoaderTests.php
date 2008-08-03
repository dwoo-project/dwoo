<?php

require_once 'Dwoo/Compiler.php';

class LoaderTests extends PHPUnit_Framework_TestCase
{
	protected $compiler;
	protected $dwoo;

	public function __construct()
	{
		// extend this class and override this in your constructor to test a modded compiler
		$this->compiler = new Dwoo_Compiler();
		$this->dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
	}

	public function testLoaderGetSet()
	{
		$dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
		$loader = new Dwoo_Loader(TEST_DIRECTORY.'/temp/cache');

		$dwoo->setLoader($loader);
		$this->assertEquals($loader, $dwoo->getLoader());
	}

	public function testPluginLoad()
	{
		$dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
		$loader = new Dwoo_Loader(TEST_DIRECTORY.'/temp/cache');

		$dwoo->setLoader($loader);
		$loader->addDirectory(TEST_DIRECTORY.'/resources/plugins');

		$tpl = new Dwoo_Template_String('{loaderTest}');
		$tpl->forceCompilation();
		$this->assertEquals('Moo', $dwoo->get($tpl, array(), $this->compiler));
	}

	public function testRebuildClassPath()
	{
		$dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
		$loader = new Dwoo_Loader(TEST_DIRECTORY.'/temp/cache');

		$dwoo->setLoader($loader);
		$loader->addDirectory(TEST_DIRECTORY.'/resources/plugins');
		file_put_contents(TEST_DIRECTORY.'/resources/plugins/loaderTest2.php', '<?php function Dwoo_Plugin_loaderTest2(Dwoo $dwoo) { return "It works!"; }');

		$tpl = new Dwoo_Template_String('{loaderTest2}');
		$tpl->forceCompilation();
		$this->assertEquals('It works!', $dwoo->get($tpl, array(), $this->compiler));
		unlink(TEST_DIRECTORY.'/resources/plugins/loaderTest2.php');
	}
}
