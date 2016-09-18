<?php
/**
 */

class PluginHelperTests extends PHPUnit_Framework_TestCase
{
    protected $compiler;
    protected $dwoo;

    public function __construct()
    {
        $this->compiler = new Dwoo\Compiler();
        $this->dwoo     = new Dwoo\Core(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
    }

    public function tearDown()
    {
        unset($this->compiler, $this->dwoo);
    }

    public function testArrayFunctionPluginCompile()
    {
//        $tpl = new Dwoo\Template\String('{array(a, b, c)}');
//        $tpl->forceCompilation();
//
//        var_dump($this->dwoo->get($tpl));
    }

}

