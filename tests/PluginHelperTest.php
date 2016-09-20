<?php
/**
 */

namespace Dwoo\Tests
{

    use Dwoo\Template\String as TemplateString;

    class PluginHelperTests extends BaseTests
    {
        public function tearDown()
        {
            unset($this->compiler, $this->dwoo);
        }

        public function testArrayFunctionPluginCompile()
        {
            $tpl = new TemplateString('{array(a, b, c)}');
            $tpl->forceCompilation();

            var_dump($this->dwoo->get($tpl));
        }

    }
}