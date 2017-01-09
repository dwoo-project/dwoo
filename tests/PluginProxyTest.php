<?php

namespace Dwoo\Tests
{

    use Dwoo\Template\Str as TemplateString;
    use PluginProxyTest_PluginProxy;

    /**
     * Class PluginProxyTest
     *
     * @package Dwoo\Tests
     */
    class PluginProxyTest extends BaseTests
    {

        public function tearDown()
        {
            parent::tearDown();
            unset($this->compiler, $this->dwoo);
        }

        public function testPlainNestedProxyCall()
        {
            $this->dwoo->setPluginProxy(new PluginProxyTest_PluginProxy());

            // test simple assign
            $tpl = new TemplateString('{F1_Stub(F2_Stub())}');
            $tpl->forceCompilation();

            $this->assertContains('PluginProxyTest_F1_Stub(PluginProxyTest_F2_Stub', $this->compiler->compile($this->dwoo, $tpl));
        }

        public function testAdvNestedProxyCall()
        {
            $this->dwoo->setPluginProxy(new PluginProxyTest_PluginProxy());

            // test simple assign
            $tpl = new TemplateString('{assign F1_Stub(F2_Stub(\'/public/css/global.css\'))->something(F3_Stub(\'/public/css/global.css\')) styles}');
            $tpl->forceCompilation();

            $this->assertContains('PluginProxyTest_F3_Stub(', $this->compiler->compile($this->dwoo, $tpl));
        }
    }
}

namespace
{

    class PluginProxyTest_PluginProxy implements Dwoo\IPluginProxy
    {
        public function handles($name)
        {
            return 'F' == substr($name, 0, 1);
        }

        public function getCode($name, $params)
        {
            return 'PluginProxyTest_' . $name . '(' . Dwoo\Compiler::implode_r($params) . ')';
        }

        public function getCallback($name)
        {
            return 'PluginProxyTest_' . $name;
        }

        public function getLoader($name)
        {
            return '';
        }
    }

    function PluginProxyTest_F1_Stub()
    {
        return new PluginProxyTest_C1_Stub();
    }

    function PluginProxyTest_F2_Stub($in = '')
    {
        return $in . '#1';
    }

    function PluginProxyTest_F3_Stub($in = '')
    {
        return $in . '#1';
    }

    class PluginProxyTest_C1_Stub
    {
        public function something($in)
        {
            return $in . '#2';
        }
    }
}