<?php

require_once DWOO_DIRECTORY . 'Dwoo/Compiler.php';

class BugTests extends PHPUnit_Framework_TestCase
{
    protected $compiler;
    protected $dwoo;

    public function __construct()
    {
        $this->compiler = new Dwoo_Compiler();
        $this->dwoo = new Dwoo_Core(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
    }

    public function testBlockStackBufferingBug()
    {
        $tpl = new Dwoo_Template_String('{textformat 10 wrap_char="\n"}here is some text that should wrap{/textformat}
{textformat 10 wrap_cut=true wrap_char="\n"}and this one should cut words that go beyooooooond 10 chars{/textformat}');
        $tpl->forceCompilation();

        $this->assertEquals("here is\nsome text\nthat\nshould\nwrap\nand this\none should\ncut words\nthat go\nbeyooooooo\nnd 10\nchars", $this->dwoo->get($tpl, array()));
    }

    public function testSpaceBeforeArgsBug()
    {
        $tpl = new Dwoo_Template_String('{upper ("moo")}.{upper    	("moo")}.{if (true) && (true)}MOO{/if}');
        $tpl->forceCompilation();

        $this->assertEquals("MOO.MOO.MOO", $this->dwoo->get($tpl, array()));
    }

    public function testEmptyStringArgInModifierCall()
    {
        $tpl = new Dwoo_Template_String('{$var|replace:"foo":""}');
        $tpl->forceCompilation();

        $this->assertEquals("ab", $this->dwoo->get($tpl, array('var'=>'afoob')));
    }

    public function testRecursiveVarModifiersCalls()
    {
        $tpl = new Dwoo_Template_String('{$var|replace:array("foo", "bar"):array("")}');
        $tpl->forceCompilation();

        $this->assertEquals("abc", $this->dwoo->get($tpl, array('var'=>'afoobbarc')));
    }

    public function testVarModifierCallWithSpaces()
    {
        $tpl = new Dwoo_Template_String('{"x$var|replace:array(\'foo\', bar):array(\"\") y"}');
        $tpl->forceCompilation();

        $this->assertEquals("xabc y", $this->dwoo->get($tpl, array('var'=>'afoobbarc')));
    }

    public function testVarModifierCallWithDelimiters()
    {
        $tpl = new Dwoo_Template_String('{"x`$var|replace:array(\'foo\', bar):array(\"\")`y"}');
        $tpl->forceCompilation();

        $this->assertEquals("xabcy", $this->dwoo->get($tpl, array('var'=>'afoobbarc')));
    }

    public function testStringModifierInOtherCall()
    {
        $tpl = new Dwoo_Template_String('{cat "f o o"|replace:" ":"" "xx"}');
        $tpl->forceCompilation();

        $this->assertEquals("fooxx", $this->dwoo->get($tpl, array()));
    }

    public function testPhpTagWithoutSemicolon()
    {
        $tpl = new Dwoo_Template_String('{capture "foo"}<?php $var=3; echo $var ?>{/capture}-{$.capture.foo}');
        $tpl->forceCompilation();

        $this->assertEquals("-3", $this->dwoo->get($tpl, array()));
    }

    public function testUppercasePlugin()
    {
        $tpl = new Dwoo_Template_String('{X foo}');
        $tpl->forceCompilation();

        $this->assertEquals("foo", $this->dwoo->get($tpl, array()));
    }

    public function testMultilineAssignments()
    {
        $tpl = new Dwoo_Template_String('{$foo = array(
moo=bar
foo=baz
)}{foreach $foo k v}{$k; $v}.{/foreach}');
        $tpl->forceCompilation();

        $this->assertEquals("moobar.foobaz.", $this->dwoo->get($tpl, array()));
    }

    public function testAssignmentsWithAutoEscape()
    {
    	$cmp = new Dwoo_Compiler();
    	$cmp->setAutoEscape(true);
        $tpl = new Dwoo_Template_String('{$foo = $bar}>{$foo}');
        $tpl->forceCompilation();

        $this->assertEquals(">moo", $this->dwoo->get($tpl, array('bar'=>'moo'), $cmp));
    }

    public function testAndOrOperatorsFollowedWithRoundBrackets()
    {
        $tpl = new Dwoo_Template_String('{if 1 AND (0 OR 1)}true{/if}');
        $tpl->forceCompilation();

        $this->assertEquals("true", $this->dwoo->get($tpl, array()));
    }

    public function testMultipleVarsWithStringKey()
    {
        $tpl = new Dwoo_Template_String('{$foo["bar"]}{$foo["baz"]}');
        $tpl->forceCompilation();

        $this->assertEquals("12", $this->dwoo->get($tpl, array('foo'=>array('bar'=>1, 'baz'=>2))));
    }

    public function testTopCommentParsingWithWhitespaceAtTheEnd()
    {
        $tpl = new Dwoo_Template_String('{* Foo *}
aaa
 ');
        $tpl->forceCompilation();

        $this->assertEquals('aaa
 ', $this->dwoo->get($tpl, array()));
    }

    public function testTopCommentParsingWithWhitespaceAtTheEndAndBeginning()
    {
        $tpl = new Dwoo_Template_String(' {* Foo *}
aaa
 ');
        $tpl->forceCompilation();

        $this->assertEquals(" \naaa\n ", $this->dwoo->get($tpl, array()));
    }

    public function testNestedDynamicTags()
    {
        $tpl = new Dwoo_Template_String('
        {dynamic}
        {dynamic}
        {foreach $foo member}
        {/foreach}
        {/dynamic}
        {/dynamic}
        ');

        $tpl->forceCompilation();

        $this->dwoo->get($tpl, array());
    }

    public function testDoubleEscapingOnAssignments()
    {
        $tpl = new Dwoo_Template_String('{$bar = $foo}{$foo}{$bar}');
        $tpl->forceCompilation();
        $cmp = new Dwoo_Compiler();
        $cmp->setAutoEscape(true);

        $this->assertEquals('a&#039;ba&#039;b', $this->dwoo->get($tpl, array('foo' => "a'b"), $cmp));
    }
}

function Dwoo_Plugin_X_compile(Dwoo_Compiler $cmp, $text)
{
	return $text;
}