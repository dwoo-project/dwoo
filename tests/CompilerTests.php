<?php

require_once 'Dwoo/Compiler.php';

class CompilerTests extends PHPUnit_Framework_TestCase
{
	protected $compiler;
	protected $dwoo;

	public function __construct()
	{
		// extend this class and override this in your constructor to test a modded compiler
		$this->compiler = new Dwoo_Compiler();
		$this->dwoo = new Dwoo();
	}

	public function testVarReplacement()
	{
		$tpl = new Dwoo_Template_String('{$foo}');
		$tpl->forceCompilation();

		$this->assertEquals('bar', $this->dwoo->get($tpl, array('foo'=>'bar'), $this->compiler));
	}

	public function testModifier()
	{
		$tpl = new Dwoo_Template_String('{$foo|upper}');
		$tpl->forceCompilation();

		$this->assertEquals('BAR', $this->dwoo->get($tpl, array('foo'=>'bar'), $this->compiler));
	}

	public function testModifierArgs()
	{
		$tpl = new Dwoo_Template_String('{$foo|spacify:"-"|upper}');
		$tpl->forceCompilation();

		$this->assertEquals('B-A-R', $this->dwoo->get($tpl, array('foo'=>'bar'), $this->compiler));
	}

	public function testModifierArgsVars()
	{
		$tpl = new Dwoo_Template_String('{$foo|spacify:$bar|upper}');
		$tpl->forceCompilation();

		$this->assertEquals('B-A-R', $this->dwoo->get($tpl, array('foo'=>'bar', 'bar'=>'-'), $this->compiler));
	}

	public function testModifierOnString()
	{
		$tpl = new Dwoo_Template_String('{"bar"|spacify:"-"|upper}');
		$tpl->forceCompilation();

		$this->assertEquals('B-A-R', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testModifierOnStringWithVar()
	{
		$tpl = new Dwoo_Template_String('{"bar"|spacify:$bar|upper}');
		$tpl->forceCompilation();

		$this->assertEquals('B-A-R', $this->dwoo->get($tpl, array('bar'=>'-'), $this->compiler));
	}

	public function testModifierWithModifier()
	{
		$tpl = new Dwoo_Template_String('{$foo.0}{assign $foo|reverse foo}{$foo.0}{assign $foo|@reverse foo}{$foo.0}');
		$tpl->forceCompilation();

		$this->assertEquals('barbazzab', $this->dwoo->get($tpl, array('foo'=>array('bar','baz')), $this->compiler));
	}

	public function testDwooFunc()
	{
		$tpl = new Dwoo_Template_String('{upper($foo)}');
		$tpl->forceCompilation();

		$this->assertEquals('BAR', $this->dwoo->get($tpl, array('foo'=>'bar'), $this->compiler));
	}

	public function testDwooLoose()
	{
		$tpl = new Dwoo_Template_String('{upper $foo}');
		$tpl->forceCompilation();

		$this->assertEquals('BAR', $this->dwoo->get($tpl, array('foo'=>'bar'), $this->compiler));
	}

	public function testNamedParameter()
	{
		$tpl = new Dwoo_Template_String('{upper value=$foo}');
		$tpl->forceCompilation();

		$this->assertEquals('BAR', $this->dwoo->get($tpl, array('foo'=>'bar'), $this->compiler));
	}

	public function testNamedParameter2()
	{
		$tpl = new Dwoo_Template_String('{replace value=$foo search="BAR"|lower replace="BAR"}');
		$tpl->forceCompilation();

		$this->assertEquals('BAR', $this->dwoo->get($tpl, array('foo'=>'bar'), $this->compiler));
	}

	public function testNamedParameter3()
	{
		$tpl = new Dwoo_Template_String('{assign value=reverse(array(foo=3,boo=5, 3=4), true) var=arr}{foreach $arr k v}{$k}{$v}{/foreach}');
		$tpl->forceCompilation();

		$this->assertEquals('34boo5foo3', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testRecursiveCall()
	{
		$tpl = new Dwoo_Template_String('{lower(reverse(upper($foo)))}');
		$tpl->forceCompilation();

		$this->assertEquals('rab', $this->dwoo->get($tpl, array('foo'=>'bar'), $this->compiler));
	}

	public function testComplexRecursiveCall()
	{
		$tpl = new Dwoo_Template_String('{lower reverse($foo|reverse|upper)}');
		$tpl->forceCompilation();

		$this->assertEquals('bar', $this->dwoo->get($tpl, array('foo'=>'BaR'), $this->compiler));
	}

	public function testComplexRecursiveCall2()
	{
		$tpl = new Dwoo_Template_String('{str_repeat "AB`$foo|reverse|spacify:o`CD" 3}');
		$tpl->forceCompilation();
		$this->assertEquals('AB3o2o1CDAB3o2o1CDAB3o2o1CD', $this->dwoo->get($tpl, array('foo'=>'123'), $this->compiler));
	}

	public function testStrip()
	{
		$tpl = new Dwoo_Template_String("{strip}a\nb\nc{/strip}a\nb\nc");
		$tpl->forceCompilation();
		$this->assertEquals("abca\nb\nc", $this->dwoo->get($tpl, array(), $this->compiler));
	}

	/**
	 * @expectedException Dwoo_Compilation_Exception
	 */
	public function testUnclosedStrip()
	{
		$tpl = new Dwoo_Template_String("{strip}a\nb\nca\nb\nc");
		$tpl->forceCompilation();
		$this->dwoo->get($tpl, array(), $this->compiler);
	}

	public function testWhitespace()
	{
		$tpl = new Dwoo_Template_String("{\$foo}{\$foo}\n{\$foo}\n\n{\$foo}\n\n\n{\$foo}");
		$tpl->forceCompilation();
		$this->assertEquals("aa\na\n\na\n\n\na", $this->dwoo->get($tpl, array('foo'=>'a'), $this->compiler));
	}

	public function testLiteral()
	{
		$tpl = new Dwoo_Template_String('{literal}{$foo}{hurray}{/literal}');
		$tpl->forceCompilation();
		$this->assertEquals('{$foo}{hurray}', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	/**
	 * @expectedException Dwoo_Compilation_Exception
	 */
	public function testUnclosedLiteral()
	{
		$tpl = new Dwoo_Template_String('{literal}{$foo}{hurray}');
		$tpl->forceCompilation();
		$this->dwoo->get($tpl, array(), $this->compiler);
	}

	public function testEscaping()
	{
		$tpl = new Dwoo_Template_String('\{foo\{bar\\\\{$var}}{"tes\}t"}{"foo\"lol\"bar"}');
		$tpl->forceCompilation();
		$this->assertEquals('{foo{bar\\1}tes}tfoo"lol"bar', $this->dwoo->get($tpl, array('var'=>1), $this->compiler));
	}

	public function testFunctions()
	{
		$tpl = new Dwoo_Template_String('{dump()}{dump( )}{dump}');
		$tpl->forceCompilation();
		$this->assertEquals('<div style="background:#aaa; padding:5px; margin:5px; color:#000;">data (current scope): <div style="background:#ccc;"></div></div><div style="background:#aaa; padding:5px; margin:5px; color:#000;">data (current scope): <div style="background:#ccc;"></div></div><div style="background:#aaa; padding:5px; margin:5px; color:#000;">data (current scope): <div style="background:#ccc;"></div></div>', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testExpressions()
	{
		$tpl = new Dwoo_Template_String('{$foo+5} {$foo+$foo} {$foo+3*$foo} {$foo*$foo+4*$foo} {$foo*2/2|number_format} {$foo*2/3|number_format:1} {number_format $foo*2/3 1} {if $foo+5>9 && $foo < 7 && $foo+$foo==$foo*2}win{/if} {$arr[$foo+3]}');
		$tpl->forceCompilation();

		$this->assertEquals("10 10 40 145 5 3.3 3.3 win win", $this->dwoo->get($tpl, array('foo'=>5, 'arr'=>array(8=>'win')), $this->compiler));

		$tpl = new Dwoo_Template_String('{"$foo/$foo"}');
		$tpl->forceCompilation();

		$this->assertEquals("1", $this->dwoo->get($tpl, array('foo'=>5), $this->compiler));
	}

	public function testNonExpressions()
	{
		$tpl = new Dwoo_Template_String('{"`$foo`/`$foo`"}');
		$tpl->forceCompilation();

		$this->assertEquals("5/5", $this->dwoo->get($tpl, array('foo'=>5), $this->compiler));
	}

	public function testConstants()
	{
		if(!defined('TEST'))
			define('TEST', 'Test');
		$tpl = new Dwoo_Template_String('{$dwoo.const.TEST} {$dwoo.const.Dwoo::FUNC_PLUGIN*$dwoo.const.Dwoo::BLOCK_PLUGIN}');
		$tpl->forceCompilation();

		$this->assertEquals(TEST.' '.(Dwoo::FUNC_PLUGIN*Dwoo::BLOCK_PLUGIN), $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testShortConstants()
	{
		if(!defined('TEST'))
			define('TEST', 'Test');
		$tpl = new Dwoo_Template_String('{%TEST} {$dwoo.const.Dwoo::FUNC_PLUGIN*%Dwoo::BLOCK_PLUGIN}');
		$tpl->forceCompilation();

		$this->assertEquals(TEST.' '.(Dwoo::FUNC_PLUGIN*Dwoo::BLOCK_PLUGIN), $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testAltDelimiters()
	{
		$tpl = new Dwoo_Template_String('{"test"} <%"test"%> <%"foo{lol}\%>"%>');
		$tpl->forceCompilation();
		$this->compiler->setDelimiters('<%', '%>');
		$this->assertEquals('{"test"} test foo{lol}%>', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('d"O"b');
		$tpl->forceCompilation();
		$this->compiler->setDelimiters('d', 'b');
		$this->assertEquals('O', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('<!-- "O" --> \<!-- ');
		$tpl->forceCompilation();
		$this->compiler->setDelimiters('<!-- ', ' -->');
		$this->assertEquals('O <!-- ', $this->dwoo->get($tpl, array(), $this->compiler));

		$this->compiler->setDelimiters('{', '}');
	}

	public function testNumberedIndexes()
	{
		$tpl = new Dwoo_Template_String('{$100}-{$150}-{if $0}FAIL{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('bar-foo-', $this->dwoo->get($tpl, array('100'=>'bar', 150=>'foo'), $this->compiler));
	}

	public function testParseBool()
	{
		$tpl = new Dwoo_Template_String('{if (true === yes && true === on) && (false===off && false===no)}okay{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('okay', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testMethodCalls()
	{
		$tpl = new Dwoo_Template_String('{$a} {$a->foo()} {$b[$c]->foo()} {$a->bar()+$a->bar()} {$a->baz(5, $foo)} {$a->make(5)->getInt()} {$a->make(5)->getInt()/2}');
		$tpl->forceCompilation();

		$a = new MethodCallsHelper();
		$this->assertEquals('obj 0 1 7 10bar 5 2.5', $this->dwoo->get($tpl, array('a'=>$a, 'b'=>array('test'=>$a), 'c'=>'test', 'foo'=>'bar'), $this->compiler));
	}

	public function testLooseTagHandling()
	{
		$this->compiler->setLooseOpeningHandling(true);
		$this->assertEquals($this->compiler->getLooseOpeningHandling(), true);

		$tpl = new Dwoo_Template_String('{     $a      }{$a     }{     $a}{$a}');
		$tpl->forceCompilation();

		$this->assertEquals('moomoomoomoo', $this->dwoo->get($tpl, array('a'=>'moo'), $this->compiler));

	   	$this->compiler->setLooseOpeningHandling(false);
		$tpl = new Dwoo_Template_String('{     $a      }{$a     }{     $a}{$a}');
		$tpl->forceCompilation();

		$this->assertEquals('{     $a      }moo{     $a}moo', $this->dwoo->get($tpl, array('a'=>'moo'), $this->compiler));
	}

	public function testDwooDotShortcut()
	{
		$tpl = new Dwoo_Template_String('{$.server.SCRIPT_NAME}{foreach $a item}{$.foreach.default.iteration}{$item}{$.foreach.$b.$c}{/foreach}');
		$tpl->forceCompilation();

		$this->assertEquals($_SERVER['SCRIPT_NAME'].'1a12b2', $this->dwoo->get($tpl, array('a'=>array('a','b'), 'b'=>'default', 'c'=>'iteration'), $this->compiler));
	}

	public function testRootAndParentShortcut()
	{
		$tpl = new Dwoo_Template_String('{with $a}{$__.b}{$_.b}{$0}{/with}{$__.b}');
		$tpl->forceCompilation();

		$this->assertEquals('defaultdefaultadefault', $this->dwoo->get($tpl, array('a'=>array('a','b'), 'b'=>'default'), $this->compiler));
	}

	public function testCurrentScopeShortcut()
	{
		$tpl = new Dwoo_Template_String('{loop $}{$_key} > {loop $}{$}{/loop}{/loop}');
		$tpl->forceCompilation();

		$this->assertEquals('1 > ab2 > cd', $this->dwoo->get($tpl, array('1'=>array('a','b'), '2'=>array('c','d')), $this->compiler));

		$tpl = new Dwoo_Template_String('{with $a}{$1}{/with}{loop $a}{$}{/loop}');
		$tpl->forceCompilation();

		$this->assertEquals('bab', $this->dwoo->get($tpl, array('a'=>array('a','b'), 'b'=>'default', 'c'=>'iteration'), $this->compiler));
	}

	public function testCloseBlockShortcut()
	{
		$tpl = new Dwoo_Template_String('{loop $}{$_key} > {loop $}{$}{/}{/}');
		$tpl->forceCompilation();

		$this->assertEquals('1 > ab2 > cd', $this->dwoo->get($tpl, array('1'=>array('a','b'), '2'=>array('c','d')), $this->compiler));
	}

	public function testImplicitCloseBlock()
	{
		$tpl = new Dwoo_Template_String('{loop $}{$_key} > {loop $}{$}');
		$tpl->forceCompilation();

		$this->assertEquals('1 > ab2 > cd', $this->dwoo->get($tpl, array('1'=>array('a','b'), '2'=>array('c','d')), $this->compiler));
	}

	public function testAssignAndIncrement()
	{
		$tpl = new Dwoo_Template_String('{$foo}{$foo+=3}{$foo}
{$foo}{$foo-=3}{$foo}
{$foo++}{$foo++}{$foo}{$foo*=$foo}{$foo}
{$foo--}{$foo=5}{$foo}');
		$tpl->forceCompilation();

		$this->assertEquals("03\n30\n0124\n45", $this->dwoo->get($tpl, array('foo'=>0), $this->compiler));

		$tpl = new Dwoo_Template_String('{$foo="moo"}{$foo}{$foo=math("3+5")}{$foo}');
		$tpl->forceCompilation();

		$this->assertEquals("moo8", $this->dwoo->get($tpl, array('foo'=>0), $this->compiler));
	}

	public function testSetStringValToTrueWhenUsingNamedParams()
	{
		$this->dwoo->addPlugin('test', create_function('Dwoo $dwoo, $name, $bool=false', 'return $bool ? $name."!" : $name."?";'));
		$tpl = new Dwoo_Template_String('{test name="Name"}{test name="Name" bool}');
		$tpl->forceCompilation();

		$this->assertEquals("Name?Name!", $this->dwoo->get($tpl, array(), $this->compiler));
		$this->dwoo->removePlugin('test');
	}
}

class MethodCallsHelper {
 	public function __construct($int=0) {
		$this->int = $int;
	}
	public function getInt() {
		return $this->int;
	}
	public function make($a=0) {
		return new self($a);
	}
	public function foo() {
		static $a=0;
		return $a++;
	}
	public function bar() {
		static $a=3;
		return $a++;
	}
	public function baz($int, $str) {
		return ($int+5).$str;
	}
	public function __toString() { return 'obj'; }
}
