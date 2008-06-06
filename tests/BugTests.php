<?php

require_once 'Dwoo/Compiler.php';

class BugTests extends PHPUnit_Framework_TestCase
{
    protected $compiler;
    protected $dwoo;

    public function __construct()
    {
        $this->compiler = new Dwoo_Compiler();
        $this->dwoo = new Dwoo();
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
}
