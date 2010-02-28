<?php

require_once DWOO_DIRECTORY . 'Dwoo/Compiler.php';

class DataTests extends PHPUnit_Framework_TestCase
{
	protected $compiler;
	protected $dwoo;

	public function __construct()
	{
		// extend this class and override this in your constructor to test a modded compiler
		$this->compiler = new Dwoo_Compiler();
		$this->dwoo = new Dwoo_Core(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
		$this->tpl = new Dwoo_Template_String('{$var}{$var2}{$var3}{$var4}');
		$this->tpl->forceCompilation();
	}

	public function testSetMergeAndClear()
	{
		$data = new Dwoo_Data();

		$data->setData(array('foo'));
		$this->assertEquals(array('foo'), $data->getData());

		$data->mergeData(array('baz'),array('bar', 'boo'=>'moo'));

		$this->assertEquals(array('foo', 'baz', 'bar', 'boo'=>'moo'), $data->getData());

		$data->clear();
		$this->assertEquals(array(), $data->getData());
	}

	public function testAssign()
	{
		$data = new Dwoo_Data();

		$data->assign('var', '1');
		$data->assign(array('var2'=>'1', 'var3'=>1));
		$ref = 0;
		$data->assignByRef('var4', $ref);
		$ref = 1;

		$this->assertEquals('1111', $this->dwoo->get($this->tpl, $data, $this->compiler));
	}

	public function testClear()
	{
		$data = new Dwoo_Data();

		$data->assign(array('var2'=>'1', 'var3'=>1, 'var4'=>5));
		$data->clear(array('var2', 'var4'));

		$this->assertEquals(array('var3'=>1), $data->getData());

		$data->assign('foo', 'moo');
		$data->clear('var3');

		$this->assertEquals(array('foo'=>'moo'), $data->getData());
	}

	public function testAppend()
	{
		$data = new Dwoo_Data();

		$data->assign('var', 'val');
		$data->append('var', 'moo');

		$this->assertEquals(array('var'=>array('val','moo')), $data->getData());

		$data->assign('var', 'val');
		$data->append(array('var'=>'moo', 'var2'=>'bar'));
		$this->assertEquals(array('var'=>array('val','moo'), 'var2'=>array('bar')), $data->getData());
	}

	public function testMagicGetSetStuff()
	{
		$data = new Dwoo_Data();

		$data->variable = 'val';
		$data->append('variable', 'moo');

		$this->assertEquals(array('val','moo'), $data->get('variable'));
		$this->assertEquals(array('val','moo'), $data->variable);
		$this->assertEquals(true, $data->isAssigned('variable'));
	}

	/**
	 * @expectedException Dwoo_Exception
	 */
	public function testUnset()
	{
		$data = new Dwoo_Data();
		$data->variable = 'val';
		$this->assertEquals(true, isset($data->variable));
		unset($data->variable);
		$data->get('variable');
	}

	/**
	 * @expectedException Dwoo_Exception
	 */
	public function testUnassign()
	{
		$data = new Dwoo_Data();
		$data->variable = 'val';
		$this->assertEquals(true, isset($data->variable));
		$data->unassign('variable');
		$data->get('variable');
	}
}
