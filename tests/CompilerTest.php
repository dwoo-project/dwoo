<?php

namespace Dwoo\Tests
{

    use Dwoo\Compiler;
    use Dwoo\Core;
    use PluginHelper;
    use Dwoo\Template\Str as TemplateString;
    use Dwoo\Template\File as TemplateFile;
    use Dwoo\Security\Policy as SecurityPolicy;

    /**
     * Class CompilerTest
     *
     * @package Dwoo\Tests
     */
    class CompilerTest extends BaseTests
    {
        const FOO = 3;

        public function testVarReplacement()
        {
            $tpl = new TemplateString('{$foo}');
            $tpl->forceCompilation();

            $this->assertEquals('bar', $this->dwoo->get($tpl, array('foo' => 'bar'), $this->compiler));
        }

        public function testVariableObjectPropertyAccess()
        {
            $tpl = new TemplateString('{$obj->$var}');
            $tpl->forceCompilation();

            $this->assertEquals('yay', $this->dwoo->get($tpl, array(
                'obj' => new PluginHelper(),
                'var' => 'moo'
            ), $this->compiler));
        }

        public function testComplexVarReplacement()
        {
            $tpl = new TemplateString('{$_root[$a].0}{$_[$a][0]}{$_[$c.d].0}{$_.$a.0}{$_[$c[$x.0]].0}{$_[$c.$y.0].0}');
            $tpl->forceCompilation();

            $this->assertEquals('cccccc', $this->dwoo->get($tpl, array(
                'a' => 'b',
                'x' => array('d'),
                'y' => 'e',
                'b' => array('c', 'd'),
                'c' => array('d' => 'b', 'e' => array('b'))
            ), $this->compiler));
        }

        public function testModifier()
        {
            $tpl = new TemplateString('{$foo|upper}');
            $tpl->forceCompilation();

            $this->assertEquals('BAR', $this->dwoo->get($tpl, array('foo' => 'bar'), $this->compiler));
        }

        public function testModifierArgs()
        {
            $tpl = new TemplateString('{$foo|spacify:"-"|upper}');
            $tpl->forceCompilation();

            $this->assertEquals('B-A-R', $this->dwoo->get($tpl, array('foo' => 'bar'), $this->compiler));
        }

        public function testModifierArgsVars()
        {
            $tpl = new TemplateString('{$foo|spacify:$bar|upper}');
            $tpl->forceCompilation();

            $this->assertEquals('B-A-R', $this->dwoo->get($tpl, array('foo' => 'bar', 'bar' => '-'), $this->compiler));
        }

        public function testModifierOnString()
        {
            $tpl = new TemplateString('{"bar"|spacify:"-"|upper}');
            $tpl->forceCompilation();

            $this->assertEquals('B-A-R', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testModifierOnStringWithVar()
        {
            $tpl = new TemplateString('{"bar"|spacify:$bar|upper}');
            $tpl->forceCompilation();

            $this->assertEquals('B-A-R', $this->dwoo->get($tpl, array('bar' => '-'), $this->compiler));
        }

        public function testModifierWithModifier()
        {
            $tpl = new TemplateString('{$foo.0}{assign $foo|reverse foo}{$foo.0}{assign $foo|@reverse foo}{$foo.0}');
            $tpl->forceCompilation();

            $this->assertEquals('barbazzab', $this->dwoo->get($tpl, array(
                'foo' => array(
                    'bar',
                    'baz'
                )
            ), $this->compiler));
        }

        public function testDwooFunc()
        {
            $tpl = new TemplateString('{upper($foo)}');
            $tpl->forceCompilation();

            $this->assertEquals('BAR', $this->dwoo->get($tpl, array('foo' => 'bar'), $this->compiler));
        }

        public function testDwooLoose()
        {
            $tpl = new TemplateString('{upper $foo}');
            $tpl->forceCompilation();

            $this->assertEquals('BAR', $this->dwoo->get($tpl, array('foo' => 'bar'), $this->compiler));
        }

        public function testNamedParameter()
        {
            $tpl = new TemplateString('{upper value=$foo}');
            $tpl->forceCompilation();

            $this->assertEquals('BAR', $this->dwoo->get($tpl, array('foo' => 'bar'), $this->compiler));
        }

        public function testNamedParameter2()
        {
            $tpl = new TemplateString('{replace value=$foo search="BAR"|lower replace="BAR"}');
            $tpl->forceCompilation();

            $this->assertEquals('BAR', $this->dwoo->get($tpl, array('foo' => 'bar'), $this->compiler));
        }

        public function testNamedParameter3()
        {
            $tpl = new TemplateString('{assign value=reverse(array(foo=3,boo=5, 3=4), true) var=arr}{foreach $arr k v}{$k}{$v}{/foreach}');
            $tpl->forceCompilation();

            $this->assertEquals('34boo5foo3', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testQuotedNamedParameters()
        {
            $tpl = new TemplateString('{assign \'value\'=reverse(array("foo"=3,boo=5, 3=4), true) "var"=arr}{foreach $arr k v}{$k}{$v}{/foreach}');
            $tpl->forceCompilation();

            $this->assertEquals('34boo5foo3', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testMixedParameters()
        {
            $tpl = new TemplateString('{assign value=array(3, boo=5, 3=4) var=arr}{foreach $arr k v}{$k}{$v}{/foreach}');
            $tpl->forceCompilation();

            $this->assertEquals('03boo534', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        /**
         * @expectedException \Dwoo\Compilation\Exception
         */
        public function testMixedParametersWrongOrder()
        {
            $tpl = new TemplateString('{assign value=5, 3)}');
            $tpl->forceCompilation();
            $this->dwoo->get($tpl, array(), $this->compiler);
        }

        public function testMixedParamsMultiline()
        {
            $tpl = new TemplateString('{replace(
												$foo	search="BAR"|lower
replace="BAR"
)}');
            $tpl->forceCompilation();

            $this->assertEquals('BAR', $this->dwoo->get($tpl, array('foo' => 'bar'), $this->compiler));

            $tpl = new TemplateString('{replace(
												$foo	search=$bar|lower
replace="BAR"
)}');
            $tpl->forceCompilation();

            $this->assertEquals('BAR', $this->dwoo->get($tpl, array('foo' => 'bar', 'bar' => 'BAR'), $this->compiler));
        }

        public function testRecursiveCall()
        {
            $tpl = new TemplateString('{lower(reverse(upper($foo)))}');
            $tpl->forceCompilation();

            $this->assertEquals('rab', $this->dwoo->get($tpl, array('foo' => 'bar'), $this->compiler));
        }

        public function testComplexRecursiveCall()
        {
            $tpl = new TemplateString('{lower reverse($foo|reverse|upper)}');
            $tpl->forceCompilation();

            $this->assertEquals('bar', $this->dwoo->get($tpl, array('foo' => 'BaR'), $this->compiler));
        }

        public function testComplexRecursiveCall2()
        {
            $tpl = new TemplateString('{str_repeat "AB`$foo|reverse|spacify:o`CD" 3}');
            $tpl->forceCompilation();
            $this->assertEquals('AB3o2o1CDAB3o2o1CDAB3o2o1CD', $this->dwoo->get($tpl, array('foo' => '123'), $this->compiler));
        }

        public function testWhitespace()
        {
            $tpl = new TemplateString("{\$foo}{\$foo}\n{\$foo}\n\n{\$foo}\n\n\n{\$foo}");
            $tpl->forceCompilation();
            $this->assertEquals("aa\na\n\na\n\n\na", $this->dwoo->get($tpl, array('foo' => 'a'), $this->compiler));
        }

        public function testLiteral()
        {
            $tpl = new TemplateString('{literal}{$foo}{hurray}{/literal}');
            $tpl->forceCompilation();
            $this->assertEquals('{$foo}{hurray}', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        /**
         * @expectedException \Dwoo\Compilation\Exception
         */
        public function testUnclosedLiteral()
        {
            $tpl = new TemplateString('{literal}{$foo}{hurray}');
            $tpl->forceCompilation();
            $this->dwoo->get($tpl, array(), $this->compiler);
        }

        public function testEscaping()
        {
            $tpl = new TemplateString('\{foo\{bar\\\\{$var}}{"tes}t"}{"foo\"lol\"bar"}');
            $tpl->forceCompilation();
            $this->assertEquals('{foo{bar\\1}tes}tfoo"lol"bar', $this->dwoo->get($tpl, array('var' => 1), $this->compiler));
        }

        public function testFunctions()
        {
            $tpl = new TemplateString('{dump()}{dump( )}{dump}');
            $tpl->forceCompilation();
            $this->assertEquals('<div style="background:#aaa; padding:5px; margin:5px; color:#000;">data (current scope): <div style="background:#ccc;"></div></div><div style="background:#aaa; padding:5px; margin:5px; color:#000;">data (current scope): <div style="background:#ccc;"></div></div><div style="background:#aaa; padding:5px; margin:5px; color:#000;">data (current scope): <div style="background:#ccc;"></div></div>', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testExpressions()
        {
            $tpl = new TemplateString('{$foo+5} {$foo+$foo} {$foo+3*$foo} {$foo*$foo+4*$foo} {$foo*2/2|number_format} {$foo*2/3|number_format:1} {number_format $foo*2/3 1} {if $foo+5>9 && $foo < 7 && $foo+$foo==$foo*2}win{/if} {$arr[$foo+3]}');
            $tpl->forceCompilation();

            $this->assertEquals('10 10 40 145 5 3.3 3.3 win win', $this->dwoo->get($tpl, array(
                'foo' => 5,
                'arr' => array(8 => 'win')
            ), $this->compiler));
        }

        public function testDelimitedExpressionsInString()
        {
            $tpl = new TemplateString('{"`$foo/$foo`"}');
            $tpl->forceCompilation();

            $this->assertEquals('1', $this->dwoo->get($tpl, array('foo' => 5), $this->compiler));
        }

        public function testNonDelimitedExpressionsInString()
        {
            $tpl = new TemplateString('{"$foo/$foo"}');
            $tpl->forceCompilation();

            $this->assertEquals('5/5', $this->dwoo->get($tpl, array('foo' => 5), $this->compiler));
        }

        public function testConstants()
        {
            if (!defined('TEST')) {
                define('TEST', 'Test');
            }
            $tpl = new TemplateString('{$dwoo.const.TEST}');
            $tpl->forceCompilation();

            $this->assertEquals(TEST, $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testShortConstants()
        {
            if (!defined('TEST')) {
                define('TEST', 'Test');
            }
            $tpl = new TemplateString('{%TEST} {$dwoo.const.PHP_MAJOR_VERSION*%PHP_MINOR_VERSION}');
            $tpl->forceCompilation();

            $this->assertEquals(TEST . ' ' . (PHP_MAJOR_VERSION * PHP_MINOR_VERSION), $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testClassConstants()
        {
            $tpl = new TemplateString('{$dwoo.const.Dwoo\Core::FUNC_PLUGIN*$dwoo.const.Dwoo\Core::BLOCK_PLUGIN}');
            $tpl->forceCompilation();
            $this->assertEquals((Core::FUNC_PLUGIN * Core::BLOCK_PLUGIN), $this->dwoo->get($tpl, array(), $this->compiler));

            $tpl = new TemplateString('{$dwoo.const.Dwoo\\Core::FUNC_PLUGIN*$dwoo.const.Dwoo\\Core::BLOCK_PLUGIN}');
            $tpl->forceCompilation();
            $this->assertEquals((Core::FUNC_PLUGIN * Core::BLOCK_PLUGIN), $this->dwoo->get($tpl, array(), $this->compiler));

            $tpl = new TemplateFile(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'resources' . DIRECTORY_SEPARATOR . 'constants.html');
            $tpl->forceCompilation();
            $this->assertEquals((Core::FUNC_PLUGIN * Core::BLOCK_PLUGIN) . "\n" . (Core::FUNC_PLUGIN * Core::BLOCK_PLUGIN), $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testShortClassConstants()
        {
            $tpl = new TemplateString('{if %Dwoo\Tests\CompilerTest::FOO == 3}{%Dwoo\Tests\CompilerTest::FOO}{/}');
            $tpl->forceCompilation();

            $this->assertEquals(self::FOO, $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testAltDelimiters()
        {
            $tpl = new TemplateString('{"test"} <%"test"%> <%"foo{lol}%>"%>');
            $tpl->forceCompilation();
            $this->compiler->setDelimiters('<%', '%>');
            $this->assertEquals('{"test"} test foo{lol}%>', $this->dwoo->get($tpl, array(), $this->compiler));

            $tpl = new TemplateString('d"O"b');
            $tpl->forceCompilation();
            $this->compiler->setDelimiters('d', 'b');
            $this->assertEquals('O', $this->dwoo->get($tpl, array(), $this->compiler));

            $tpl = new TemplateString('<!-- "O" --> \<!-- ');
            $tpl->forceCompilation();
            $this->compiler->setDelimiters('<!-- ', ' -->');
            $this->assertEquals('O <!-- ', $this->dwoo->get($tpl, array(), $this->compiler));

            $this->compiler->setDelimiters('{', '}');
        }

        public function testNumberedIndexes()
        {
            $tpl = new TemplateString('{$100}-{$150}-{if $0}FAIL{/if}');
            $tpl->forceCompilation();

            $this->assertEquals('bar-foo-', $this->dwoo->get($tpl, array(
                '100' => 'bar',
                150   => 'foo'
            ), $this->compiler));
        }

        public function testParseBool()
        {
            $tpl = new TemplateString('{if (true === yes && true === on) && (false===off && false===no)
            }okay{/if}');
            $tpl->forceCompilation();

            $this->assertEquals('okay', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testMethodCalls()
        {
            $tpl = new TemplateString('{$a} {$a->foo()} {$b[$c]->foo()} {$a->bar()+$a->bar()} {$a->baz(5, $foo)} {$a->make(5)->getInt()} {$a->make(5)->getInt()/2} {$a->_foo($foo, 5)} {$a->_fooChain()->_foo(5, $foo)}');
            $tpl->forceCompilation();

            $a = new \MethodCallsHelper();
            $this->assertEquals('obj 0 1 7 10bar 5 2.5 -5bar- -bar5-', $this->dwoo->get($tpl, array(
                'a'   => $a,
                'b'   => array('test' => $a),
                'c'   => 'test',
                'foo' => 'bar'
            ), $this->compiler));
        }

        public function testLooseTagHandling()
        {
            $this->compiler->setLooseOpeningHandling(true);
            $this->assertEquals($this->compiler->getLooseOpeningHandling(), true);

            $tpl = new TemplateString('{     $a      }{$a     }{     $a}{$a}');
            $tpl->forceCompilation();

            $this->assertEquals('moomoomoomoo', $this->dwoo->get($tpl, array('a' => 'moo'), $this->compiler));

            $this->compiler->setLooseOpeningHandling(false);
            $tpl = new TemplateString('{     $a      }{$a     }{     $a}{$a}');
            $tpl->forceCompilation();

            $this->assertEquals('{     $a      }moo{     $a}moo', $this->dwoo->get($tpl, array('a' => 'moo'), $this->compiler));
        }

        public function testDwooDotShortcut()
        {
            $tpl = new TemplateString('{$.server.SCRIPT_NAME}{foreach $a item}{$.foreach.default.iteration}{$item}{$.foreach.$b.$c}{/foreach}');
            $tpl->forceCompilation();

            $this->assertEquals($_SERVER['SCRIPT_NAME'] . '1a12b2', $this->dwoo->get($tpl, array(
                'a' => array('a', 'b'),
                'b' => 'default',
                'c' => 'iteration'
            ), $this->compiler));
        }

        public function testRootAndParentShortcut()
        {
            $tpl = new TemplateString('{with $a}{$__.b}{$_.b}{$0}{/with}{$__.b}');
            $tpl->forceCompilation();

            $this->assertEquals('defaultdefaultadefault', $this->dwoo->get($tpl, array(
                'a' => array('a', 'b'),
                'b' => 'default'
            ), $this->compiler));
        }

        public function testCurrentScopeShortcut()
        {
            $tpl = new TemplateString('{loop $}{$_key} > {loop $}{$}{/loop}{/loop}');
            $tpl->forceCompilation();

            $this->assertEquals('1 > ab2 > cd', $this->dwoo->get($tpl, array(
                '1' => array('a', 'b'),
                '2' => array('c', 'd')
            ), $this->compiler));

            $tpl = new TemplateString('{with $a}{$1}{/with}{loop $a}{$}{/loop}');
            $tpl->forceCompilation();

            $this->assertEquals('bab', $this->dwoo->get($tpl, array(
                'a' => array('a', 'b'),
                'b' => 'default',
                'c' => 'iteration'
            ), $this->compiler));
        }

        public function testCloseBlockShortcut()
        {
            $tpl = new TemplateString('{loop $}{$_key} > {loop $}{$}{/}{/}');
            $tpl->forceCompilation();

            $this->assertEquals('1 > ab2 > cd', $this->dwoo->get($tpl, array(
                '1' => array('a', 'b'),
                '2' => array('c', 'd')
            ), $this->compiler));
        }

        public function testImplicitCloseBlock()
        {
            $tpl = new TemplateString('{loop $}{$_key} > {loop $}{$}');
            $tpl->forceCompilation();

            $this->assertEquals('1 > ab2 > cd', $this->dwoo->get($tpl, array(
                '1' => array('a', 'b'),
                '2' => array('c', 'd')
            ), $this->compiler));
        }

        public function testAssignAndIncrement()
        {
            $tpl = new TemplateString('{$foo}{$foo+=3}{$foo}
{$foo}{$foo-=3}{$foo}
{$foo++}{$foo++}{$foo}{$foo*=$foo}{$foo}
{$foo--}{$foo=5}{$foo}');
            $tpl->forceCompilation();

            $this->assertEquals("03\n30\n0124\n45", $this->dwoo->get($tpl, array('foo' => 0), $this->compiler));

            $tpl = new TemplateString('{$foo="moo"}{$foo}{$foo=math("3+5")}{$foo}');
            $tpl->forceCompilation();

            $this->assertEquals('moo8', $this->dwoo->get($tpl, array('foo' => 0), $this->compiler));
        }

        public function testAssignAndConcatenate()
        {
            $tpl = new TemplateString('{$foo="test"}{$foo.="test"}{$foo}');
            $tpl->forceCompilation();

            $this->assertEquals('testtest', $this->dwoo->get($tpl, array('foo' => 0), $this->compiler));

            $tpl = new TemplateString('{$foo.="baz"}{$foo|upper}');
            $tpl->forceCompilation();

            $this->assertEquals('BARBAZ', $this->dwoo->get($tpl, array('foo' => 'bar'), $this->compiler));
        }

        public function testSetStringValToTrueWhenUsingNamedParams()
        {
            $this->dwoo->addPlugin('test', create_function('Dwoo\Core $dwoo, $name, $bool=false', 'return $bool ? $name."!" : $name."?";'));
            $tpl = new TemplateString('{test name="Name"}{test name="Name" bool}');
            $tpl->forceCompilation();

            $this->assertEquals('Name?Name!', $this->dwoo->get($tpl, array(), $this->compiler));
            $this->dwoo->removePlugin('test');
        }

        /**
         * @expectedException \Dwoo\Exception
         */
        public function testAddPreProcessorWithBadName()
        {
            $cmp = new Compiler();
            $cmp->addPreProcessor('__BAAAAD__', true);

            $tpl = new TemplateString('');
            $tpl->forceCompilation();
            $this->dwoo->get($tpl, array(), $cmp);
        }

        /**
         * @expectedException \Dwoo\Exception
         */
        public function testAddPostProcessorWithBadName()
        {
            $cmp = new Compiler();
            $cmp->addPostProcessor('__BAAAAD__', true);

            $tpl = new TemplateString('');
            $tpl->forceCompilation();
            $this->dwoo->get($tpl, array(), $cmp);
        }

        /**
         * @expectedException \Dwoo\Compilation\Exception
         */
        public function testCloseUnopenedBlock()
        {
            $tpl = new TemplateString('{/foreach}');
            $tpl->forceCompilation();

            $this->dwoo->get($tpl, array(), $this->compiler);
        }

        /**
         * @expectedException \Dwoo\Compilation\Exception
         */
        public function testParseError()
        {
            $tpl = new TemplateString('{++}');
            $tpl->forceCompilation();

            $this->dwoo->get($tpl, array(), $this->compiler);
        }

        /**
         * @expectedException \Dwoo\Compilation\Exception
         */
        public function testUnfinishedStringException()
        {
            $tpl = new TemplateString('{"fooo}');
            $tpl->forceCompilation();

            $this->dwoo->get($tpl, array(), $this->compiler);
        }

        /**
         * @expectedException \Dwoo\Compilation\Exception
         */
        public function testMissingArgumentException()
        {
            $tpl = new TemplateString('{upper()}');
            $tpl->forceCompilation();

            $this->dwoo->get($tpl, array('foo' => 0), $this->compiler);
        }

        /**
         * @expectedException \Dwoo\Compilation\Exception
         */
        public function testMissingArgumentExceptionVariation2()
        {
            $tpl = new TemplateString('{upper foo=bar}');
            $tpl->forceCompilation();

            $this->dwoo->get($tpl, array(), $this->compiler);
        }

        /**
         * @expectedException \Dwoo\Compilation\Exception
         */
        public function testUnclosedTemplateTag()
        {
            $tpl = new TemplateString('aa{upper foo=bar');
            $tpl->forceCompilation();

            $this->dwoo->get($tpl, array(), $this->compiler);
        }

        public function testAutoEscape()
        {
            $cmp = new Compiler();
            $cmp->setAutoEscape(true);
            $this->assertEquals(true, $cmp->getAutoEscape());

            $tpl = new TemplateString('{$foo}{$foo|safe}');
            $tpl->forceCompilation();

            $this->assertEquals('a&lt;b&gt;ca<b>c', $this->dwoo->get($tpl, array('foo' => 'a<b>c'), $cmp));
        }

        public function testAutoEscapeWithFunctionCall()
        {
            $cmp = new Compiler();
            $cmp->setAutoEscape(true);
            $this->assertEquals(true, $cmp->getAutoEscape());

            $tpl = new TemplateString('{upper $foo}{upper $foo|safe}');
            $tpl->forceCompilation();

            $this->assertEquals('A&LT;B&GT;CA<B>C', $this->dwoo->get($tpl, array('foo' => 'a<b>c'), $cmp));
        }

        public function testPhpInjection()
        {
            $tpl = new TemplateString('{$foo}');
            $tpl->forceCompilation();

            $this->assertEquals('a <?php echo "foo"; ?>', $this->dwoo->get($tpl, array('foo' => 'a <?php echo "foo"; ?>'), $this->compiler));
        }

        public function testStaticMethodCall()
        {
            $tpl = new TemplateString('{upper MethodCallsHelper::staticFoo(bar "baz")}');
            $tpl->forceCompilation();

            $this->assertEquals('-BAZBAR-', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testFunctionCallsChaining()
        {
            $tpl = new TemplateString('{getobj()->foo()->Bar("hoy") getobj()->moo}');
            $tpl->forceCompilation();
            $this->dwoo->addPlugin('getobj', array(new PluginHelper(), 'call'));

            $this->assertEquals('HOYyay', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testPluginProxy()
        {
            $proxy = new \ProxyHelper('baz', true, 3);
            $this->dwoo->setPluginProxy($proxy);
            $tpl = new TemplateString('{TestProxy("baz", true, 3)}');
            $tpl->forceCompilation();

            $this->assertEquals('valid', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testCallingMethodOnProperty()
        {
            $tpl = new TemplateString('{getobj()->instance->Bar("hoy")}');
            $tpl->forceCompilation();
            $this->dwoo->addPlugin('getobj', array(new PluginHelper(), 'call'));

            $this->assertEquals('HOY', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testNestedCommentHandlingGetSet()
        {
            $cmp = new Compiler();
            $this->assertEquals(false, $cmp->getNestedCommentsHandling());
            $cmp->setNestedCommentsHandling(true);
            $this->assertEquals(true, $cmp->getNestedCommentsHandling());
        }

        public function testNestedCommentHandling()
        {
            $tpl = new TemplateString('{* foo {* bar *} baz *}');
            $tpl->forceCompilation();
            $cmp = new Compiler();
            $cmp->setNestedCommentsHandling(true);
            $this->assertEquals('', $this->dwoo->get($tpl, array(), $cmp));
        }

        public function testSecurityPolicyGetSet()
        {
            $cmp    = new Compiler();
            $policy = new SecurityPolicy();
            $this->assertEquals(null, $cmp->getSecurityPolicy());
            $cmp->setSecurityPolicy($policy);
            $this->assertEquals($policy, $cmp->getSecurityPolicy());
        }

        public function testPointerGetSet()
        {
            $cmp = new Compiler();
            $this->assertEquals(null, $cmp->getPointer());
            $cmp->setPointer(5);
            $this->assertEquals(5, $cmp->getPointer());
            $cmp->setPointer(5, true);
            $this->assertEquals(10, $cmp->getPointer());
        }

        public function testLineGetSet()
        {
            $cmp = new Compiler();
            $this->assertEquals(null, $cmp->getLine());
            $cmp->setLine(5);
            $this->assertEquals(5, $cmp->getLine());
            $cmp->setLine(5, true);
            $this->assertEquals(10, $cmp->getLine());
        }

        public function testTemplateSourceGetSet()
        {
            $cmp = new Compiler();
            $this->assertEquals(null, $cmp->getTemplateSource());
            $cmp->setTemplateSource('foobar');
            $cmp->setPointer(3);
            $this->assertEquals('foobar', $cmp->getTemplateSource());
            $this->assertEquals('bar', $cmp->getTemplateSource(true));
            $this->assertEquals('r', $cmp->getTemplateSource(5));
            $cmp->setTemplateSource('baz', true);
            $this->assertEquals('foobaz', $cmp->getTemplateSource());
            $this->assertEquals('baz', $cmp->getTemplateSource(true));
            $this->assertEquals('z', $cmp->getTemplateSource(5));
            $cmp->setTemplateSource('baz');
            $this->assertEquals('baz', $cmp->getTemplateSource());
            $this->assertEquals('', $cmp->getTemplateSource(true));
            $this->assertEquals('az', $cmp->getTemplateSource(1));
        }

        public function testEndInstruction()
        {
            $tpl = new TemplateString('{$foo = 4; $bar = 5; $foo; $bar}');
            $tpl->forceCompilation();
            $this->assertEquals('45', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testExpressionAsParameter()
        {
            $tpl = new TemplateString('{$foo = 4; $bar = 8; lower value=$foo + $bar}');
            $tpl->forceCompilation();
            $this->assertEquals('12', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testExpressionAsAssignment()
        {
            $tpl = new TemplateString('{$foo = 4; $bar = $foo + 5; $bar}');
            $tpl->forceCompilation();
            $this->assertEquals('9', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testModifierOnFunc()
        {
            $tpl = new TemplateString('{upper("fOo")|lower}');
            $tpl->forceCompilation();
            $this->assertEquals('foo', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testStaticPropertyAccess()
        {
            $tpl = new TemplateString('{StaticHelper::$foo}/{StaticHelper::$foo * StaticHelper::$foo + 5}/{upper StaticHelper::$foo}/{StaticHelper::$foo++}/{StaticHelper::$foo}');
            $tpl->forceCompilation();
            $this->assertEquals('33/1094/33/33/34', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testExcessiveArguments()
        {
            $tpl = new TemplateString('{excessArgsHelper a b c d e f}');
            $tpl->forceCompilation();
            $this->assertEquals('a:b:c:d:e:f', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testParsingOfMultilineIf()
        {
            $tpl = new TemplateString('{if 0
|| $null == "aa"}
fail
{/if}');
            $tpl->forceCompilation();
            $this->assertEquals('', trim($this->dwoo->get($tpl, array(), $this->compiler)));
        }

        public function testParsingOfMethodWithFollowingArgs()
        {
            $tpl = new TemplateString('{lower cat($obj->Bar("test"), "TEST")}');
            $tpl->forceCompilation();
            $this->assertEquals('testtest', $this->dwoo->get($tpl, array('obj' => new PluginHelper()), $this->compiler));
        }

        public function testFunctionCanStartWithUnderscore()
        {
            $tpl = new TemplateString('{_underscoreHelper("test", _underscoreHelper("bar", 10))|_underscoreModifierHelper}');
            $tpl->forceCompilation();
            $this->assertEquals('_--10bar-test-_', $this->dwoo->get($tpl, array(), $this->compiler));
        }

        public function testNamespaceStaticMethodAccess()
        {
            if (version_compare(phpversion(), '5.3.0', '<')) {
                $this->markTestSkipped();
            }
            include_once __DIR__ . '/resources/namespace.php';
            $tpl = new TemplateString('{\Dwoo\TestHelper::execute(foo)}');
            $tpl->forceCompilation();
            $this->assertEquals('foo', $this->dwoo->get($tpl, array()));
        }

        public function testNamespaceStaticVarAccess()
        {
            if (version_compare(phpversion(), '5.3.0', '<')) {
                $this->markTestSkipped();
            }
            include_once __DIR__ . '/resources/namespace.php';
            $tpl = new TemplateString('{\Dwoo\TestHelper::$var}');
            $tpl->forceCompilation();
            $this->assertEquals('foo', $this->dwoo->get($tpl, array()));
        }

        /**
         * @expectedException \Dwoo\Compilation\Exception
         */
        public function testElseWithoutIfIsInvalid()
        {
            $tpl = new TemplateString('{else}1{/}');
            $tpl->forceCompilation();
            $this->dwoo->get($tpl, array(), $this->compiler);
        }
    }
}
namespace
{

    function excessArgsHelper($a)
    {
        $args = func_get_args();

        return implode(':', $args);
    }

    function _underscoreHelper($foo, $bar)
    {
        return "-$bar$foo-";
    }

    function _underscoreModifierHelper($value)
    {
        return "_${value}_";
    }

    class StaticHelper
    {
        public static $foo = 33;
    }

    class ProxyHelper implements Dwoo\IPluginProxy
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
                return '$this->getPluginProxy()->check' . $m . '(' . implode(',', $p['*']) . ')';
            } else {
                return '$this->getPluginProxy()->check' . $m . '()';
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

        private function callbackHelper(array $rest = array())
        {
        }
    }

    class PluginHelper
    {
        public $moo = 'yay';
        public $instance;

        public function __construct()
        {
            $this->instance = $this;
        }

        public function callWithDwoo(Dwoo\Core $dwoo)
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
        public function __construct($int = 0)
        {
            $this->int = $int;
        }

        public static function staticFoo($bar, $baz)
        {
            return "-$baz$bar-";
        }

        public function getInt()
        {
            return $this->int;
        }

        public function make($a = 0)
        {
            return new self($a);
        }

        public function foo()
        {
            static $a = 0;

            return $a ++;
        }

        public function bar()
        {
            static $a = 3;

            return $a ++;
        }

        public function baz($int, $str)
        {
            return ($int + 5) . $str;
        }

        public function __toString()
        {
            return 'obj';
        }

        public function _foo($bar, $baz)
        {
            return "-$baz$bar-";
        }

        public function _fooChain()
        {
            return $this;
        }
    }
}