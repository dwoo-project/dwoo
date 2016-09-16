<?php

class CallTests extends PHPUnit_Framework_TestCase
{
    protected $compiler;
    protected $dwoo;

    public function __construct()
    {
        // extend this class and override this in your constructor to test a modded compiler
        $this->compiler = new Dwoo\Compiler();
        $this->dwoo = new Dwoo\Core(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
    }

//	public function testClosureFunctionPlugin()
//	{
//		$this->dwoo->addPlugin('test', function (Dwoo\Core $dwoo, $foo, $bar="bar")
//		{
//			return $foo.$bar;
//		});
//		$tpl = new Dwoo\Template\String('{test "xxx"}');
//		$tpl->forceCompilation();

//		$this->assertEquals('xxxbar', $this->dwoo->get($tpl, array(), $this->compiler));
//		$this->dwoo->removePlugin('test');
//	}

    public function testCustomFunctionPlugin()
    {
        $this->dwoo->addPlugin('test', 'plugin_custom_name');
        $tpl = new Dwoo\Template\String('{test "xxx"}');
        $tpl->forceCompilation();

        $this->assertEquals('xxxbar', $this->dwoo->get($tpl, array(), $this->compiler));
        $this->dwoo->removePlugin('test');
    }

    public function testHalfCustomClassPluginByClassMethodCallback()
    {
        $this->dwoo->addPlugin('test', array('plugin_half_custom', 'process'));
        $tpl = new Dwoo\Template\String('{test "xxx"}');
        $tpl->forceCompilation();

        $this->assertEquals('xxxbar', $this->dwoo->get($tpl, array(), $this->compiler));
        $this->dwoo->removePlugin('test');
    }

    public function testFullCustomClassPluginByClassMethodCallback()
    {
        $this->dwoo->addPlugin('test', array('plugin_full_custom', 'process'));
        $tpl = new Dwoo\Template\String('{test "xxx"}');
        $tpl->forceCompilation();

        $this->assertEquals('xxxbar', $this->dwoo->get($tpl, array(), $this->compiler));
        $this->dwoo->removePlugin('test');
    }

    public function testCustomClassPluginByClassname()
    {
        $this->dwoo->addPlugin('test', 'plugin_full_custom');
        $tpl = new Dwoo\Template\String('{test "xxx"}');
        $tpl->forceCompilation();

        $this->assertEquals('xxxbar', $this->dwoo->get($tpl, array(), $this->compiler));
        $this->dwoo->removePlugin('test');
    }

    public function testCustomObjectPluginByObjectMethodCallback()
    {
        $this->dwoo->addPlugin('test', array(new plugin_full_custom(), 'process'));
        $tpl = new Dwoo\Template\String('{test "xxx"}');
        $tpl->forceCompilation();

        $this->assertEquals('xxxbar', $this->dwoo->get($tpl, array(), $this->compiler));
        $this->dwoo->removePlugin('test');
    }

    public function testCustomBlockPluginByClassMethodCallback()
    {
        $this->dwoo->addPlugin('test', array('blockplugin_custom', 'process'));
        $tpl = new Dwoo\Template\String('{test "xxx"}aaa{/test}');
        $tpl->forceCompilation();

        $this->assertEquals('xxxbaraaa', $this->dwoo->get($tpl, array(), $this->compiler));
        $this->dwoo->removePlugin('test');
    }

    public function testCustomBlockPluginByClassname()
    {
        $this->dwoo->addPlugin('test', 'blockplugin_custom');
        $tpl = new Dwoo\Template\String('{test "xxx"}aaa{/test}');
        $tpl->forceCompilation();

        $this->assertEquals('xxxbaraaa', $this->dwoo->get($tpl, array(), $this->compiler));
        $this->dwoo->removePlugin('test');
    }

    /**
     * @expectedException Dwoo\Exception
     */
    public function testCustomInvalidPlugin()
    {
        $this->dwoo->addPlugin('test', 'sdfmslkfmsle');
    }
}

function plugin_custom_name(Dwoo\Core $dwoo, $foo, $bar = 'bar')
{
    return $foo.$bar;
}

class plugin_half_custom extends Dwoo\Plugin
{
    public function process($foo, $bar = 'bar')
    {
        return $foo.$bar;
    }
}

class plugin_full_custom
{
    public function process($foo, $bar = 'bar')
    {
        return $foo.$bar;
    }
}

class blockplugin_custom extends Dwoo\Block\Plugin
{
    public function init($foo, $bar = 'bar')
    {
        $this->foo = $foo;
        $this->bar = $bar;
    }

    public function process()
    {
        return $this->foo.$this->bar.$this->buffer;
    }
}
