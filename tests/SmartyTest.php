<?php

namespace Dwoo\Tests
{

    use Dwoo\Compiler;
    use Dwoo\Core;
    use Dwoo\Smarty\Adapter as SmartyAdapter;

    /**
     * Class SmartyTest
     *
     * @package Dwoo\Tests
     */
    class SmartyTest extends BaseTests
    {

        /**
         * SmartyTests constructor.
         *
         * @param null   $name
         * @param array  $data
         * @param string $dataName
         */
        public function __construct($name = null, array $data = array(), $dataName = '')
        {
            // extend this class and override this in your constructor to test a modded compiler
            $this->dwoo               = new SmartyAdapter();
            $this->dwoo->template_dir = __DIR__ . DIRECTORY_SEPARATOR . 'resources/';
            $this->dwoo->compile_dir  = __DIR__ . DIRECTORY_SEPARATOR . '/temp/compiled/';
            $this->dwoo->cache_dir    = __DIR__ . DIRECTORY_SEPARATOR . '/temp/cache/';
            $this->dwoo->config_dir   = __DIR__ . DIRECTORY_SEPARATOR . '/resources/configs/';
            $this->compiler           = new Compiler();
            $this->compiler->addPreProcessor('PluginSmartyCompatible', true);
        }

        public function testSmartyCompatible()
        {
            $this->dwoo->assign('arr', array('ab', 'cd', 'ef'));
            $this->assertEquals('{' . Core::VERSION . '} ab cd ef', $this->dwoo->fetch('smartytest.html'));
        }

        public function tearDown()
        {
        }
    }
}