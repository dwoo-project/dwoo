<?php

namespace Dwoo\Tests
{

    use Dwoo\Compiler;
    use Dwoo\Core;
    use Dwoo\Template\Str as TemplateString;

    /**
     * Class FiltersTest
     *
     * @package Dwoo\Tests
     */
    class FiltersTest extends BaseTests
    {
        public function testHtmlFormat()
        {
            $tpl = new TemplateString("<html><body><div><p>a<em>b</em>c<hr /></p><textarea>a\n  b</textarea></div></body><html>");
            $tpl->forceCompilation();

            $dwoo = new Core($this->compileDir, $this->cacheDir);
            $dwoo->addFilter('PluginHtmlFormat', true);

            $this->assertEquals(str_replace("\r", '', <<<'SNIPPET'

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

        public function testSmartyCompatible()
        {
            $tpl = new TemplateString('{ldelim}{$smarty.version}{rdelim}');
            $tpl->forceCompilation();
            $cmp = new Compiler();
            $cmp->addPreProcessor('PluginSmartyCompatible', true);

            $this->assertEquals('{' . Core::VERSION . '}', $this->dwoo->get($tpl, array(), $cmp));
        }
    }
}