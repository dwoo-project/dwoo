<?php

require_once 'Dwoo/Compiler.php';

class BugTests extends PHPUnit_Framework_TestCase
{
    protected $compiler;
    protected $dwoo;

    public function __construct()
    {
        $this->compiler = new Dwoo_Compiler();
        $this->dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
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
}
