<?php

require_once 'Dwoo/Compiler.php';

class HelperTests extends PHPUnit_Framework_TestCase
{
	protected $compiler;
	protected $dwoo;

	public function __construct()
	{
		// extend this class and override this in your constructor to test a modded compiler
		$this->compiler = new Dwoo_Compiler();
		$this->dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
	}

	public function testArray()
	{
		$tpl = new Dwoo_Template_String('{if array(3,foo, "bar",$baz|upper) === $test}true{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('true', $this->dwoo->get($tpl, array('test'=>array(3, "foo", "bar", "BAZ"), 'baz'=>'baz'), $this->compiler));
	}

	public function testAssociativeArray()
	{
		$tpl = new Dwoo_Template_String('{if array(hoy=3,5="foo",bar=moo) === $test}true{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('true', $this->dwoo->get($tpl, array('test'=>array("hoy"=>3, 5=>"foo", "bar"=>"moo"), 'baz'=>'baz'), $this->compiler));

		$tpl = new Dwoo_Template_String('{if array(hoy=3,5=array(
															"foo"
															frack
															18
															) bar=moo) === $test}true{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('true', $this->dwoo->get($tpl, array('test'=>array("hoy"=>3, 5=>array("foo", "frack", 18), "bar"=>"moo"), 'baz'=>'baz'), $this->compiler));
	}
}
