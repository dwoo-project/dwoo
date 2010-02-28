<?php

require_once DWOO_DIRECTORY . 'Dwoo/Compiler.php';

class PluginProxyTests extends PHPUnit_Framework_TestCase
{
    protected $compiler;
    protected $dwoo;

    public function setUp()
    {
        $this->compiler = new Dwoo_Compiler();
        $this->dwoo = new Dwoo_Core(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
    }

    public function tearDown()
    {
        unset($this->compiler, $this->dwoo);
    }

    public function testPlainNestedProxyCall()
    {
        $this->dwoo->setPluginProxy(new PluginProxyTest_PluginProxy);

        // test simple assign
        $tpl = new Dwoo_Template_String('{F1_Stub(F2_Stub())}');
        $tpl->forceCompilation();

        $this->assertContains(
            'PluginProxyTest_F1_Stub(PluginProxyTest_F2_Stub',
            $this->compiler->compile($this->dwoo, $tpl)
        );
    }

    public function testAdvNestedProxyCall()
    {
        $this->dwoo->setPluginProxy(new PluginProxyTest_PluginProxy);


        // test simple assign
        $tpl = new Dwoo_Template_String('{assign F1_Stub(F2_Stub(\'/public/css/global.css\'))->something(F3_Stub(\'/public/css/global.css\')) styles}');
        $tpl->forceCompilation();

        $this->assertContains(
            'PluginProxyTest_F3_Stub(',
            $this->compiler->compile($this->dwoo, $tpl)
        );
    }
}

class PluginProxyTest_PluginProxy implements Dwoo_IPluginProxy {
    public function handles($name) {
        return 'F' == substr($name, 0, 1);
    }

    public function getCode($name, $params) {
        return 'PluginProxyTest_'. $name .'('.Dwoo_Compiler::implode_r($params).')';
    }

    public function getCallback($name) {
        return 'PluginProxyTest_' . $name;
    }

    public function getLoader($name) {
        return '';
    }
}

function PluginProxyTest_F1_Stub() {
    return new PluginProxyTest_C1_Stub;
}

function PluginProxyTest_F2_Stub($in = '') {
    return $in . '#1';
}

function PluginProxyTest_F3_Stub($in = '') {
    return $in . '#1';
}

class PluginProxyTest_C1_Stub {
    function something($in) {
        return $in . '#2';
    }
}
