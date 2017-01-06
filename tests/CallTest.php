<?php

namespace Dwoo\Tests
{

    use Dwoo\Core;
    use Dwoo\Template\Str as TemplateString;
    use plugin_full_custom;

    /**
     * Class CallTest
     *
     * @package Dwoo\Tests
     */
    class CallTest extends BaseTests
    {

        public function testClosureFunctionPlugin()
        {
            $this->dwoo->addPlugin('test', function (Core $dwoo, $foo, $bar = "bar") {
                return $foo . $bar;
            });

            $tpl = new TemplateString('{test "xxx"}');
            $tpl->forceCompilation();

            $this->assertEquals('xxxbar', $this->dwoo->get($tpl, array(), $this->compiler));
            $this->dwoo->removePlugin('test');
        }

        public function testCustomFunctionPlugin()
        {
            $this->dwoo->addPlugin('test', 'plugin_custom_name');
            $tpl = new TemplateString('{test "xxx"}');
            $tpl->forceCompilation();

            $this->assertEquals('xxxbar', $this->dwoo->get($tpl, array(), $this->compiler));
            $this->dwoo->removePlugin('test');
        }

        public function testHalfCustomClassPluginByClassMethodCallback()
        {
            $this->dwoo->addPlugin('test', array('plugin_half_custom', 'process'));
            $tpl = new TemplateString('{test "xxx"}');
            $tpl->forceCompilation();

            $this->assertEquals('xxxbar', $this->dwoo->get($tpl, array(), $this->compiler));
            $this->dwoo->removePlugin('test');
        }

        public function testFullCustomClassPluginByClassMethodCallback()
        {
            $this->dwoo->addPlugin('test', array('plugin_full_custom', 'process'));
            $tpl = new TemplateString('{test "xxx"}');
            $tpl->forceCompilation();

            $this->assertEquals('xxxbar', $this->dwoo->get($tpl, array(), $this->compiler));
            $this->dwoo->removePlugin('test');
        }

        public function testCustomClassPluginByClassname()
        {
            $this->dwoo->addPlugin('test', 'plugin_full_custom');
            $tpl = new TemplateString('{test "xxx"}');
            $tpl->forceCompilation();

            $this->assertEquals('xxxbar', $this->dwoo->get($tpl, array(), $this->compiler));
            $this->dwoo->removePlugin('test');
        }

        public function testCustomObjectPluginByObjectMethodCallback()
        {
            $this->dwoo->addPlugin('test', array(new plugin_full_custom(), 'process'));
            $tpl = new TemplateString('{test "xxx"}');
            $tpl->forceCompilation();

            $this->assertEquals('xxxbar', $this->dwoo->get($tpl, array(), $this->compiler));
            $this->dwoo->removePlugin('test');
        }

        public function testCustomBlockPluginByClassMethodCallback()
        {
            $this->dwoo->addPlugin('test', array('blockplugin_custom', 'process'));
            $tpl = new TemplateString('{test "xxx"}aaa{/test}');
            $tpl->forceCompilation();

            $this->assertEquals('xxxbaraaa', $this->dwoo->get($tpl, array(), $this->compiler));
            $this->dwoo->removePlugin('test');
        }

        public function testCustomBlockPluginByClassname()
        {
            $this->dwoo->addPlugin('test', 'blockplugin_custom');
            $tpl = new TemplateString('{test "xxx"}aaa{/test}');
            $tpl->forceCompilation();

            $this->assertEquals('xxxbaraaa', $this->dwoo->get($tpl, array(), $this->compiler));
            $this->dwoo->removePlugin('test');
        }

        /**
         * @expectedException \Dwoo\Exception
         */
        public function testCustomInvalidPlugin()
        {
            $this->dwoo->addPlugin('test', 'sdfmslkfmsle');
        }
    }
}
namespace
{

    use Dwoo\Core;
    use Dwoo\Block\Plugin as BlockPlugin;
    use Dwoo\Plugin;

    function plugin_custom_name(Core $dwoo, $foo, $bar = 'bar')
    {
        return $foo . $bar;
    }

    class plugin_half_custom extends Plugin
    {
        public function process($foo, $bar = 'bar')
        {
            return $foo . $bar;
        }
    }

    class plugin_full_custom
    {
        public function process($foo, $bar = 'bar')
        {
            return $foo . $bar;
        }
    }

    class blockplugin_custom extends BlockPlugin
    {
        public function init($foo, $bar = 'bar')
        {
            $this->foo = $foo;
            $this->bar = $bar;
        }

        public function process()
        {
            return $this->foo . $this->bar . $this->buffer;
        }
    }
}