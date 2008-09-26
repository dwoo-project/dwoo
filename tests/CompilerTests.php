<?php

require_once 'Dwoo/Compiler.php';

class CompilerTests extends PHPUnit_Framework_TestCase
{
	const FOO = 3;
	protected $compiler;
	protected $dwoo;

	public function __construct()
	{
		// extend this class and override this in your constructor to test a modded compiler
		$this->compiler = new Dwoo_Compiler();
		$this->dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
	}

	public function testVarReplacement()
	{
		$tpl = new Dwoo_Template_String('{$foo}');
		$tpl->forceCompilation();

		$this->assertEquals('bar', $this->dwoo->get($tpl, array('foo'=>'bar'), $this->compiler));
	}

	public function testComplexVarReplacement()
	{
		$tpl = new Dwoo_Template_String('{$_root[$a].0}{$_[$a][0]}{$_[$c.d].0}{$_.$a.0}{$_[$c[$x.0]].0}{$_[$c.$y.0].0}');
		$tpl->forceCompilation();

		$this->assertEquals('cccccc', $this->dwoo->get($tpl, array('a'=>'b', 'x'=>array('d'), 'y'=>'e', 'b'=>array('c','d'),'c'=>array('d'=>'b','e'=>array('b'))), $this->compiler));
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

	public function testMixedParameters()
	{
		$tpl = new Dwoo_Template_String('{assign value=array(3, boo=5, 3=4) var=arr}{foreach $arr k v}{$k}{$v}{/foreach}');
		$tpl->forceCompilation();

		$this->assertEquals('03boo534', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	/**
	 * @expectedException Dwoo_Compilation_Exception
	 */
	public function testMixedParametersWrongOrder()
	{
		$tpl = new Dwoo_Template_String("{array(boo=5, 3)}");
		$tpl->forceCompilation();
		$this->dwoo->get($tpl, array(), $this->compiler);
	}

	public function testMixedParamsMultiline()
	{
		$tpl = new Dwoo_Template_String('{replace(
												$foo	search="BAR"|lower
replace="BAR"
)}');
		$tpl->forceCompilation();

		$this->assertEquals('BAR', $this->dwoo->get($tpl, array('foo'=>'bar'), $this->compiler));

		$tpl = new Dwoo_Template_String('{replace(
												$foo	search=$bar|lower
replace="BAR"
)}');
		$tpl->forceCompilation();

		$this->assertEquals('BAR', $this->dwoo->get($tpl, array('foo'=>'bar','bar'=>'BAR'), $this->compiler));
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
		$tpl = new Dwoo_Template_String('\{foo\{bar\\\\{$var}}{"tes}t"}{"foo\"lol\"bar"}');
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
	}

	public function testDelimitedExpressionsInString()
	{
		$tpl = new Dwoo_Template_String('{"`$foo/$foo`"}');
		$tpl->forceCompilation();

		$this->assertEquals("1", $this->dwoo->get($tpl, array('foo'=>5), $this->compiler));
	}

	public function testNonDelimitedExpressionsInString()
	{
		$tpl = new Dwoo_Template_String('{"$foo/$foo"}');
		$tpl->forceCompilation();

		$this->assertEquals("5/5", $this->dwoo->get($tpl, array('foo'=>5), $this->compiler));
	}

	public function testConstants()
	{
		if (!defined('TEST')) {
			define('TEST', 'Test');
		}
		$tpl = new Dwoo_Template_String('{$dwoo.const.TEST} {$dwoo.const.Dwoo::FUNC_PLUGIN*$dwoo.const.Dwoo::BLOCK_PLUGIN}');
		$tpl->forceCompilation();

		$this->assertEquals(TEST.' '.(Dwoo::FUNC_PLUGIN*Dwoo::BLOCK_PLUGIN), $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testShortConstants()
	{
		if (!defined('TEST')) {
			define('TEST', 'Test');
		}
		$tpl = new Dwoo_Template_String('{%TEST} {$dwoo.const.Dwoo::FUNC_PLUGIN*%Dwoo::BLOCK_PLUGIN}');
		$tpl->forceCompilation();

		$this->assertEquals(TEST.' '.(Dwoo::FUNC_PLUGIN*Dwoo::BLOCK_PLUGIN), $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testShortClassConstants()
	{
		$tpl = new Dwoo_Template_String('{if %CompilerTests::FOO == 3}{%CompilerTests::FOO}{/}');
		$tpl->forceCompilation();

		$this->assertEquals(self::FOO, $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testAltDelimiters()
	{
		$tpl = new Dwoo_Template_String('{"test"} <%"test"%> <%"foo{lol}%>"%>');
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

	/**
	 * @expectedException Dwoo_Exception
	 */
	public function testAddPreProcessorWithBadName()
	{
		$cmp = new Dwoo_Compiler();
		$cmp->addPreProcessor('__BAAAAD__', true);

		$tpl = new Dwoo_Template_String('');
		$tpl->forceCompilation();
		$this->dwoo->get($tpl, array(), $cmp);
	}

	/**
	 * @expectedException Dwoo_Exception
	 */
	public function testAddPostProcessorWithBadName()
	{
		$cmp = new Dwoo_Compiler();
		$cmp->addPostProcessor('__BAAAAD__', true);

		$tpl = new Dwoo_Template_String('');
		$tpl->forceCompilation();
		$this->dwoo->get($tpl, array(), $cmp);
	}

	/**
	 * @expectedException Dwoo_Compilation_Exception
	 */
	public function testCloseUnopenedBlock()
	{
		$tpl = new Dwoo_Template_String('{/foreach}');
		$tpl->forceCompilation();

		$this->dwoo->get($tpl, array(), $this->compiler);
	}

	/**
	 * @expectedException Dwoo_Compilation_Exception
	 */
	public function testParseError()
	{
		$tpl = new Dwoo_Template_String('{++}');
		$tpl->forceCompilation();

		$this->dwoo->get($tpl, array(), $this->compiler);
	}

	/**
	 * @expectedException Dwoo_Compilation_Exception
	 */
	public function testUnfinishedStringException()
	{
		$tpl = new Dwoo_Template_String('{"fooo}');
		$tpl->forceCompilation();

		$this->dwoo->get($tpl, array(), $this->compiler);
	}

	/**
	 * @expectedException Dwoo_Compilation_Exception
	 */
	public function testMissingArgumentException()
	{
		$tpl = new Dwoo_Template_String('{upper()}');
		$tpl->forceCompilation();

		$this->dwoo->get($tpl, array('foo'=>0), $this->compiler);
	}

	/**
	 * @expectedException Dwoo_Compilation_Exception
	 */
	public function testMissingArgumentExceptionVariation2()
	{
		$tpl = new Dwoo_Template_String('{upper foo=bar}');
		$tpl->forceCompilation();

		$this->dwoo->get($tpl, array(), $this->compiler);
	}

	/**
	 * @expectedException Dwoo_Compilation_Exception
	 */
	public function testUnclosedTemplateTag()
	{
		$tpl = new Dwoo_Template_String('aa{upper foo=bar');
		$tpl->forceCompilation();

		$this->dwoo->get($tpl, array(), $this->compiler);
	}

	public function testAutoEscape()
	{
		$cmp = new Dwoo_Compiler();
		$cmp->setAutoEscape(true);
		$this->assertEquals(true, $cmp->getAutoEscape());

		$tpl = new Dwoo_Template_String('{$foo}{$foo|safe}');
		$tpl->forceCompilation();

		$this->assertEquals("a&lt;b&gt;ca<b>c", $this->dwoo->get($tpl, array('foo'=>'a<b>c'), $cmp));
	}

	public function testAutoEscapeWithFunctionCall()
	{
		$cmp = new Dwoo_Compiler();
		$cmp->setAutoEscape(true);
		$this->assertEquals(true, $cmp->getAutoEscape());

		$tpl = new Dwoo_Template_String('{upper $foo}{upper $foo|safe}');
		$tpl->forceCompilation();

		$this->assertEquals("A&LT;B&GT;CA<B>C", $this->dwoo->get($tpl, array('foo'=>'a<b>c'), $cmp));
	}

	public function testPhpInjection()
	{
		$tpl = new Dwoo_Template_String('{$foo}');
		$tpl->forceCompilation();

		$this->assertEquals('a <?php echo "foo"; ?>', $this->dwoo->get($tpl, array('foo'=>'a <?php echo "foo"; ?>'), $this->compiler));
	}

	public function testStaticMethodCall()
	{
		$tpl = new Dwoo_Template_String('{upper MethodCallsHelper::staticFoo(bar "baz")}');
		$tpl->forceCompilation();

		$this->assertEquals('-BAZBAR-', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testFunctionCallsChaining()
	{
		$tpl = new Dwoo_Template_String('{getobj()->foo()->Bar("hoy") getobj()->moo}');
		$tpl->forceCompilation();
		$dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
		$dwoo->addPlugin('getobj', array(new PluginHelper(), 'call'));

		$this->assertEquals('HOYyay', $dwoo->get($tpl, array(), $this->compiler));
	}

	public function testPluginProxy()
	{
		$proxy = new ProxyHelper('baz',true,3);
		$dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
		$dwoo->setPluginProxy($proxy);
		$tpl = new Dwoo_Template_String('{TestProxy("baz", true, 3)}');
		$tpl->forceCompilation();

		$this->assertEquals('valid', $dwoo->get($tpl, array(), $this->compiler));
	}

	public function testCallingMethodOnPropery()
	{
		$tpl = new Dwoo_Template_String('{getobj()->instance->Bar("hoy")}');
		$tpl->forceCompilation();
		$dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
		$dwoo->addPlugin('getobj', array(new PluginHelper(), 'call'));

		$this->assertEquals('HOY', $dwoo->get($tpl, array(), $this->compiler));
	}
}

class ProxyHelper implements Dwoo_IPluginProxy
{
	public function __construct()
	{
		$this->params = func_get_args();
	}

	public function handles($name)
	{
		return $name === 'TestProxy';
	}

	public function checkTestProxy()
	{
		return func_get_args() === $this->params ? 'valid' : 'fubar';
	}

	public function getCode($m, $p)
	{
		if (isset($p['*'])) {
			return '$this->getPluginProxy()->check'.$m.'('.implode(',', $p['*']).')';
		} else {
			return '$this->getPluginProxy()->check'.$m.'()';
		}
	}

	public function getCallback($name)
	{
		return array($this, 'callbackHelper');
	}

	public function getLoader($name)
	{
		return '';
	}

	private function callbackHelper(array $rest = array()) {

	}
}

class PluginHelper
{
	public $moo = "yay";
	public $instance;

	public function __construct()
	{
		$this->instance = $this;
	}

	public function callWithDwoo(Dwoo $dwoo)
	{
		return $this;
	}

	public function call()
	{
		return $this;
	}

	public function foo()
	{
		return $this;
	}

	public function Bar($a)
	{
		return strtoupper($a);
	}
}

class MethodCallsHelper
{
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

	public static function staticFoo($bar, $baz) {
		return "-$baz$bar-";
	}
}
