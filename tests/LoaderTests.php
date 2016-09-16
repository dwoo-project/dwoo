<?php

class LoaderTests extends PHPUnit_Framework_TestCase
{
    protected $compiler;
    protected $dwoo;

    public function __construct()
    {
        // extend this class and override this in your constructor to test a modded compiler
        $this->compiler = new Dwoo\Compiler();
        $this->dwoo = new Dwoo\Core(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
    }

    public function testLoaderGetSet()
    {
        $dwoo = new Dwoo\Core(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
        $loader = new Dwoo\Loader(TEST_DIRECTORY.'/temp/cache');

        $dwoo->setLoader($loader);
        $this->assertEquals($loader, $dwoo->getLoader());
    }

    public function testPluginLoad()
    {
        $dwoo = new Dwoo\Core(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
        $loader = new Dwoo\Loader(TEST_DIRECTORY.'/temp/cache');

        $dwoo->setLoader($loader);
        $loader->addDirectory(TEST_DIRECTORY.'/resources/plugins');

        $tpl = new Dwoo\Template\String('{loaderTest}');
        $tpl->forceCompilation();
        $this->assertEquals('Moo', $dwoo->get($tpl, array(), $this->compiler));
    }

//	public function testRebuildClassPath()
//	{
//		$dwoo = new Dwoo\Core(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
//		$loader = new Dwoo_Loader(TEST_DIRECTORY.'/temp/cache');

//		$dwoo->setLoader($loader);
//		file_put_contents(TEST_DIRECTORY.'/resources/plugins/loaderTest2.php', '<?php function Dwoo_Plugin_loaderTest2(Dwoo\Core $dwoo) { return "It works!"; }');
//		$loader->addDirectory(TEST_DIRECTORY.'/resources/plugins');

//		$tpl = new Dwoo\Template\String('{loaderTest2}');
//		$tpl->forceCompilation();
//		$this->assertEquals('It works!', $dwoo->get($tpl, array(), $this->compiler));
//		unlink(TEST_DIRECTORY.'/resources/plugins/loaderTest2.php');
//	}
}
