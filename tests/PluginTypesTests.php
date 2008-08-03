<?php

require_once 'Dwoo/Compiler.php';

class PluginTypesTests extends PHPUnit_Framework_TestCase
{
	protected $compiler;
	protected $dwoo;

	public function __construct()
	{
		// extend this class and override this in your constructor to test a modded compiler
		$this->compiler = new Dwoo_Compiler();
		$this->dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
	}

	// Functions - Dwoo style
	public function testCompilableFunctionPlugin()
	{
		$tpl = new Dwoo_Template_String('{CompilableFunctionPlugin 4 5}');
		$tpl->forceCompilation();

		$this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testCompilableFunctionPluginAsModifier()
	{
		$tpl = new Dwoo_Template_String('{$foo=4}{$foo|CompilableFunctionPlugin:5}');
		$tpl->forceCompilation();

		$this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testFunctionPlugin()
	{
		$tpl = new Dwoo_Template_String('{FunctionPlugin 4 5}');
		$tpl->forceCompilation();

		$this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testFunctionPluginAsModifier()
	{
		$tpl = new Dwoo_Template_String('{$foo=4}{$foo|FunctionPlugin:5}');
		$tpl->forceCompilation();

		$this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	// Classes - Dwoo style
	public function testCompilableClassPlugin()
	{
		$tpl = new Dwoo_Template_String('{CompilableClassPlugin 4 5}');
		$tpl->forceCompilation();

		$this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testCompilableClassPluginAsModifier()
	{
		$tpl = new Dwoo_Template_String('{$foo=4}{$foo|CompilableClassPlugin:5}');
		$tpl->forceCompilation();

		$this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testClassPlugin()
	{
		$tpl = new Dwoo_Template_String('{ClassPlugin 4 5}');
		$tpl->forceCompilation();

		$this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testClassPluginAsModifier()
	{
		$tpl = new Dwoo_Template_String('{$foo=4}{$foo|ClassPlugin:5}');
		$tpl->forceCompilation();

		$this->assertEquals('20', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	// Functions - Custom style
	public function testCustomCompilableFunctionPlugin()
	{
		$dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
		$dwoo->addPlugin('CustomCompilableFunctionPlugin', 'custom_compilable_plugin', true);
		$tpl = new Dwoo_Template_String('{CustomCompilableFunctionPlugin 4 5}');
		$tpl->forceCompilation();

		$this->assertEquals('20', $dwoo->get($tpl, array(), $this->compiler));
	}

	public function testCustomCompilableFunctionPluginAsModifier()
	{
		$dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
		$dwoo->addPlugin('CustomCompilableFunctionPlugin', 'custom_compilable_plugin', true);
		$tpl = new Dwoo_Template_String('{$foo=4}{$foo|CustomCompilableFunctionPlugin:5}');
		$tpl->forceCompilation();

		$this->assertEquals('20', $dwoo->get($tpl, array(), $this->compiler));
	}

	public function testCustomFunctionPlugin()
	{
		$dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
		$dwoo->addPlugin('CustomFunctionPlugin', 'custom_plugin');
		$tpl = new Dwoo_Template_String('{CustomFunctionPlugin 4 5}');
		$tpl->forceCompilation();

		$this->assertEquals('20', $dwoo->get($tpl, array(), $this->compiler));
	}

	public function testCustomFunctionPluginAsModifier()
	{
		$dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
		$dwoo->addPlugin('CustomFunctionPlugin', 'custom_plugin');
		$tpl = new Dwoo_Template_String('{$foo=4}{$foo|CustomFunctionPlugin:5}');
		$tpl->forceCompilation();

		$this->assertEquals('20', $dwoo->get($tpl, array(), $this->compiler));
	}

	// Classes - Custom style - Static
	public function testCustomCompilableClassPlugin()
	{
		$dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
		$dwoo->addPlugin('CustomCompilableClassPlugin', array('custom_compilable_class_plugin', 'call'), true);
		$tpl = new Dwoo_Template_String('{CustomCompilableClassPlugin 4 5}');
		$tpl->forceCompilation();

		$this->assertEquals('20', $dwoo->get($tpl, array(), $this->compiler));
	}

	public function testCustomCompilableClassPluginAsModifier()
	{
		$dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
		$dwoo->addPlugin('CustomCompilableClassPlugin', array('custom_compilable_class_plugin', 'call'), true);
		$tpl = new Dwoo_Template_String('{$foo=4}{$foo|CustomCompilableClassPlugin:5}');
		$tpl->forceCompilation();

		$this->assertEquals('20', $dwoo->get($tpl, array(), $this->compiler));
	}

	public function testCustomClassPlugin()
	{
		$dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
		$dwoo->addPlugin('CustomClassPlugin', array('custom_class_plugin', 'call'));
		$tpl = new Dwoo_Template_String('{CustomClassPlugin 4 5}');
		$tpl->forceCompilation();

		$this->assertEquals('20', $dwoo->get($tpl, array(), $this->compiler));
	}

	public function testCustomClassPluginAsModifier()
	{
		$dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
		$dwoo->addPlugin('CustomClassPlugin', array('custom_class_plugin', 'call'));
		$tpl = new Dwoo_Template_String('{$foo=4}{$foo|CustomClassPlugin:5}');
		$tpl->forceCompilation();

		$this->assertEquals('20', $dwoo->get($tpl, array(), $this->compiler));
	}

	// Classes - Custom style - Instance
	public function testCustomCompilableClassPluginInstance()
	{
		$dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
		$dwoo->addPlugin('CustomCompilableClassPlugin', array(new custom_compilable_class_plugin_obj(), 'call'), true);
		$tpl = new Dwoo_Template_String('{CustomCompilableClassPlugin 4 5}');
		$tpl->forceCompilation();

		$this->assertEquals('20', $dwoo->get($tpl, array(), $this->compiler));
	}

	public function testCustomCompilableClassPluginInstanceAsModifier()
	{
		$dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
		$dwoo->addPlugin('CustomCompilableClassPlugin', array(new custom_compilable_class_plugin_obj(), 'call'), true);
		$tpl = new Dwoo_Template_String('{$foo=4}{$foo|CustomCompilableClassPlugin:5}');
		$tpl->forceCompilation();

		$this->assertEquals('20', $dwoo->get($tpl, array(), $this->compiler));
	}

	public function testCustomClassPluginInstance()
	{
		$dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
		$dwoo->addPlugin('CustomClassPlugin', array(new custom_class_plugin_obj(), 'call'));
		$tpl = new Dwoo_Template_String('{CustomClassPlugin 4 5}');
		$tpl->forceCompilation();

		$this->assertEquals('20', $dwoo->get($tpl, array(), $this->compiler));
	}

	public function testCustomClassPluginInstanceAsModifier()
	{
		$dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
		$dwoo->addPlugin('CustomClassPlugin', array(new custom_class_plugin_obj(), 'call'));
		$tpl = new Dwoo_Template_String('{$foo=4}{$foo|CustomClassPlugin:5}');
		$tpl->forceCompilation();

		$this->assertEquals('20', $dwoo->get($tpl, array(), $this->compiler));
	}
}

function Dwoo_Plugin_CompilableFunctionPlugin_compile(Dwoo_Compiler $compiler, $number, $number2)
{
	return "$number * $number2";
}

function Dwoo_Plugin_FunctionPlugin(Dwoo $dwoo, $number, $number2)
{
	return $number * $number2;
}

class Dwoo_Plugin_CompilableClassPlugin extends Dwoo_Plugin implements Dwoo_ICompilable
{
	public static function compile(Dwoo_Compiler $compiler, $number, $number2)
	{
		return "$number * $number2";
	}
}

class Dwoo_Plugin_ClassPlugin extends Dwoo_Plugin
{
	public function process($number, $number2)
	{
		return $number * $number2;
	}
}

function custom_compilable_plugin(Dwoo_Compiler $cmp, $number, $number2)
{
	return "$number * $number2";
}

function custom_plugin(Dwoo $dwoo, $number, $number2)
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
