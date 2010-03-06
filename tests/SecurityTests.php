<?php

require_once DWOO_DIRECTORY . 'Dwoo/Compiler.php';

class SecurityTests extends PHPUnit_Framework_TestCase
{
	protected $compiler;
	protected $dwoo;
	protected $policy;

	public function __construct()
	{
		$this->compiler = new Dwoo_Compiler();
		$this->dwoo = new Dwoo_Core(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
		$this->policy = new Dwoo_Security_Policy();
		$this->dwoo->setSecurityPolicy($this->policy);
	}

	public function testConstantHandling()
	{
		$tpl = new Dwoo_Template_String('{$dwoo.const.DWOO_DIRECTORY}');
		$tpl->forceCompilation();

		$this->assertEquals("", $this->dwoo->get($tpl, array(), $this->compiler));

		$this->policy->setConstantHandling(Dwoo_Security_Policy::CONST_ALLOW);

		$tpl = new Dwoo_Template_String('{$dwoo.const.DWOO_DIRECTORY}');
		$tpl->forceCompilation();

		$this->assertEquals(DWOO_DIRECTORY, $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testPhpHandling()
	{
		$this->policy->setPhpHandling(Dwoo_Security_Policy::PHP_ALLOW);

		$tpl = new Dwoo_Template_String('<?php echo "moo"; ?>');
		$tpl->forceCompilation();

		$this->assertEquals("moo", $this->dwoo->get($tpl, array(), $this->compiler));


		$this->policy->setPhpHandling(Dwoo_Security_Policy::PHP_ENCODE);

		$tpl = new Dwoo_Template_String('<?php echo "moo"; ?>');
		$tpl->forceCompilation();

		$this->assertEquals(htmlspecialchars('<?php echo "moo"; ?>'), $this->dwoo->get($tpl, array(), $this->compiler));


		$this->policy->setPhpHandling(Dwoo_Security_Policy::PHP_REMOVE);

		$tpl = new Dwoo_Template_String('<?php echo "moo"; ?>');
		$tpl->forceCompilation();

		$this->assertEquals('', $this->dwoo->get($tpl, array(), $this->compiler));
	}

	public function testAllowPhpFunction()
	{
		$this->policy->allowPhpFunction('testphpfunc');

		$tpl = new Dwoo_Template_String('{testphpfunc("foo")}');
		$tpl->forceCompilation();

		$this->assertEquals("fooOK", $this->dwoo->get($tpl, array(), $this->compiler));

		$this->policy->disallowPhpFunction('testphpfunc');
	}

	/**
	 * @expectedException Dwoo_Security_Exception
	 */
	public function testNotAllowedPhpFunction()
	{
		$tpl = new Dwoo_Template_String('{testphpfunc("foo")}');
		$tpl->forceCompilation();

		$this->dwoo->get($tpl, array(), $this->compiler);
	}

	public function testAllowMethod()
	{
		$this->policy->allowMethod('testSecurityClass','testOK');

		$tpl = new Dwoo_Template_String('{$obj->testOK("foo")}');
		$tpl->forceCompilation();

		$this->assertEquals("fooOK", $this->dwoo->get($tpl, array('obj' => new testSecurityClass), $this->compiler));

		$this->policy->disallowMethod('testSecurityClass','test');
	}

	/**
	 * @expectedException PHPUnit_Framework_Error
	 */
	public function testNotAllowedMethod()
	{
		$tpl = new Dwoo_Template_String('{$obj->testOK("foo")}');
		$tpl->forceCompilation();

		$this->dwoo->get($tpl, array('obj' => new testSecurityClass), $this->compiler);
	}

	public function testAllowStaticMethod()
	{
		$this->policy->allowMethod('testSecurityClass','testStatic');

		$tpl = new Dwoo_Template_String('{testSecurityClass::testStatic("foo")}');
		$tpl->forceCompilation();

		$this->assertEquals("fooOK", $this->dwoo->get($tpl, array(), $this->compiler));

		$this->policy->disallowMethod('testSecurityClass','testStatic');
	}

	/**
	 * @expectedException Dwoo_Security_Exception
	 */
	public function testNotAllowedStaticMethod()
	{
		$tpl = new Dwoo_Template_String('{testSecurityClass::testStatic("foo")}');
		$tpl->forceCompilation();

		$this->dwoo->get($tpl, array(), $this->compiler);
	}

	/**
	 * @expectedException Dwoo_Security_Exception
	 */
	public function testNotAllowedSubExecution()
	{
		$tpl = new Dwoo_Template_String('{$obj->test(preg_replace_callback("{.}", "mail", "f"))}');
		$tpl->forceCompilation();

		$this->dwoo->get($tpl, array('obj' => new testSecurityClass), $this->compiler);
	}

	public function testAllowDirectoryGetSet()
	{
		$old = $this->policy->getAllowedDirectories();
		$this->policy->allowDirectory(array('./resources'));
		$this->policy->allowDirectory('./temp');
		$this->assertEquals(array_merge($old, array(realpath('./resources')=>true, realpath('./temp')=>true)), $this->policy->getAllowedDirectories());

		$this->policy->disallowDirectory(array('./resources'));
		$this->policy->disallowDirectory('./temp');
		$this->assertEquals($old, $this->policy->getAllowedDirectories());
	}

	public function testAllowPhpGetSet()
	{
		$old = $this->policy->getAllowedPhpFunctions();
		$this->policy->allowPhpFunction(array('a','b'));
		$this->policy->allowPhpFunction('c');
		$this->assertEquals(array_merge($old, array('a'=>true, 'b'=>true, 'c'=>true)), $this->policy->getAllowedPhpFunctions());

		$this->policy->disallowPhpFunction(array('a', 'b'));
		$this->policy->disallowPhpFunction('c');
		$this->assertEquals($old, $this->policy->getAllowedPhpFunctions());
	}
}

function testphpfunc($input) { return $input.'OK'; }

class testSecurityClass {
	public static function testStatic($input) {
		return $input.'OK';
	}

	public function testOK($input) {
		return $input.'OK';
	}

	public function test($input) {
		throw new Exception('can not call');
	}
}
