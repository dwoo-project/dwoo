<?php

namespace Dwoo\Tests
{

    use custom_class_plugin_obj;
    use custom_compilable_class_plugin_obj;
    use Dwoo\Template\Str as TemplateString;

    /**
     * Class PluginTypesTest
     *
     * @package Dwoo\Tests
     */
    class PluginTypesTest extends BaseTests
    {

        // Functions - Dwoo style
        public function testCompilableFunctionPlugin()
        {
            $tpl = new TemplateString('{CompilableFunctionPlugin 4 5}');
            $tpl->forceCompilation();

            $this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testCompilableFunctionPluginAsModifier()
        {
            $tpl = new TemplateString('{$foo=4}{$foo|CompilableFunctionPlugin:5}');
            $tpl->forceCompilation();

            $this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testFunctionPlugin()
        {
            $tpl = new TemplateString('{FunctionPlugin 4 5}');
            $tpl->forceCompilation();

            $this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testFunctionPluginAsModifier()
        {
            $tpl = new TemplateString('{$foo=4}{$foo|FunctionPlugin:5}');
            $tpl->forceCompilation();

            $this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        // Classes - Dwoo style
        public function testCompilableClassPlugin()
        {
            $tpl = new TemplateString('{CompilableClassPlugin 4 5}');
            $tpl->forceCompilation();

            $this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testCompilableClassPluginAsModifier()
        {
            $tpl = new TemplateString('{$foo=4}{$foo|CompilableClassPlugin:5}');
            $tpl->forceCompilation();

            $this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testClassPlugin()
        {
            $tpl = new TemplateString('{ClassPlugin 4 5}');
            $tpl->forceCompilation();

            $this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testClassPluginAsModifier()
        {
            $tpl = new TemplateString('{$foo=4}{$foo|ClassPlugin:5}');
            $tpl->forceCompilation();

            $this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        // Functions - Custom style
        public function testCustomCompilableFunctionPlugin()
        {
            $this->dwoo->addPlugin('CustomCompilableFunctionPlugin', 'custom_compilable_plugin', true);
            $tpl = new TemplateString('{CustomCompilableFunctionPlugin 4 5}');
            $tpl->forceCompilation();

            $this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testCustomCompilableFunctionPluginAsModifier()
        {
            $this->dwoo->addPlugin('CustomCompilableFunctionPlugin', 'custom_compilable_plugin', true);
            $tpl = new TemplateString('{$foo=4}{$foo|CustomCompilableFunctionPlugin:5}');
            $tpl->forceCompilation();

            $this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testCustomFunctionPlugin()
        {
            $this->dwoo->addPlugin('CustomFunctionPlugin', 'custom_plugin');
            $tpl = new TemplateString('{CustomFunctionPlugin 4 5}');
            $tpl->forceCompilation();

            $this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testCustomFunctionPluginAsModifier()
        {
            $this->dwoo->addPlugin('CustomFunctionPlugin', 'custom_plugin');
            $tpl = new TemplateString('{$foo=4}{$foo|CustomFunctionPlugin:5}');
            $tpl->forceCompilation();

            $this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        // Classes - Custom style - Static
        public function testCustomCompilableClassPlugin()
        {
            $this->dwoo->addPlugin('CustomCompilableClassPlugin', array('custom_compilable_class_plugin', 'call'), true);
            $tpl = new TemplateString('{CustomCompilableClassPlugin 4 5}');
            $tpl->forceCompilation();

            $this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testCustomCompilableClassPluginAsModifier()
        {
            $this->dwoo->addPlugin('CustomCompilableClassPlugin', array('custom_compilable_class_plugin', 'call'), true);
            $tpl = new TemplateString('{$foo=4}{$foo|CustomCompilableClassPlugin:5}');
            $tpl->forceCompilation();

            $this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testCustomClassPlugin()
        {
            $this->dwoo->addPlugin('CustomClassPlugin', array('custom_class_plugin', 'call'));
            $tpl = new TemplateString('{CustomClassPlugin 4 5}');
            $tpl->forceCompilation();

            $this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testCustomClassPluginAsModifier()
        {
            $this->dwoo->addPlugin('CustomClassPlugin', array('custom_class_plugin', 'call'));
            $tpl = new TemplateString('{$foo=4}{$foo|CustomClassPlugin:5}');
            $tpl->forceCompilation();

            $this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        // Classes - Custom style - Instance
        public function testCustomCompilableClassPluginInstance()
        {
            $this->dwoo->addPlugin('CustomCompilableClassPlugin', array(
                new custom_compilable_class_plugin_obj(),
                'call'
            ), true);
            $tpl = new TemplateString('{CustomCompilableClassPlugin 4 5}');
            $tpl->forceCompilation();

            $this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testCustomCompilableClassPluginInstanceAsModifier()
        {
            $this->dwoo->addPlugin('CustomCompilableClassPlugin', array(
                new custom_compilable_class_plugin_obj(),
                'call'
            ), true);
            $tpl = new TemplateString('{$foo=4}{$foo|CustomCompilableClassPlugin:5}');
            $tpl->forceCompilation();

            $this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testCustomClassPluginInstance()
        {
            $this->dwoo->addPlugin('CustomClassPlugin', array(new custom_class_plugin_obj(), 'call'));
            $tpl = new TemplateString('{CustomClassPlugin 4 5}');
            $tpl->forceCompilation();

            $this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testCustomClassPluginInstanceAsModifier()
        {
            $this->dwoo->addPlugin('CustomClassPlugin', array(new custom_class_plugin_obj(), 'call'));
            $tpl = new TemplateString('{$foo=4}{$foo|CustomClassPlugin:5}');
            $tpl->forceCompilation();

            $this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
        }
    }
}

namespace
{

    function PluginCompilableFunctionPluginCompile(Dwoo\Compiler $compiler, $number, $number2)
    {
        return "$number * $number2";
    }

    function PluginFunctionPlugin(Dwoo\Core $dwoo, $number, $number2)
    {
        return $number * $number2;
    }

    class PluginCompilableClassPlugin extends Dwoo\Plugin implements Dwoo\ICompilable
    {
        public static function compile(Dwoo\Compiler $compiler, $number, $number2)
        {
            return "$number * $number2";
        }

        public function process()
        {
        }
    }

    class PluginClassPlugin extends Dwoo\Plugin
    {
        public function process($number, $number2)
        {
            return $number * $number2;
        }
    }

    function custom_compilable_plugin(Dwoo\Compiler $cmp, $number, $number2)
    {
        return "$number * $number2";
    }

    function custom_plugin(Dwoo\Core $dwoo, $number, $number2)
    {
        return $number * $number2;
    }

    class custom_compilable_class_plugin
    {
        public static function call($number, $number2)
        {
            return "$number * $number2";
        }
    }

    class custom_class_plugin
    {
        public static function call($number, $number2)
        {
            return $number * $number2;
        }
    }

    class custom_compilable_class_plugin_obj
    {
        public function call($number, $number2)
        {
            return "$number * $number2";
        }
    }

    class custom_class_plugin_obj
    {
        public function call($number, $number2)
        {
            return $number * $number2;
        }
    }
}