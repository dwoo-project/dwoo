<?php

require_once 'Dwoo/Compiler.php';

class HelperTests extends PHPUnit_Framework_TestCase
{
	protected $compiler;
	protected $dwoo;

	public function __construct()
	{
		// extend this class and override this in your constructor to test a modded compiler
		$this->compiler = new DwooCompiler();
		$this->dwoo = new Dwoo();
	}

	public function testArray()
	{
		$tpl = new DwooTemplateString('{if array(3,foo, "bar",$baz|upper) === $test}true{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('true', $this->dwoo->get($tpl, array('test'=>array(3, "foo", "bar", "BAZ"), 'baz'=>'baz'), $this->compiler));
	}

	public function testAssociativeArray()
	{
		$tpl = new DwooTemplateString('{if array(hoy=3,5="foo",bar=moo) === $test}true{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('true', $this->dwoo->get($tpl, array('test'=>array("hoy"=>3, 5=>"foo", "bar"=>"moo"), 'baz'=>'baz'), $this->compiler));
	}
}

?>