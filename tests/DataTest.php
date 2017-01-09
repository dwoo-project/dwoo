<?php

namespace Dwoo\Tests
{

    use Dwoo\Data;
    use Dwoo\Template\Str as TemplateString;

    /**
     * Class DataTest
     *
     * @package Dwoo\Tests
     */
    class DataTest extends BaseTests
    {
        protected $tpl;

        public function __construct($name = null, array $data = array(), $dataName = '')
        {
            parent::__construct($name, $data, $dataName);

            $this->tpl = new TemplateString('{$var}{$var2}{$var3}{$var4}');
            $this->tpl->forceCompilation();
        }

        public function testSetMergeAndClear()
        {
            $data = new Data();

            $data->setData(array('foo'));
            $this->assertEquals(array('foo'), $data->getData());

            $data->mergeData(array('baz'), array('bar', 'boo' => 'moo'));

            $this->assertEquals(array('foo', 'baz', 'bar', 'boo' => 'moo'), $data->getData());

            $data->clear();
            $this->assertEquals(array(), $data->getData());
        }

        public function testAssign()
        {
            $data = new Data();

            $data->assign('var', '1');
            $data->assign(array('var2' => '1', 'var3' => 1));
            $ref = 0;
            $data->assignByRef('var4', $ref);
            $ref = 1;

            $this->assertEquals('1111', $this->dwoo->get($this->tpl, $data, $this->compiler));
        }

        public function testClear()
        {
            $data = new Data();

            $data->assign(array('var2' => '1', 'var3' => 1, 'var4' => 5));
            $data->clear(array('var2', 'var4'));

            $this->assertEquals(array('var3' => 1), $data->getData());

            $data->assign('foo', 'moo');
            $data->clear('var3');

            $this->assertEquals(array('foo' => 'moo'), $data->getData());
        }

        public function testAppend()
        {
            $data = new Data();

            $data->assign('var', 'val');
            $data->append('var', 'moo');

            $this->assertEquals(array('var' => array('val', 'moo')), $data->getData());

            $data->assign('var', 'val');
            $data->append(array('var' => 'moo', 'var2' => 'bar'));
            $this->assertEquals(array('var' => array('val', 'moo'), 'var2' => array('bar')), $data->getData());
        }

        public function testMagicGetSetStuff()
        {
            $data = new Data();

            $data->variable = 'val';
            $data->append('variable', 'moo');

            $this->assertEquals(array('val', 'moo'), $data->get('variable'));
            $this->assertEquals(array('val', 'moo'), $data->variable);
            $this->assertEquals(true, $data->isAssigned('variable'));
        }

        /**
         * @expectedException \Dwoo\Exception
         */
        public function testUnset()
        {
            $data           = new Data();
            $data->variable = 'val';
            $this->assertEquals(true, isset($data->variable));
            unset($data->variable);
            $data->get('variable');
        }

        /**
         * @expectedException \Dwoo\Exception
         */
        public function testUnassign()
        {
            $data           = new Data();
            $data->variable = 'val';
            $this->assertEquals(true, isset($data->variable));
            $data->unassign('variable');
            $data->get('variable');
        }
    }
}