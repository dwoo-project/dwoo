<?php

namespace Dwoo\Tests
{

    use Dwoo\Template\Str as TemplateString;

    /**
     * Class HelperTest
     *
     * @package Dwoo\Tests
     */
    class HelperTest extends BaseTests
    {

        public function testArray()
        {
            $tpl = new TemplateString('{if array(3,foo, "bar",$baz|upper) === $test}true{/if}');
            $tpl->forceCompilation();

            $this->assertEquals('true', $this->dwoo->get($tpl, array(
                'test' => array(3, 'foo', 'bar', 'BAZ'),
                'baz'  => 'baz'
            ), $this->compiler));
        }

        public function testAssociativeArray()
        {
            $tpl = new TemplateString('{if array(hoy=3,5="foo",bar=moo) === $test}true{/if}');
            $tpl->forceCompilation();

            $this->assertEquals('true', $this->dwoo->get($tpl, array(
                'test' => array(
                    'hoy' => 3,
                    5     => 'foo',
                    'bar' => 'moo'
                )
            ), $this->compiler));
        }

        public function testAssociativeArray2()
        {
            $tpl = new TemplateString('{if array(hoy=3,5=array(
															"foo"
															frack
															18
															) bar=moo) === $test}true{/if}');
            $tpl->forceCompilation();

            $this->assertEquals('true', $this->dwoo->get($tpl, array(
                'test' => array(
                    'hoy' => 3,
                    5     => array('foo', 'frack', 18),
                    'bar' => 'moo'
                )
            ), $this->compiler));
        }

        public function testNumericKeysDontOverlap()
        {
            $tpl = new TemplateString('{if array(1=2 2=3 1=4) === $test}true{/if}');
            $tpl->forceCompilation();

            $this->assertEquals('true', $this->dwoo->get($tpl, array(
                'test' => array(
                    1 => 4,
                    2 => 3
                )
            ), $this->compiler));
        }

        public function testAssociativeArrayPhpStyle()
        {
            $tpl = new TemplateString('{if array("hoy"=>3,5="foo",\'bar\'=moo) === $test}true{/if}');
            $tpl->forceCompilation();

            $this->assertEquals('true', $this->dwoo->get($tpl, array(
                'test' => array(
                    'hoy' => 3,
                    5     => 'foo',
                    'bar' => 'moo'
                ),
                'baz'  => 'baz'
            ), $this->compiler));
        }

        public function testAssociativeArrayWithVarAsKey()
        {
            $tpl = new TemplateString('{$var="hoy"}{if array($var=>hey) === $test}true{/if}');
            $tpl->forceCompilation();

            $this->assertEquals('true', $this->dwoo->get($tpl, array('test' => array('hoy' => 'hey')), $this->compiler));
        }

        public function testAssociativeArrayWithMixedOrderDefinedKeys()
        {
            $tpl = new TemplateString('{if array(5="foo", 3=moo) === $test}true{/if}');
            $tpl->forceCompilation();

            $this->assertEquals('true', $this->dwoo->get($tpl, array(
                'test' => array(
                    5 => 'foo',
                    3 => 'moo'
                )
            ), $this->compiler));
        }
    }
}