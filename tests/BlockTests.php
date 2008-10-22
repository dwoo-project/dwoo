<?php

require_once 'Dwoo/Compiler.php';

class BlockTests extends PHPUnit_Framework_TestCase
{
	protected $compiler;
	protected $dwoo;

	public function __construct()
	{
		// extend this class and override this in your constructor to test a modded compiler
		$this->compiler = new Dwoo_Compiler();
		$this->dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
	}

	public function testA()
	{
		$tpl = new Dwoo_Template_String('{a "http://foo/" test="foo" bar="bar"; "Foo!" /}');
		$tpl->forceCompilation();

		$this->assertEquals('<a href="http://foo/" test="foo" bar="bar">Foo!</a>', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('{a $url test="foo" bar="bar"}');
		$tpl->forceCompilation();

		$this->assertEquals('<a href="http://foo/" test="foo" bar="bar">http://foo/</a>', $this->dwoo->get($tpl, array('url'=>'http://foo/'), $this->compiler));

		$tpl = new Dwoo_Template_String('{a $url foo="bar"; "text" /}
{a $url; "" /}
{a $url; /}
{a $url}{/}');
		$tpl->forceCompilation();

		$this->assertEquals('<a href="http://foo/" foo="bar">text</a>
<a href="http://foo/"></a>
<a href="http://foo/">http://foo/</a>
<a href="http://foo/">http://foo/</a>', $this->dwoo->get($tpl, array('url'=>'http://foo/'), $this->compiler));
	}

	public function testAutoEscape()
	{
		$cmp = new Dwoo_Compiler();
		$cmp->setAutoEscape(true);

		$tpl = new Dwoo_Template_String('{$foo}{auto_escape off}{$foo}{/}');
		$tpl->forceCompilation();

		$this->assertEquals("a&lt;b&gt;ca<b>c", $this->dwoo->get($tpl, array('foo'=>'a<b>c'), $cmp));

		$tpl = new Dwoo_Template_String('{$foo}{auto_escape true}{$foo}{/}');
		$tpl->forceCompilation();

		$this->assertEquals("a<b>ca&lt;b&gt;c", $this->dwoo->get($tpl, array('foo'=>'a<b>c')));

		// fixes the init call not being called (which is normal)
		$fixCall = new Dwoo_Plugin_auto_escape($this->dwoo);
		$fixCall->init('');
	}

	/**
	 * @expectedException Dwoo_Compilation_Exception
	 */
	public function testAutoEscapeWrongParam()
	{
		$tpl = new Dwoo_Template_String('{$foo}{auto_escape slkfjsl}{$foo}{/}');
		$tpl->forceCompilation();

		$this->dwoo->get($tpl, array('foo'=>'a<b>c'));
	}

	public function testCapture()
	{
		$tpl = new Dwoo_Template_String('{capture name="foo" assign="foo"}BAR{/capture}{$dwoo.capture.foo}-{$foo}');
		$tpl->forceCompilation();

		$this->assertEquals('BAR-BAR', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('{capture "foo" "foo" true}BAR{/capture}{capture "foo" "foo" true}BAR{/capture}{$foo}');
		$tpl->forceCompilation();

		$this->assertEquals('BARBAR', $this->dwoo->get($tpl, array(), $this->compiler));

		// fixes the init call not being called (which is normal)
		$fixCall = new Dwoo_Plugin_capture($this->dwoo);
		$fixCall->init('');
	}

	public function testDynamic()
	{
		$preTime = time();
		$tpl = new Dwoo_Template_String('{$pre}{dynamic}{$pre}{/}', 10, 'testDynamic');
		$tpl->forceCompilation();

		$this->assertEquals($preTime . $preTime, $this->dwoo->get($tpl, array('pre'=>$preTime), $this->compiler));

		sleep(1);
		$postTime = time();
		$this->assertEquals($preTime . $postTime, $this->dwoo->get($tpl, array('pre'=>$postTime), $this->compiler));

		// fixes the init call not being called (which is normal)
		$fixCall = new Dwoo_Plugin_dynamic($this->dwoo);
		$fixCall->init('');
	}

	public function testExtends()
	{
		$tpl = new Dwoo_Template_File(dirname(__FILE__).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'extend1.html');
		$tpl->forceCompilation();

		$this->assertThat($this->dwoo->get($tpl, array(), $this->compiler), new DwooConstraintStringEquals("foo
child1
toplevelContent1
bar
toplevelContent2
baz"));
	}

	public function testNonExtendedBlocksFromParent()
	{
		$tpl = new Dwoo_Template_File(dirname(__FILE__).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'toplevel.html');
		$tpl->forceCompilation();

		$this->assertThat($this->dwoo->get($tpl, array(), $this->compiler), new DwooConstraintStringEquals("foo

toplevelContent1

bar

toplevelContent2

baz"));
		// fixes the init call not being called (which is normal)
		$fixCall = new Dwoo_Plugin_block($this->dwoo);
		$fixCall->init('');
	}

	public function testExtendsMultiple()
	{
		$tpl = new Dwoo_Template_File(dirname(__FILE__).DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'extend2.html');
		$tpl->forceCompilation();

		$this->assertThat($this->dwoo->get($tpl, array('foo'=>'bar'), $this->compiler), new DwooConstraintStringEquals("foo
child1
toplevelContent1child2
bar
FOObartoplevelContent2
baz"));
	}

	public function testIf ()
	{
		$tpl = new Dwoo_Template_String('{if "BAR"==reverse($foo|reverse|upper)}true{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('true', $this->dwoo->get($tpl, array('foo'=>'bar'), $this->compiler));

		// fixes the init call not being called (which is normal)
		$fixCall = new Dwoo_Plugin_if ($this->dwoo);
		$fixCall->init(array());
	}

	public function testIfVariation2 ()
	{
		$tpl = new Dwoo_Template_String('{if 4/2==2 && 2!=1 && 3>0 && 4<5 && 5<=5 && 6>=3 && 3===3 && "3"!==3}true{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('true', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testIfVariation3 ()
	{
		$tpl = new Dwoo_Template_String('{if 5%2==1 && !isset($foo)}true{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('true', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testIfVariation4 ()
	{
		$tpl = new Dwoo_Template_String('{if 5 is not div by 2 && 4 is div by 2 && 6 is even && 6 is not even by 5 && (3 is odd && 9 is odd by 3)}true{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('true', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testIfVariation5 ()
	{
		$tpl = new Dwoo_Template_String('{if (3==4 && 5==5) || 3==3}true{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('true', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testIfElseif ()
	{
		$tpl = new Dwoo_Template_String('{if "BAR" == "bar"}true{elseif "BAR"=="BAR"}false{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('false', $this->dwoo->get($tpl, array(), $this->compiler));

		// fixes the init call not being called (which is normal)
		$fixCall = new Dwoo_Plugin_elseif ($this->dwoo);
		$fixCall->init(array());
	}

	public function testIfElse()
	{
		$tpl = new Dwoo_Template_String('{if "BAR" == "bar"}true{else}false{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('false', $this->dwoo->get($tpl, array(), $this->compiler));

		// fixes the init call not being called (which is normal)
		$fixCall = new Dwoo_Plugin_else($this->dwoo);
		$fixCall->init();
	}

	public function testIfElseifElse()
	{
		$tpl = new Dwoo_Template_String('{if "BAR" == "bar"}true{elseif 3==5}true{else}false{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('false', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testIfElseifElseifElse()
	{
		$tpl = new Dwoo_Template_String('{if "BAR" == "bar"}true{elseif 3==5}true{elseif 5==3}true{else}false{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('false', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testIfElseifElseif()
	{
		$tpl = new Dwoo_Template_String('{if "BAR" == "bar"}true{elseif 3==5}true{elseif 5==5}moo{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('moo', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testIfElseifElseifVariation2()
	{
		$tpl = new Dwoo_Template_String('{if "BAR" == "bar"}true{elseif 5==5}moo{elseif 3==5}true{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('moo', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testIfElseImplicitUnset()
	{
		$tpl = new Dwoo_Template_String('{if $moo}true{else}false{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('false', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testIfElseImplicitTrue()
	{
		$tpl = new Dwoo_Template_String('{if $moo}true{else}false{/if}');
		$tpl->forceCompilation();

		$this->assertEquals('true', $this->dwoo->get($tpl, array('moo'=>'i'), $this->compiler));
	}

	public function testFor()
	{
		$tpl = new Dwoo_Template_String('{for name=i from=$sub}{$i}.{$sub[$i]}{/for}');
		$tpl->forceCompilation();

		$this->assertEquals('0.foo1.bar2.baz3.qux', $this->dwoo->get($tpl, array('sub'=>array('foo','bar','baz','qux')), $this->compiler));

		$tpl = new Dwoo_Template_String('{for name=i from=$sub to=2}{$i}.{$sub[$i]}{/for}');
		$tpl->forceCompilation();

		$this->assertEquals('0.foo1.bar', $this->dwoo->get($tpl, array('sub'=>array('foo','bar','baz','qux')), $this->compiler));

		// fixes the init call not being called (which is normal)
		$fixCall = new Dwoo_Plugin_for ($this->dwoo);
		$fixCall->init(null,null);
	}

	public function testForVariations()
	{
		$tpl = new Dwoo_Template_String('{for i 1 1}-{$i}{/for}|{for i 1 2}-{$i}{/for}|{for i 1 3}-{$i}{/for}');
		$tpl->forceCompilation();

		$this->assertEquals('-1|-1-2|-1-2-3', $this->dwoo->get($tpl, array('sub'=>array('foo','bar','baz','qux')), $this->compiler));
	}

	public function testForElse()
	{
		$tpl = new Dwoo_Template_String('{for name=i from=array()}{$i}{else}Narp!{/for}');
		$tpl->forceCompilation();

		$this->assertEquals('Narp!', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('{for name=i from=0 to=0}{$i}{forelse}Narp!{/for}');
		$tpl->forceCompilation();

		$this->assertEquals('0', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('{for name=i from=0 to=10 step=3}{$i}{else}Narp!{/for}');
		$tpl->forceCompilation();

		$this->assertEquals('0369', $this->dwoo->get($tpl, array(), $this->compiler));

		// fixes the init call not being called (which is normal)
		$fixCall = new Dwoo_Plugin_forelse($this->dwoo);
		$fixCall->init(null,null);
	}

	public function testForeachSmarty()
	{
		$tpl = new Dwoo_Template_String('{foreach from=$sub key=key item=item}{$key}.{$item}{/foreach}');
		$tpl->forceCompilation();

		$this->assertEquals('0.foo1.bar', $this->dwoo->get($tpl, array('sub'=>array('foo','bar')), $this->compiler));

		// fixes the init call not being called (which is normal)
		$fixCall = new Dwoo_Plugin_foreach ($this->dwoo);
		$fixCall->init('');
	}

	public function testForeachSmartyAlt()
	{
		$tpl = new Dwoo_Template_String('{foreach from=$sub key=key item=item}{$key}.{$item}{/foreach}');
		$tpl->forceCompilation();

		$this->assertEquals('0.foo1.bar', $this->dwoo->get($tpl, array('sub'=>array('foo','bar')), $this->compiler));
	}

	public function testForeachDwoo()
	{
		// Item only, key arg is mapped to it just as foreach ($foo as $item)
		$tpl = new Dwoo_Template_String('{foreach $sub item}{$item}{/foreach}');
		$tpl->forceCompilation();

		$this->assertEquals('foobar', $this->dwoo->get($tpl, array('sub'=>array('foo','bar')), $this->compiler));

		// Item and key used, key is second just as foreach ($foo as $key=>$item)
		$tpl = new Dwoo_Template_String('{foreach $sub key item}{$key}.{$item}{/foreach}');
		$tpl->forceCompilation();

		$this->assertEquals('0.foo1.bar', $this->dwoo->get($tpl, array('sub'=>array('foo','bar')), $this->compiler));
	}

	public function testForeachWithGlobalVars()
	{
		$tpl = new Dwoo_Template_String('{foreach $sub key item foo}{if $dwoo.foreach.foo.first}F{elseif $dwoo.foreach.foo.last}L{/if}{/foreach}');
		$tpl->forceCompilation();

		$this->assertEquals('FL', $this->dwoo->get($tpl, array('sub'=>array('foo','bar')), $this->compiler));
	}

	public function testForeachWithGlobalVarsPreceding()
	{
		$tpl = new Dwoo_Template_String('{if isset($dwoo.foreach.foo.total)}fail{/if}{foreach $sub key item foo}{/foreach}');
		$tpl->forceCompilation();

		$this->assertEquals('', $this->dwoo->get($tpl, array('sub'=>array('foo','bar')), $this->compiler));
	}

	public function testForeachWithGlobalVarsFollowing()
	{
		$tpl = new Dwoo_Template_String('{foreach $sub key item foo}{/foreach}{$dwoo.foreach.foo.total}');
		$tpl->forceCompilation();

		$this->assertEquals('2', $this->dwoo->get($tpl, array('sub'=>array('foo','bar')), $this->compiler));
	}

	public function testForeachDwoo_Alt()
	{
		$tpl = new Dwoo_Template_String('{foreach $sub key item}{$key}.{$item}{/foreach}');
		$tpl->forceCompilation();

		$this->assertEquals('0.foo1.bar', $this->dwoo->get($tpl, array('sub'=>array('foo','bar')), $this->compiler));
	}

	public function testForeachElseEmpty()
	{
		$tpl = new Dwoo_Template_String('{foreach from=$sub key=key item=item}{$key}.{$item}{foreachelse}bar{/foreach}');
		$tpl->forceCompilation();

		$this->assertEquals('bar', $this->dwoo->get($tpl, array('sub'=>array()), $this->compiler));

		// fixes the init call not being called (which is normal)
		$fixCall = new Dwoo_Plugin_foreachelse($this->dwoo);
		$fixCall->init('');
	}

	public function testForeachElseUnset()
	{
		$tpl = new Dwoo_Template_String('{foreach from=$sub key=key item=item}{$key}.{$item}{foreachelse}bar{/foreach}');
		$tpl->forceCompilation();

		$this->assertEquals('bar', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testLoop()
	{
		$tpl = new Dwoo_Template_String('{loop $foo}{$.loop.default.index}>{$0}/{$1}{/loop}');
		$tpl->forceCompilation();

		$this->assertEquals('0>a/b1>c/d', $this->dwoo->get($tpl, array('foo'=>array(array('a','b'), array('c','d'))) , $this->compiler));

		// fixes the init call not being called (which is normal)
		$fixCall = new Dwoo_Plugin_loop($this->dwoo);
		$fixCall->init('');
	}

	public function testLoopElse()
	{
		$tpl = new Dwoo_Template_String('{loop $foo}{$.loop.default.index}>{$0}/{$1}{else}MOO{/loop}');
		$tpl->forceCompilation();

		$this->assertEquals('MOO', $this->dwoo->get($tpl, array() , $this->compiler));

		// fixes the init call not being called (which is normal)
		$fixCall = new Dwoo_Plugin_loop($this->dwoo);
		$fixCall->init('');
	}

	public function testStrip()
	{
		$tpl = new Dwoo_Template_String("{strip}a\nb\nc{/strip}a\nb\nc");
		$tpl->forceCompilation();
		$this->assertEquals("abca\nb\nc", $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testStripWithPhp()
	{
		$tpl = new Dwoo_Template_String("{strip}a\nb{\$foo=\"\\n\"}{if \$foo}>{\$foo}<{/if}\nc{/strip}a\nb\nc");
		$tpl->forceCompilation();
		$this->assertEquals("ab>\n<ca\nb\nc", $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testTextFormat()
	{
		$tpl = new Dwoo_Template_String('aa{textformat wrap=10 wrap_char="\n"}hello world is so unoriginal but hey.. {textformat wrap=4 wrap_char="\n"}a a a a a a{/textformat}it works.{/textformat}bb');
		$tpl->forceCompilation();

		$this->assertEquals('aahello
world is
so
unoriginal
but hey..
a a

a a

a ait
works.bb', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('{textformat style=email indent=50 wrap_char="\n"}hello world is so unoriginal but hey.. it works.{/textformat}');
		$tpl->forceCompilation();

		$this->assertEquals('                                                  hello world is so
                                                  unoriginal but hey..
                                                  it works.', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('{textformat style=email indent=50 assign=foo wrap_char="\n"}hello world is so unoriginal but hey.. it works.{/textformat}-{$foo}');
		$tpl->forceCompilation();

		$this->assertEquals('-                                                  hello world is so
                                                  unoriginal but hey..
                                                  it works.', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new Dwoo_Template_String('{textformat style=html wrap=10 wrap_char="\n"}hello world{/textformat}');
		$tpl->forceCompilation();

		$this->assertEquals('hello<br />world', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testWith()
	{
		$tpl = new Dwoo_Template_String('{with $foo}{$a}{/with}-{if $a}FAIL{/if}-{with $foo.b}mlsk{/with}');
		$tpl->forceCompilation();

		$this->assertEquals('bar--', $this->dwoo->get($tpl, array('foo'=>array('a'=>'bar')), $this->compiler));

		$tpl = new Dwoo_Template_String('{with $foo}{$a.0}{with $a}{$0}{/with}{with $b}B{else}NOB{/with}{/with}-{if $a}FAIL{/if}-{with $foo.b}mlsk{/with}{with $fooo}a{withelse}b{/with}');
		$tpl->forceCompilation();

		$this->assertEquals('barbarNOB--b', $this->dwoo->get($tpl, array('foo'=>array('a'=>array('bar'))), $this->compiler));

		// fixes the init call not being called (which is normal)
		$fixCall = new Dwoo_Plugin_with($this->dwoo);
		$fixCall->init('');
		$fixCall = new Dwoo_Plugin_withelse($this->dwoo);
		$fixCall->init('');
	}
}
