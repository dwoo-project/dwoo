<?php

require_once dirname(dirname(__FILE__)).'/DwooCompiler.php';

function testphpfunc($input) { return $input.'OK'; }

class SecurityTests extends PHPUnit_Framework_TestCase
{
	protected $compiler;
	protected $dwoo;
	protected $policy;

	public function __construct()
	{
		$this->compiler = new DwooCompiler();
		$this->dwoo = new Dwoo();
		$this->policy = new DwooSecurityPolicy();
		$this->dwoo->setSecurityPolicy($this->policy);
	}

    public function testConstantHandling()
    {
    	$tpl = new DwooTemplateString('{$dwoo.const.DWOO_DIR}');
		$tpl->forceCompilation();

		$this->assertEquals("", $this->dwoo->get($tpl, array(), $this->compiler));

		$this->policy->setConstantHandling(DwooSecurityPolicy::CONST_ALLOW);

    	$tpl = new DwooTemplateString('{$dwoo.const.DWOO_DIR}');
		$tpl->forceCompilation();

		$this->assertEquals(DWOO_DIR, $this->dwoo->get($tpl, array(), $this->compiler));
    }

    public function testPhpHandling()
    {
		$this->policy->setPhpHandling(DwooSecurityPolicy::PHP_ALLOW);

    	$tpl = new DwooTemplateString('<?php echo "moo"; ?>');
		$tpl->forceCompilation();

		$this->assertEquals("moo", $this->dwoo->get($tpl, array(), $this->compiler));


		$this->policy->setPhpHandling(DwooSecurityPolicy::PHP_ENCODE);

    	$tpl = new DwooTemplateString('<?php echo "moo"; ?>');
		$tpl->forceCompilation();

		$this->assertEquals(htmlspecialchars('<?php echo "moo"; ?>'), $this->dwoo->get($tpl, array(), $this->compiler));


		$this->policy->setPhpHandling(DwooSecurityPolicy::PHP_REMOVE);

    	$tpl = new DwooTemplateString('<?php echo "moo"; ?>');
		$tpl->forceCompilation();

		$this->assertEquals('', $this->dwoo->get($tpl, array(), $this->compiler));
    }

    public function testAllowPhpFunction()
    {
		$this->policy->allowPhpFunction('testphpfunc');

    	$tpl = new DwooTemplateString('{testphpfunc("foo")}');
		$tpl->forceCompilation();

		$this->assertEquals("fooOK", $this->dwoo->get($tpl, array(), $this->compiler));
    }

}

?>