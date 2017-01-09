<?php

namespace Dwoo\Tests
{

    use Dwoo\Loader;
    use Dwoo\Template\Str as TemplateString;

    /**
     * Class LoaderTest
     *
     * @package Dwoo\Tests
     */
    class LoaderTest extends BaseTests
    {

        public function testLoaderGetSet()
        {
            $loader = new Loader(__DIR__ . '/temp/cache');

            $this->dwoo->setLoader($loader);
            $this->assertEquals($loader, $this->dwoo->getLoader());
        }

        public function testPluginLoad()
        {
            $loader = new Loader(__DIR__ . '/temp/cache');

            $this->dwoo->setLoader($loader);
            $loader->addDirectory(__DIR__ . '/resources/plugins');

            $tpl = new TemplateString('{loaderTest}');
            $tpl->forceCompilation();
            $this->assertEquals('Moo', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testRebuildClassPath()
        {
            $loader = new Loader(__DIR__ . '/temp/cache');

            $this->dwoo->setLoader($loader);
            file_put_contents(__DIR__ . '/resources/plugins/PluginLoaderTest2.php', '<?php function PluginLoaderTest2(Dwoo\Core $dwoo) { return "It works!"; }');
            $loader->addDirectory(__DIR__ . '/resources/plugins');

            $tpl = new TemplateString('{loaderTest2}');
            $tpl->forceCompilation();
            $this->assertEquals('It works!', $this->dwoo->get($tpl, array(), $this->compiler));
            unlink(__DIR__ . '/resources/plugins/PluginLoaderTest2.php');
        }
    }
}