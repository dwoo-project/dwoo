<?php

require_once DWOO_DIRECTORY . 'Dwoo/Compiler.php';

class HelperTests extends PHPUnit_Framework_TestCase
{
	protected $compiler;
	protected $dwoo;

	public function __construct()
	{
		// extend this class and override this in your constructor to test a modded compiler
		$this->compiler = new Dwoo_Compiler();
		$this->dwoo = new Dwoo_Core(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
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

		$this->assertEquals('true', $this->dwoo->get($tpl, array('test'=>array("hoy"=>3, 5=>"foo", "bar"=>"moo")), $this->compiler));
	}

	public function testAssociativeArray2()
	{
		$tpl = new Dwoo_Template_String('{if array(hoy=3,5=array(
															"foo"
															frack
															18
															) bar=moo) === $test}true{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('true', $this->dwoo->get($tpl, array('test'=>array("hoy"=>3, 5=>array("foo", "frack", 18), "bar"=>"moo")), $this->compiler));
	}

	public function testNumericKeysDontOverlap()
	{
		$tpl = new Dwoo_Template_String('{if array(1=2 2=3 1=4) === $test}true{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('true', $this->dwoo->get($tpl, array('test'=>array(1=>4, 2=>3)), $this->compiler));
	}

	public function testAssociativeArrayPhpStyle()
	{
		$tpl = new Dwoo_Template_String('{if array("hoy"=>3,5="foo",\'bar\'=moo) === $test}true{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('true', $this->dwoo->get($tpl, array('test'=>array("hoy"=>3, 5=>"foo", "bar"=>"moo"), 'baz'=>'baz'), $this->compiler));
	}

	public function testAssociativeArrayWithVarAsKey()
	{
		$tpl = new Dwoo_Template_String('{$var="hoy"}{if array($var=>hey) === $test}true{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('true', $this->dwoo->get($tpl, array('test'=>array("hoy"=>'hey')), $this->compiler));
	}

	public function testAssociativeArrayWithMixedOrderDefinedKeys()
	{
		$tpl = new Dwoo_Template_String('{if array(5="foo", 3=moo) === $test}true{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('true', $this->dwoo->get($tpl, array('test'=>array(5=>"foo", 3=>"moo")), $this->compiler));
	}
}
