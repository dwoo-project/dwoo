<?php

require_once 'Dwoo/Compiler.php';

class TemplateTests extends PHPUnit_Framework_TestCase
{
	protected $compiler;
	protected $dwoo;

	public function __construct()
	{
		$this->compiler = new Dwoo_Compiler();
		$this->dwoo = new Dwoo(DWOO_COMPILE_DIR, DWOO_CACHE_DIR);
	}

	public function testIncludePath()
	{
		// no include path
		$tpl = new Dwoo_Template_File('test.html');
		$this->assertEquals('test.html', $tpl->getResourceIdentifier());

		// include path in constructor
		$tpl = new Dwoo_Template_File('test.html', null, null, null, TEST_DIRECTORY.DIRECTORY_SEPARATOR.'resources');
		$this->assertEquals(TEST_DIRECTORY.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'test.html', $tpl->getResourceIdentifier());

		// set include path as string
		$tpl->setIncludePath(TEST_DIRECTORY.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'subfolder'.DIRECTORY_SEPARATOR);
		$this->assertThat($tpl->getResourceIdentifier(), new DwooConstraintPathEquals(TEST_DIRECTORY.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'subfolder'.DIRECTORY_SEPARATOR.'test.html'));

		// set include path as array
		$tpl->setIncludePath(array(TEST_DIRECTORY.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'subfolder2', TEST_DIRECTORY.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'subfolder'.DIRECTORY_SEPARATOR));
		$this->assertThat($tpl->getResourceIdentifier(), new DwooConstraintPathEquals(TEST_DIRECTORY.DIRECTORY_SEPARATOR.'resources'.DIRECTORY_SEPARATOR.'subfolder2'.DIRECTORY_SEPARATOR.'test.html'));
	}
}
