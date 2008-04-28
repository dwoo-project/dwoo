<?php

require_once 'Dwoo/Compiler.php';

class DataTests extends PHPUnit_Framework_TestCase
{
	protected $compiler;
	protected $dwoo;

	public function __construct()
	{
		// extend this class and override this in your constructor to test a modded compiler
		$this->compiler = new DwooCompiler();
		$this->dwoo = new Dwoo();
		$this->tpl = new DwooTemplateString('{$var}{$var2}{$var3}{$var4}');
		$this->tpl->forceCompilation();
	}

	public function testSetMergeAndClear()
	{
		$data = new DwooData();

		$data->setData(array('foo'));
		$this->assertEquals(array('foo'), $data->getData());

		$data->mergeData(array('baz'),array('bar', 'boo'=>'moo'));

		$this->assertEquals(array('foo', 'baz', 'bar', 'boo'=>'moo'), $data->getData());

		$data->clear();
		$this->assertEquals(array(), $data->getData());
	}

	public function testAssign()
	{
		$data = new DwooData();

		$data->assign('var', '1');
		$data->assign(array('var2'=>'1', 'var3'=>1));
		$ref = 0;
		$data->assignByRef('var4', $ref);
		$ref = 1;

		$this->assertEquals('1111', $this->dwoo->get($this->tpl, $data, $this->compiler));
	}

	public function testClear()
	{
		$data = new DwooData();

		$data->assign(array('var2'=>'1', 'var3'=>1, 'var4'=>5));
		$data->clear(array('var2', 'var4'));

		$this->assertEquals(array('var3'=>1), $data->getData());

		$data->assign('foo', 'moo');
		$data->clear('var3');

		$this->assertEquals(array('foo'=>'moo'), $data->getData());
	}

	public function testAppend()
	{
		$data = new DwooData();

		$data->assign('var', 'val');
		$data->append('var', 'moo');

		$this->assertEquals(array('var'=>array('val','moo')), $data->getData());

		$data->assign('var', 'val');
		$data->append(array('var'=>'moo', 'var2'=>'bar'));
		$this->assertEquals(array('var'=>array('val','moo'), 'var2'=>array('bar')), $data->getData());
	}
}

?>