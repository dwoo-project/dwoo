<?php

require_once dirname(dirname(__FILE__)).'/DwooCompiler.php';

class CompilerTests extends PHPUnit_Framework_TestCase
{
    protected $compiler;
    protected $dwoo;

    public function __construct()
    {
        // extend this class and override this in your constructor to test a modded compiler
        $this->compiler = new DwooCompiler();
        $this->dwoo = new Dwoo();
    }

    public function testVarReplacement()
    {
        $tpl = new DwooTemplateString('{$foo}');
        $tpl->forceCompilation();

        $this->assertEquals('bar', $this->dwoo->get($tpl, array('foo'=>'bar'), $this->compiler));
    }

    public function testModifier()
    {
        $tpl = new DwooTemplateString('{$foo|upper}');
        $tpl->forceCompilation();

        $this->assertEquals('BAR', $this->dwoo->get($tpl, array('foo'=>'bar'), $this->compiler));
    }

    public function testModifierArgs()
    {
        $tpl = new DwooTemplateString('{$foo|spacify:"-"|upper}');
        $tpl->forceCompilation();

        $this->assertEquals('B-A-R', $this->dwoo->get($tpl, array('foo'=>'bar'), $this->compiler));
    }

    public function testDwooFunc()
    {
        $tpl = new DwooTemplateString('{upper($foo)}');
        $tpl->forceCompilation();

        $this->assertEquals('BAR', $this->dwoo->get($tpl, array('foo'=>'bar'), $this->compiler));
    }

    public function testDwooLoose()
    {
        $tpl = new DwooTemplateString('{upper $foo}');
        $tpl->forceCompilation();

        $this->assertEquals('BAR', $this->dwoo->get($tpl, array('foo'=>'bar'), $this->compiler));
    }

    public function testNamedParameter()
    {
        $tpl = new DwooTemplateString('{upper value=$foo}');
        $tpl->forceCompilation();

        $this->assertEquals('BAR', $this->dwoo->get($tpl, array('foo'=>'bar'), $this->compiler));
    }

    public function testNamedParameter2()
    {
        $tpl = new DwooTemplateString('{replace value=$foo search="BAR"|lower replace="BAR"}');
        $tpl->forceCompilation();

        $this->assertEquals('BAR', $this->dwoo->get($tpl, array('foo'=>'bar'), $this->compiler));
    }

    public function testNamedParameter3()
    {
        $tpl = new DwooTemplateString('{assign value=reverse(array(foo=3,boo=5, 3=4)) var=arr}{foreach $arr k v}{$k}{$v}{/foreach}');
        $tpl->forceCompilation();

        $this->assertEquals('34boo5foo3', $this->dwoo->get($tpl, array(), $this->compiler));
    }

    public function testRecursiveCall()
    {
        $tpl = new DwooTemplateString('{lower(reverse(upper($foo)))}');
        $tpl->forceCompilation();

        $this->assertEquals('rab', $this->dwoo->get($tpl, array('foo'=>'bar'), $this->compiler));
    }

    public function testComplexRecursiveCall()
    {
        $tpl = new DwooTemplateString('{lower reverse($foo|reverse|upper)}');
        $tpl->forceCompilation();

        $this->assertEquals('bar', $this->dwoo->get($tpl, array('foo'=>'BaR'), $this->compiler));
    }

    public function testComplexRecursiveCall2()
    {
        $tpl = new DwooTemplateString('{str_repeat "AB`$foo|reverse|spacify:o`CD" 3}');
        $tpl->forceCompilation();
        $this->assertEquals('AB3o2o1CDAB3o2o1CDAB3o2o1CD', $this->dwoo->get($tpl, array('foo'=>'123'), $this->compiler));
    }

    public function testStrip()
    {
        $tpl = new DwooTemplateString("{strip}a\nb\nc{/strip}a\nb\nc");
        $tpl->forceCompilation();
        $this->assertEquals("abca\nb\nc", $this->dwoo->get($tpl, array(), $this->compiler));
    }

    public function testWhitespace()
    {
        $tpl = new DwooTemplateString("{\$foo}{\$foo}\n{\$foo}\n\n{\$foo}\n\n\n{\$foo}");
        $tpl->forceCompilation();
        $this->assertEquals("aa\na\n\na\n\n\na", $this->dwoo->get($tpl, array('foo'=>'a'), $this->compiler));
    }

    public function testLiteral()
    {
        $tpl = new DwooTemplateString('{literal}{$foo}{hurray}{/literal}');
        $tpl->forceCompilation();
        $this->assertEquals('{$foo}{hurray}', $this->dwoo->get($tpl, array(), $this->compiler));
    }

    public function testEscaping()
    {
        $tpl = new DwooTemplateString('\{foo\{bar\\\\{$var}}{"tes\}t"}{"foo\"lol\"bar"}');
        $tpl->forceCompilation();
        $this->assertEquals('{foo{bar\\1}tes}tfoo"lol"bar', $this->dwoo->get($tpl, array('var'=>1), $this->compiler));
    }

    public function testFunctions()
    {
        $tpl = new DwooTemplateString('{dump()}{dump( )}{dump}');
        $tpl->forceCompilation();
        $this->assertEquals('<div style="background:#aaa; padding:5px; margin:5px;">data (current scope): <div style="background:#ccc;"></div></div><div style="background:#aaa; padding:5px; margin:5px;">data (current scope): <div style="background:#ccc;"></div></div><div style="background:#aaa; padding:5px; margin:5px;">data (current scope): <div style="background:#ccc;"></div></div>', $this->dwoo->get($tpl, array(), $this->compiler));
    }

    public function testExpressions()
    {
        $tpl = new DwooTemplateString('{$foo+5} {$foo+$foo} {$foo+3*$foo} {$foo*$foo+4*$foo} {$foo*2/2|number_format} {$foo*2/3|number_format:1} {number_format $foo*2/3 1}');
        $tpl->forceCompilation();

        $this->assertEquals("10 10 40 145 5 3.3 3.3", $this->dwoo->get($tpl, array('foo'=>5), $this->compiler));

        $tpl = new DwooTemplateString('{"$foo/$foo"}');
        $tpl->forceCompilation();

        $this->assertEquals("1", $this->dwoo->get($tpl, array('foo'=>5), $this->compiler));
    }

    public function testNonExpressions()
    {
        $tpl = new DwooTemplateString('{"`$foo`/`$foo`"}');
        $tpl->forceCompilation();

        $this->assertEquals("5/5", $this->dwoo->get($tpl, array('foo'=>5), $this->compiler));
    }

    public function testConstants()
    {
        if(!defined('TEST'))
            define('TEST', 'Test');
        $tpl = new DwooTemplateString('{$dwoo.const.TEST} {$dwoo.const.Dwoo::FUNC_PLUGIN*$dwoo.const.Dwoo::BLOCK_PLUGIN}');
        $tpl->forceCompilation();

        $this->assertEquals(TEST.' '.(Dwoo::FUNC_PLUGIN*Dwoo::BLOCK_PLUGIN), $this->dwoo->get($tpl, array(), $this->compiler));
    }

    public function testAltDelimiters()
    {
        $tpl = new DwooTemplateString('{"test"} <%"test"%> <%"foo{lol}\%>"%>');
        $tpl->forceCompilation();
        $this->compiler->setDelimiters('<%', '%>');
        $this->assertEquals('{"test"} test foo{lol}%>', $this->dwoo->get($tpl, array(), $this->compiler));

        $tpl = new DwooTemplateString('d"O"b');
        $tpl->forceCompilation();
        $this->compiler->setDelimiters('d', 'b');
        $this->assertEquals('O', $this->dwoo->get($tpl, array(), $this->compiler));

        $tpl = new DwooTemplateString('<!-- "O" --> \<!-- ');
        $tpl->forceCompilation();
        $this->compiler->setDelimiters('<!-- ', ' -->');
        $this->assertEquals('O <!-- ', $this->dwoo->get($tpl, array(), $this->compiler));

        $this->compiler->setDelimiters('{', '}');
    }

    public function testNumberedIndexes()
    {
        $tpl = new DwooTemplateString('{$100}-{$150}-{$0}');
        $tpl->forceCompilation();

        $this->assertEquals('bar-foo-', $this->dwoo->get($tpl, array('100'=>'bar', 150=>'foo'), $this->compiler));
    }

    public function testParseBool()
    {
        $tpl = new DwooTemplateString('{if (true === yes && true === on) && (false===off && false===no)}okay{/if}');
        $tpl->forceCompilation();

        $this->assertEquals('okay', $this->dwoo->get($tpl, array(), $this->compiler));
    }

    public function testMethodCalls()
    {
        $tpl = new DwooTemplateString('{$a} {$a->foo()} {$b[$c]->foo()} {$a->bar()+$a->bar()} {$a->baz(5, $foo)} {$a->make(5)->getInt()} {$a->make(5)->getInt()/2}');
        $tpl->forceCompilation();

        $a = new MethodCallsHelper();
        $this->assertEquals('obj 0 1 7 10bar 5 2.5', $this->dwoo->get($tpl, array('a'=>$a, 'b'=>array('test'=>$a), 'c'=>'test', 'foo'=>'bar'), $this->compiler));
    }

    public function testLooseTagHandling()
    {
    	$this->compiler->setLooseOpeningHandling(true);
    	$this->assertEquals($this->compiler->getLooseOpeningHandling(), true);

        $tpl = new DwooTemplateString('{     $a      }{$a     }{     $a}{$a}');
        $tpl->forceCompilation();

        $this->assertEquals('moomoomoomoo', $this->dwoo->get($tpl, array('a'=>'moo'), $this->compiler));

	   	$this->compiler->setLooseOpeningHandling(false);
        $tpl = new DwooTemplateString('{     $a      }{$a     }{     $a}{$a}');
        $tpl->forceCompilation();

        $this->assertEquals('{     $a      }moo{     $a}moo', $this->dwoo->get($tpl, array('a'=>'moo'), $this->compiler));
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

?>