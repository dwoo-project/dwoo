<?php

require_once dirname(dirname(__FILE__)).'/DwooCompiler.php';

class CoreTests extends PHPUnit_Framework_TestCase
{
	protected $compiler;
	protected $dwoo;

	public function __construct()
	{
		$this->compiler = new DwooCompiler();
		$this->dwoo = new Dwoo();
	}

    public function testCoverConstructorsEtc()
    {
		// extend this class and override this in your constructor to test a modded compiler
		$this->compiler = new DwooCompiler();
		$this->dwoo = new Dwoo();
		DwooLoader::rebuildClassPathCache(DWOO_PATH.'plugins', DWOO_PATH.'cache');
    }

    public function testReadVar()
    {
    	$tpl = new DwooTemplateString('{$foo.$bar[$baz->qux][moo]}{with $foo}{$a.b.moo}{/with}{$baz->qux}');
		$tpl->forceCompilation();

		$Obj = new stdClass;
		$Obj->qux = 'b';
		$data = array(
			'foo'=>array('a' => array('b'=>array('moo'=>'Yay!'))),
			'bar'=>'a',
			'baz'=>$Obj,
		);
		$this->assertEquals("Yay!Yay!b", $this->dwoo->get($tpl, $data, $this->compiler));

		$this->assertEquals('Yay!aaab', $this->dwoo->readVar('foo.a.b.moo') . $this->dwoo->readVar('bar')  . $this->dwoo->readVar('_root.bar')  . $this->dwoo->readVar('_parent.bar')  . $this->dwoo->readVar('baz->qux'));
		$this->assertEquals($data, $this->dwoo->readVar('_root'));
		$this->assertEquals($data, $this->dwoo->readVar('_parent'));

    }

    public function testReadParentVar()
    {
    	$tpl = new DwooTemplateString('{assign "Yay!" a.b->qux}{$a.b->qux}');
		$tpl->forceCompilation();

		$this->assertEquals("Yay!", $this->dwoo->get($tpl, array('bar'=>'a'), $this->compiler));

		$this->assertEquals('a', $this->dwoo->readParentVar(2, 'bar'));
    }

    public function testAssignVarInScope()
    {
    	$tpl = new DwooTemplateString('{assign "Yay!" a.b->qux}{$a.b->qux}');
		$tpl->forceCompilation();

		$Obj = new stdClass;
		$Obj->qux = 'Noes:(';

		$this->assertEquals("Yay!", $this->dwoo->get($tpl, array('a'=>array('b'=>$Obj)), $this->compiler));

    	$tpl = new DwooTemplateString('{assign "Yay!" a->b.qux}{$a->b.qux}');
		$tpl->forceCompilation();

		$Obj = new stdClass;
		$Obj->b = array('qux'=>'Noes:(');

		$this->assertEquals("Yay!", $this->dwoo->get($tpl, array('a'=>$Obj), $this->compiler));
    }

    public function testPhpCall()
    {
    	$tpl = new DwooTemplateString('{"foo"|strtoupper}');
		$tpl->forceCompilation();

		$this->assertEquals("FOO", $this->dwoo->get($tpl, array(), $this->compiler));

    	$tpl = new DwooTemplateString('{foreach $foo|count p}{$p}{/foreach}');
		$tpl->forceCompilation();

		$this->assertEquals("21", $this->dwoo->get($tpl, array('foo'=>array('a'=>array(1,2), 'b'=>array(2))), $this->compiler));
    }

    public function testClassCall()
    {
    	$tpl = new DwooTemplateString('{dump $foo.b.0}');
		$tpl->forceCompilation();

		$this->assertEquals("2<br />", $this->dwoo->get($tpl, array('foo'=>array('a'=>array(1,2), 'b'=>array(2))), $this->compiler));
    }

	public function testSuperGlobals()
	{
		$_GET[5] = 'Yay';
    	$tpl = new DwooTemplateString('{$dwoo.get.5} {$dwoo.get.$foo}');
		$tpl->forceCompilation();

		$this->assertEquals("Yay Yay", $this->dwoo->get($tpl, array('foo'=>5), $this->compiler));
	}

	public function testGettersSetters()
	{
    	$this->dwoo->setCacheTime(5);
		$this->assertEquals(5, $this->dwoo->getCacheTime());
    	$this->dwoo->setCacheTime(0);

    	$this->dwoo->setCharset('foo');
		$this->assertEquals('foo', $this->dwoo->getCharset());
    	$this->dwoo->setCharset('utf-8');

    	$this->dwoo->setDefaultCompilerFactory('file', 'Moo');
		$this->assertEquals('Moo', $this->dwoo->getDefaultCompilerFactory('file'));
    	$this->dwoo->setDefaultCompilerFactory('file', array('DwooCompiler', 'compilerFactory'));

	}

	public function testAddAndRemoveResource()
	{
    	$this->dwoo->addResource('news', 'DwooTemplateFile', array('DwooCompiler', 'compilerFactory'));
    	$tpl = new DwooTemplateString('{include file="news:'.DWOO_PATH.'tests/resources/test.html" foo=3 bar=4}');
		$tpl->forceCompilation();
		$this->assertEquals("34", $this->dwoo->get($tpl, array()));
		$this->dwoo->removeResource('news');

    	$this->dwoo->addResource('file', 'DwooTemplateString', 'Fake');
		$this->dwoo->removeResource('file');
    	$tpl = new DwooTemplateString('{include file="'.DWOO_PATH.'tests/resources/test.html" foo=3 bar=4}');
		$tpl->forceCompilation();
		$this->assertEquals("34", $this->dwoo->get($tpl, array()));
	}

	public function testTemplateFile()
	{
    	$tpl = new DwooTemplateFile(DWOO_PATH.'tests/resources/test.html');
		$tpl->forceCompilation();

		$this->assertEquals("12", $this->dwoo->get($tpl, array('foo'=>1, 'bar'=>2)));
	}

	public function testTemplateString()
	{
    	$tpl = new DwooTemplateString('foo', 13);

		$this->assertEquals("13", $tpl->getCacheTime());
		$this->assertEquals(null, $tpl->getCompiler());
		$this->assertEquals(false, DwooTemplateString::templateFactory($this->dwoo, 'foo', 5));
	}
}

?>