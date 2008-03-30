<?php

require_once dirname(dirname(__FILE__)).'/DwooCompiler.php';

class DataTests extends PHPUnit_Framework_TestCase
{
	protected $compiler;
	protected $dwoo;

	public function __construct()
	{
		// extend this class and override this in your constructor to test a modded compiler
		$this->compiler = new DwooCompiler();
		$this->dwoo = new Dwoo();
	}

	public function testDwooData()
	{
		// test simple assign
		$tpl = new DwooTemplateString('{$var}{$var2}{$var3}{$var4}');
		$tpl->forceCompilation();

		$data = new DwooData();

		$data->setData(array('foo'));
		$this->assertEquals(array('foo'), $data->getData());

		$data->mergeData(array('baz'),array('bar', 'boo'=>'moo'));

		$this->assertEquals(array('foo', 'baz', 'bar', 'boo'=>'moo'), $data->getData());

		$data->clear();
		$this->assertEquals(array(), $data->getData());

		$data->assign('var', '1');
		$data->assign(array('var2'=>'1', 'var3'=>1));
		$ref = 0;
		$data->assignByRef('var4', $ref);
		$ref = 1;

		$this->assertEquals('1111', $this->dwoo->get($tpl, $data, $this->compiler));
	}
}

?>