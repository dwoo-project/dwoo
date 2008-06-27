<?php

require_once 'Dwoo/Compiler.php';

class FiltersTests extends PHPUnit_Framework_TestCase
{
	protected $compiler;
	protected $dwoo;

	public function __construct()
	{
		// extend this class and override this in your constructor to test a modded compiler
		$this->compiler = new Dwoo_Compiler();
		$this->dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
	}

	public function testHtmlFormat()
	{
		$tpl = new Dwoo_Template_String("<html><body><div><p>a<em>b</em>c<hr /></p><textarea>a\n  b</textarea></div></body><html>");
		$tpl->forceCompilation();

		$dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
		$dwoo->addFilter('html_format', true);

		$this->assertEquals(str_replace("\r", '', <<<SNIPPET

<html>
<body>
	<div>
		<p>
			a<em>b</em>c
			<hr />
		</p><textarea>a
  b</textarea>
	</div>
</body>
<html>
SNIPPET
), $dwoo->get($tpl, array(), $this->compiler));
	}

	public function testSmartyCompat()
	{
		$tpl = new Dwoo_Template_String('{ldelim}{$smarty.version}{rdelim}');
		$tpl->forceCompilation();
		$cmp = new Dwoo_Compiler();
		$cmp->addPreProcessor('smarty_compat', true);

		$this->assertEquals('{'.Dwoo::VERSION.'}', $this->dwoo->get($tpl, array(), $cmp));
	}
}
