<?php

require_once dirname(dirname(__FILE__)).'/DwooCompiler.php';

class FiltersTests extends PHPUnit_Framework_TestCase
{
	protected $compiler;
	protected $dwoo;

	public function __construct()
	{
		// extend this class and override this in your constructor to test a modded compiler
		$this->compiler = new DwooCompiler();
		$this->dwoo = new Dwoo();
	}

    public function testHtmlFormat()
    {
		$tpl = new DwooTemplateString("<html><body><div><p>a<em>b</em>c<hr /></p><textarea>a\n  b</textarea></div></body><html>");
		$tpl->forceCompilation();

		$dwoo = new Dwoo();
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
    	$tpl = new DwooTemplateString('{ldelim}{$smarty.version}{rdelim}');
		$tpl->forceCompilation();
		$cmp = new DwooCompiler();
		$cmp->addPreProcessor('smarty_compat', true);

        $this->assertEquals('{'.Dwoo::VERSION.'}', $this->dwoo->get($tpl, array(), $cmp));
    }
}

?>