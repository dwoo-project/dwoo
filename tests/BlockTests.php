<?php

require_once dirname(dirname(__FILE__)).'/DwooCompiler.php';

class BlockTests extends PHPUnit_Framework_TestCase
{
	protected $compiler;
	protected $dwoo;

	public function __construct()
	{
		// extend this class and override this in your constructor to test a modded compiler
		$this->compiler = new DwooCompiler();
		$this->dwoo = new Dwoo();
	}

    public function testCapture()
    {
		$tpl = new DwooTemplateString('{capture name="foo" assign="foo"}BAR{/capture}{$dwoo.capture.foo}-{$foo}');
		$tpl->forceCompilation();

        $this->assertEquals('BAR-BAR', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new DwooTemplateString('{capture "foo" "foo" true}BAR{/capture}{capture "foo" "foo" true}BAR{/capture}{$foo}');
		$tpl->forceCompilation();

        $this->assertEquals('BARBAR', $this->dwoo->get($tpl, array(), $this->compiler));

    	// fixes the init call not being called (which is normal)
    	$fixCall = new DwooPlugin_capture($this->dwoo);
    	$fixCall->init('');
    }

    public function testIf()
    {
		$tpl = new DwooTemplateString('{if "BAR"==reverse($foo|reverse|upper)}true{/if}');
		$tpl->forceCompilation();

        $this->assertEquals('true', $this->dwoo->get($tpl, array('foo'=>'bar'), $this->compiler));

		$tpl = new DwooTemplateString('{if 4/2==2 && 2!=1 && 3>0 && 4<5 && 5<=5 && 6>=3 && 3===3 && "3"!==3}true{/if}');
		$tpl->forceCompilation();

        $this->assertEquals('true', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new DwooTemplateString('{if 5%2==1 && !isset($foo)}true{/if}');
		$tpl->forceCompilation();

        $this->assertEquals('true', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new DwooTemplateString('{if 5 is not div by 2 && 4 is div by 2 && 6 is even && 6 is not even by 5 && (3 is odd && 9 is odd by 3)}true{/if}');
		$tpl->forceCompilation();

        $this->assertEquals('true', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new DwooTemplateString('{if (3==4 && 5==5) || 3==3}true{/if}');
		$tpl->forceCompilation();

        $this->assertEquals('true', $this->dwoo->get($tpl, array(), $this->compiler));

    	// fixes the init call not being called (which is normal)
    	$fixCall = new DwooPlugin_if($this->dwoo);
    	$fixCall->init(array());
    }

    public function testIfElseif()
    {
		$tpl = new DwooTemplateString('{if "BAR" == "bar"}true{elseif "BAR"=="BAR"}false{/if}');
		$tpl->forceCompilation();

        $this->assertEquals('false', $this->dwoo->get($tpl, array(), $this->compiler));

    	// fixes the init call not being called (which is normal)
    	$fixCall = new DwooPlugin_elseif($this->dwoo);
    	$fixCall->init(array());
    }

    public function testIfElse()
    {
		$tpl = new DwooTemplateString('{if "BAR" == "bar"}true{else}false{/if}');
		$tpl->forceCompilation();

        $this->assertEquals('false', $this->dwoo->get($tpl, array(), $this->compiler));

    	// fixes the init call not being called (which is normal)
    	$fixCall = new DwooPlugin_else($this->dwoo);
    	$fixCall->init();
    }

    public function testIfElseImplicitUnset()
    {
		$tpl = new DwooTemplateString('{if $moo}true{else}false{/if}');
		$tpl->forceCompilation();

        $this->assertEquals('false', $this->dwoo->get($tpl, array(), $this->compiler));
    }

    public function testIfElseImplicitTrue()
    {
		$tpl = new DwooTemplateString('{if $moo}true{else}false{/if}');
		$tpl->forceCompilation();

        $this->assertEquals('true', $this->dwoo->get($tpl, array('moo'=>'i'), $this->compiler));
    }

    public function testFor()
    {
		$tpl = new DwooTemplateString('{for name=i from=$sub}{$i}.{$sub[$i]}{/for}');
		$tpl->forceCompilation();

        $this->assertEquals('0.foo1.bar2.baz3.qux', $this->dwoo->get($tpl, array('sub'=>array('foo','bar','baz','qux')), $this->compiler));

    	// fixes the init call not being called (which is normal)
    	$fixCall = new DwooPlugin_for($this->dwoo);
    	$fixCall->init(null,null);
    }

    public function testForElse()
    {
		$tpl = new DwooTemplateString('{for name=i from=0 to=0}{$i}{else}Narp!{/for}');
		$tpl->forceCompilation();

        $this->assertEquals('Narp!', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new DwooTemplateString('{for name=i from=0 to=0}{$i}{forelse}Narp!{/for}');
		$tpl->forceCompilation();

        $this->assertEquals('Narp!', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new DwooTemplateString('{for name=i from=0 to=10 step=3}{$i}{else}Narp!{/for}');
		$tpl->forceCompilation();

        $this->assertEquals('0369', $this->dwoo->get($tpl, array(), $this->compiler));

    	// fixes the init call not being called (which is normal)
    	$fixCall = new DwooPlugin_forelse($this->dwoo);
    	$fixCall->init(null,null);
    }

    public function testForeachSmarty()
    {
		$tpl = new DwooTemplateString('{foreach from=$sub key=key item=item}{$key}.{$item}{/foreach}');
		$tpl->forceCompilation();

        $this->assertEquals('0.foo1.bar', $this->dwoo->get($tpl, array('sub'=>array('foo','bar')), $this->compiler));

    	// fixes the init call not being called (which is normal)
    	$fixCall = new DwooPlugin_foreach($this->dwoo);
    	$fixCall->init('');
    }

    public function testForeachSmartyAlt()
    {
		$tpl = new DwooTemplateString('{foreach from=$sub key=key item=item}{$key}.{$item}{/foreach}');
		$tpl->forceCompilation();

        $this->assertEquals('0.foo1.bar', $this->dwoo->get($tpl, array('sub'=>array('foo','bar')), $this->compiler));
    }

    public function testForeachDwoo()
    {
		$tpl = new DwooTemplateString('{foreach $sub key item}{$key}.{$item}{/foreach}');
		$tpl->forceCompilation();

        $this->assertEquals('0.foo1.bar', $this->dwoo->get($tpl, array('sub'=>array('foo','bar')), $this->compiler));
    }

    public function testForeachWithGlobalVars()
    {
		$tpl = new DwooTemplateString('{foreach $sub key item foo}{if $dwoo.foreach.foo.first}F{elseif $dwoo.foreach.foo.last}L{/if}{/foreach}');
		$tpl->forceCompilation();

        $this->assertEquals('FL', $this->dwoo->get($tpl, array('sub'=>array('foo','bar')), $this->compiler));
    }

    public function testForeachWithGlobalVarsPreceding()
    {
		$tpl = new DwooTemplateString('{$dwoo.foreach.foo.total}{foreach $sub key item foo}{/foreach}');
		$tpl->forceCompilation();

        $this->assertEquals('', $this->dwoo->get($tpl, array('sub'=>array('foo','bar')), $this->compiler));
    }

    public function testForeachWithGlobalVarsFollowing()
    {
		$tpl = new DwooTemplateString('{foreach $sub key item foo}{/foreach}{$dwoo.foreach.foo.total}');
		$tpl->forceCompilation();

        $this->assertEquals('2', $this->dwoo->get($tpl, array('sub'=>array('foo','bar')), $this->compiler));
    }

    public function testForeachDwooAlt()
    {
		$tpl = new DwooTemplateString('{foreach $sub key item}{$key}.{$item}{/foreach}');
		$tpl->forceCompilation();

        $this->assertEquals('0.foo1.bar', $this->dwoo->get($tpl, array('sub'=>array('foo','bar')), $this->compiler));
    }

    public function testForeachElseEmpty()
    {
		$tpl = new DwooTemplateString('{foreach from=$sub key=key item=item}{$key}.{$item}{foreachelse}bar{/foreach}');
		$tpl->forceCompilation();

        $this->assertEquals('bar', $this->dwoo->get($tpl, array('sub'=>array()), $this->compiler));

    	// fixes the init call not being called (which is normal)
    	$fixCall = new DwooPlugin_foreachelse($this->dwoo);
    	$fixCall->init('');
    }

    public function testForeachElseUnset()
    {
		$tpl = new DwooTemplateString('{foreach from=$sub key=key item=item}{$key}.{$item}{foreachelse}bar{/foreach}');
		$tpl->forceCompilation();

        $this->assertEquals('bar', $this->dwoo->get($tpl, array(), $this->compiler));
    }

    public function testTextFormat()
    {
		$tpl = new DwooTemplateString('{textformat wrap=10}hello world is so unoriginal but hey.. it works.{/textformat}');
		$tpl->forceCompilation();

        $this->assertEquals('hello
world is
so
unoriginal
but hey..
it works.', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new DwooTemplateString('{textformat style=email indent=50}hello world is so unoriginal but hey.. it works.{/textformat}');
		$tpl->forceCompilation();

        $this->assertEquals('                                                  hello world is so
                                                  unoriginal but hey..
                                                  it works.', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new DwooTemplateString('{textformat style=email indent=50 assign=foo}hello world is so unoriginal but hey.. it works.{/textformat}-{$foo}');
		$tpl->forceCompilation();

        $this->assertEquals('-                                                  hello world is so
                                                  unoriginal but hey..
                                                  it works.', $this->dwoo->get($tpl, array(), $this->compiler));

		$tpl = new DwooTemplateString('{textformat style=html wrap=10}hello world{/textformat}');
		$tpl->forceCompilation();

        $this->assertEquals('hello<br />world', $this->dwoo->get($tpl, array(), $this->compiler));
    }

    public function testWith()
    {
		$tpl = new DwooTemplateString('{with $foo}{$a}{/with}-{$a}-{with $foo.b}mlsk{/with}');
		$tpl->forceCompilation();

        $this->assertEquals('bar--', $this->dwoo->get($tpl, array('foo'=>array('a'=>'bar')), $this->compiler));

		$tpl = new DwooTemplateString('{with $foo}{$a.0}{with $a}{$0}{/with}{with $b}B{else}NOB{/with}{/with}-{$a}-{with $foo.b}mlsk{/with}{with $fooo}a{withelse}b{/with}');
		$tpl->forceCompilation();

        $this->assertEquals('barbarNOB--b', $this->dwoo->get($tpl, array('foo'=>array('a'=>array('bar'))), $this->compiler));

    	// fixes the init call not being called (which is normal)
    	$fixCall = new DwooPlugin_with($this->dwoo);
    	$fixCall->init('');
    	$fixCall = new DwooPlugin_withelse($this->dwoo);
    	$fixCall->init('');
    }
}

?>